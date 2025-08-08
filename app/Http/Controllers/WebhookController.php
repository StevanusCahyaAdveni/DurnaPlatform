<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Income;
use App\Services\XenditService;

class WebhookController extends Controller
{
    protected $xenditService;

    public function __construct(XenditService $xenditService)
    {
        $this->xenditService = $xenditService;
    }

    public function xenditWebhook(Request $request)
    {
        try {
            $rawBody = $request->getContent();
            $signature = $request->header('x-callback-token');

            // Verify webhook signature
            if (!$this->xenditService->verifyWebhookSignature($rawBody, $signature)) {
                Log::warning('Invalid Xendit webhook signature');
                return response()->json(['status' => 'error', 'message' => 'Invalid signature'], 400);
            }

            $data = $request->all();

            Log::info('Xendit webhook received', $data);

            // Handle invoice paid webhook
            if (isset($data['external_id']) && str_starts_with($data['external_id'], 'income_')) {
                $incomeId = str_replace('income_', '', $data['external_id']);
                $income = Income::find($incomeId);

                if ($income) {
                    // Check if payment is completed
                    if (in_array(strtoupper($data['status'] ?? ''), ['PAID', 'SETTLED', 'SUCCESS', 'COMPLETED'])) {
                        $income->update([
                            'status' => 'paid',
                            'paid_at' => now(),
                            'payment_channel' => $data['payment_channel'] ?? $data['payment_method'] ?? null,
                        ]);

                        Log::info('Payment status updated via webhook', [
                            'income_id' => $incomeId,
                            'status' => 'paid',
                            'amount' => $data['amount'] ?? 'unknown'
                        ]);
                    }
                }
            }

            return response()->json(['status' => 'success']);
        } catch (\Exception $e) {
            Log::error('Xendit webhook error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json(['status' => 'error', 'message' => 'Internal error'], 500);
        }
    }
}

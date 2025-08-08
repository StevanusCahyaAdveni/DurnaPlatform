<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class XenditService
{
    private $secretKey;
    private $baseUrl = 'https://api.xendit.co';

    public function __construct()
    {
        $this->secretKey = config('services.xendit.secret_key');
    }

    public function createInvoice($incomeId, $amount, $userEmail, $userName)
    {
        try {
            $params = [
                'external_id' => 'income_' . $incomeId,
                'payer_email' => $userEmail,
                'description' => 'Top Up Saldo - ' . $userName,
                'amount' => $amount,
                'currency' => 'IDR',
                'invoice_duration' => 86400, // 24 hours
                'success_redirect_url' => url('/income/success'),
                'failure_redirect_url' => url('/income/failed'),
                'payment_methods' => ['BANK_TRANSFER', 'EWALLET', 'RETAIL_OUTLET', 'QRIS'],
                'fees' => [
                    [
                        'type' => 'ADMIN',
                        'value' => 5000 // Admin fee 5000 IDR
                    ]
                ]
            ];

            $response = Http::withBasicAuth($this->secretKey, '')
                ->withHeaders([
                    'Content-Type' => 'application/json',
                ])
                ->post($this->baseUrl . '/v2/invoices', $params);

            if ($response->successful()) {
                $invoice = $response->json();

                Log::info('Xendit invoice created', ['invoice_id' => $invoice['id']]);

                return [
                    'success' => true,
                    'invoice_url' => $invoice['invoice_url'],
                    'invoice_id' => $invoice['id']
                ];
            } else {
                Log::error('Xendit API Error', [
                    'status' => $response->status(),
                    'response' => $response->json()
                ]);

                return [
                    'success' => false,
                    'error' => 'API Error: ' . $response->status()
                ];
            }
        } catch (\Exception $e) {
            Log::error('Xendit invoice creation failed', ['error' => $e->getMessage()]);

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    public function getInvoice($invoiceId)
    {
        try {
            $response = Http::withBasicAuth($this->secretKey, '')
                ->get($this->baseUrl . '/v2/invoices/' . $invoiceId);

            if ($response->successful()) {
                $invoiceData = $response->json();

                // Log the invoice status for debugging
                Log::info('Invoice status retrieved', [
                    'invoice_id' => $invoiceId,
                    'status' => $invoiceData['status'] ?? 'unknown',
                    'full_response' => $invoiceData
                ]);

                return $invoiceData;
            }

            Log::error('Failed to retrieve invoice', [
                'invoice_id' => $invoiceId,
                'status' => $response->status(),
                'response' => $response->json()
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('Failed to retrieve invoice', ['error' => $e->getMessage()]);
            return null;
        }
    }

    public function verifyWebhookSignature($rawBody, $callbackToken)
    {
        $webhookToken = config('services.xendit.webhook_token');

        // For Xendit, the callback token should match our webhook token
        return hash_equals($webhookToken, $callbackToken);
    }

    /**
     * Send money via bank transfer (Disbursement)
     */
    public function createBankDisbursement($externalId, $amount, $bankCode, $accountNumber, $accountHolderName, $description = '')
    {
        try {
            $params = [
                'external_id' => $externalId,
                'amount' => $amount,
                'bank_code' => $bankCode,
                'account_holder_name' => $accountHolderName,
                'account_number' => $accountNumber,
                'description' => $description ?: 'Withdrawal from DurnaPlatform',
                'x_idempotency_key' => $externalId . '_' . time()
            ];

            $response = Http::withBasicAuth($this->secretKey, '')
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'X-IDEMPOTENCY-KEY' => $params['x_idempotency_key']
                ])
                ->post($this->baseUrl . '/disbursements', $params);

            if ($response->successful()) {
                $disbursement = $response->json();

                Log::info('Bank disbursement created', [
                    'external_id' => $externalId,
                    'disbursement_id' => $disbursement['id']
                ]);

                return [
                    'success' => true,
                    'disbursement_id' => $disbursement['id'],
                    'status' => $disbursement['status'],
                    'data' => $disbursement
                ];
            } else {
                $errorData = $response->json();
                Log::error('Bank disbursement failed', [
                    'status' => $response->status(),
                    'response' => $errorData
                ]);

                return [
                    'success' => false,
                    'error' => $errorData['message'] ?? 'Disbursement failed'
                ];
            }
        } catch (\Exception $e) {
            Log::error('Bank disbursement exception', ['error' => $e->getMessage()]);

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Send money via e-wallet
     */
    public function createEwalletDisbursement($externalId, $amount, $ewalletType, $phoneNumber, $description = '')
    {
        try {
            $params = [
                'reference_id' => $externalId,
                'currency' => 'IDR',
                'amount' => $amount,
                'checkout_method' => 'ONE_TIME_PAYMENT',
                'channel_code' => strtoupper($ewalletType), // OVO, DANA, LINKAJA, SHOPEEPAY
                'channel_properties' => [
                    'mobile_number' => $phoneNumber
                ],
                'metadata' => [
                    'description' => $description ?: 'Withdrawal from DurnaPlatform'
                ]
            ];

            $response = Http::withBasicAuth($this->secretKey, '')
                ->withHeaders([
                    'Content-Type' => 'application/json',
                ])
                ->post($this->baseUrl . '/ewallets/payments', $params);

            if ($response->successful()) {
                $disbursement = $response->json();

                Log::info('E-wallet disbursement created', [
                    'external_id' => $externalId,
                    'payment_id' => $disbursement['id']
                ]);

                return [
                    'success' => true,
                    'disbursement_id' => $disbursement['id'],
                    'status' => $disbursement['status'],
                    'data' => $disbursement
                ];
            } else {
                $errorData = $response->json();
                Log::error('E-wallet disbursement failed', [
                    'status' => $response->status(),
                    'response' => $errorData
                ]);

                return [
                    'success' => false,
                    'error' => $errorData['message'] ?? 'E-wallet disbursement failed'
                ];
            }
        } catch (\Exception $e) {
            Log::error('E-wallet disbursement exception', ['error' => $e->getMessage()]);

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    /**
     * Check if invoice status indicates payment is completed
     */
    public function isInvoicePaid($invoice)
    {
        if (!$invoice || !isset($invoice['status'])) {
            return false;
        }

        $status = strtoupper($invoice['status']);

        // Handle different possible paid statuses from Xendit
        $paidStatuses = ['PAID', 'SETTLED', 'SUCCESS', 'COMPLETED'];

        return in_array($status, $paidStatuses);
    }

    /**
     * Get human readable status
     */
    public function getStatusText($status)
    {
        $upperStatus = strtoupper($status ?? '');

        switch ($upperStatus) {
            case 'PENDING':
                return 'Pending Payment';
            case 'PAID':
            case 'SETTLED':
            case 'SUCCESS':
            case 'COMPLETED':
                return 'Paid';
            case 'EXPIRED':
                return 'Expired';
            case 'FAILED':
                return 'Failed';
            default:
                return ucfirst(strtolower($status ?? 'unknown'));
        }
    }
}

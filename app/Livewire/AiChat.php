<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class AiChat extends Component
{
    // Properti untuk input user
    public $newMessage = '';

    // Properti untuk menyimpan riwayat chat (akan disinkronkan dari Firestore melalui JS)
    public $chatHistory = [];

    // Properti untuk API Key OpenRouter (diambil dari input user)
    public $openRouterApiKey = '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';

    // Properti untuk status loading AI response
    public $isLoading = false;  // PASTIKAN ini ada dan public

    // Properti untuk ID user (didapat dari Firebase Auth di JS, lalu dikirim ke Livewire)
    public $userId;

    // Listener untuk menerima update chat history dari JavaScript (Firestore)
    // dan untuk menerima userId dari JavaScript setelah autentikasi Firebase
    protected $listeners = [
        'setUserId' // Dipanggil dari JS setelah Firebase Auth siap
    ];

    /**
     * Metode ini dipanggil saat komponen diinisialisasi.
     * Kita tidak bisa langsung mendapatkan __app_id, __firebase_config, __initial_auth_token di sini
     * karena mereka adalah variabel JS global. Kita akan mengaturnya di Blade/JavaScript.
     */
    public function mount()
    {
        // Inisialisasi properti
        $this->newMessage = '';
        $this->isLoading = false;

        // Dapatkan user ID dari Laravel Auth
        $this->userId = Auth::id();

        // Load chat history from session
        $this->loadChatHistory();

        Log::info('User ID dari Auth: ' . $this->userId);
    }

    /**
     * Load chat history from session
     */
    private function loadChatHistory()
    {
        $sessionKey = 'ai_chat_history_' . Auth::id();
        $this->chatHistory = session($sessionKey, []);
        Log::info('Loaded chat history from session', ['count' => count($this->chatHistory)]);
    }

    /**
     * Save chat history to session
     */
    private function saveChatHistory()
    {
        $sessionKey = 'ai_chat_history_' . Auth::id();
        session([$sessionKey => $this->chatHistory]);
        Log::info('Saved chat history to session', ['count' => count($this->chatHistory)]);
    }

    /**
     * Render tampilan komponen.
     */
    public function render()
    {
        return view('livewire.ai-chat');
    }

    /**
     * Metode untuk menerima userId dari JavaScript setelah Firebase Auth siap.
     */
    public function setUserId($id)
    {
        $this->userId = $id;
        Log::info('User ID received in Livewire: ' . $this->userId);
    }

    /**
     * Mengirim pesan dari user ke AI.
     */
    public function sendMessage()
    {
        // Pastikan user sudah login
        if (!Auth::check()) {
            session()->flash('error_message', 'Anda harus login terlebih dahulu.');
            return;
        }

        $this->validate([
            'newMessage' => 'required|string|max:2000', // Batasi panjang pesan
            'openRouterApiKey' => 'required|string', // Pastikan API Key ada
        ]);

        // Gunakan Auth::id() langsung
        $this->userId = Auth::id();

        $userMessage = [
            'role' => 'user',
            'content' => $this->newMessage,
            'timestamp' => now()->timestamp, // Gunakan timestamp PHP
            'user_id' => $this->userId  // Tambahkan user_id jika perlu tracking
        ];

        // Tambahkan pesan user ke history
        $this->chatHistory[] = $userMessage;

        // Save to session immediately after adding user message
        $this->saveChatHistory();

        $this->isLoading = true;  // Set loading true
        $this->newMessage = ''; // Kosongkan input

        // Dispatch event to scroll to bottom
        $this->dispatch('scrollToBottom');

        // Panggil AI untuk mendapatkan respons
        $this->getAiResponse();
    }

    /**
     * Memanggil API OpenRouter (DeepSeek) untuk mendapatkan respons AI.
     * Ini dijalankan di sisi server (PHP).
     */
    private function getAiResponse()
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->openRouterApiKey,
                'Content-Type' => 'application/json',
            ])->timeout(30)->post('https://openrouter.ai/api/v1/chat/completions', [
                'model' => 'deepseek/deepseek-chat',
                'messages' => collect($this->chatHistory)->map(fn($msg) => [
                    'role' => $msg['role'],
                    'content' => $msg['content']
                ])->toArray(),
                'max_tokens' => 1000,
                'temperature' => 0.7,
            ]);

            if ($response->successful()) {
                $aiContent = $response->json('choices.0.message.content');

                $this->chatHistory[] = [
                    'role' => 'assistant',
                    'content' => $aiContent,
                    'timestamp' => now()->timestamp,
                ];

                // Save to session after adding AI response
                $this->saveChatHistory();

                // Dispatch event to scroll to bottom after AI response
                $this->dispatch('scrollToBottom');

                Log::info('AI Response received successfully');
            } else {
                $errorMessage = 'API Error: ' . $response->status() . ' - ' . $response->body();
                session()->flash('error_message', $errorMessage);
                Log::error($errorMessage);
            }
        } catch (\Exception $e) {
            $errorMessage = 'Error: ' . $e->getMessage();
            session()->flash('error_message', $errorMessage);
            Log::error('Exception: ' . $e->getMessage());
        } finally {
            $this->isLoading = false;  // Set loading false
        }
    }

    /**
     * Clear chat history (can be called manually or on logout)
     */
    public function clearChatHistory()
    {
        $this->chatHistory = [];
        $sessionKey = 'ai_chat_history_' . Auth::id();
        session()->forget($sessionKey);
        Log::info('Chat history cleared');

        // Dispatch event after clearing
        $this->dispatch('chatCleared');
    }
}

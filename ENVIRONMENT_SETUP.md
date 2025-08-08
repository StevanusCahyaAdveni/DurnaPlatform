# Environment Setup untuk Durna Platform

## Environment Variables yang Diperlukan

### OpenRouter API Key (untuk AI Chat)

```env
OPENROUTER_API_KEY=your_openrouter_api_key_here
```

### Xendit Payment Gateway

```env
XENDIT_PUBLIC_KEY=your_xendit_public_key
XENDIT_SECRET_KEY=your_xendit_secret_key
XENDIT_WEBHOOK_TOKEN=your_xendit_webhook_token
```

## Setup Instructions

1. Copy file `.env.example` ke `.env`:

    ```bash
    cp .env.example .env
    ```

2. Isi environment variables yang diperlukan:

    - `OPENROUTER_API_KEY`: Dapatkan dari https://openrouter.ai/
    - `XENDIT_PUBLIC_KEY`, `XENDIT_SECRET_KEY`, `XENDIT_WEBHOOK_TOKEN`: Dapatkan dari Xendit Dashboard

3. Generate application key:

    ```bash
    php artisan key:generate
    ```

4. Clear configuration cache setelah mengubah .env:
    ```bash
    php artisan config:clear
    ```

## Keamanan

-   **JANGAN** commit file `.env` ke repository
-   **JANGAN** gunakan nilai default untuk API keys di config files
-   Selalu gunakan environment variables untuk API keys dan secrets
-   File `.env.example` hanya berisi contoh struktur tanpa nilai sensitif
-   Pastikan `.env` sudah ada di `.gitignore`

### ⚠️ Penting: Jika Anda Pernah Commit API Keys

Jika sebelumnya Anda pernah commit API keys ke GitHub:

1. **Regenerate semua API keys** di dashboard provider (Xendit, OpenRouter)
2. **Revoke/nonaktifkan** API keys yang lama
3. **Update** `.env` dengan API keys yang baru
4. **Hapus history** git yang mengandung API keys (gunakan `git filter-branch` atau BFG Repo-Cleaner)

### Best Practices

-   Gunakan API keys development untuk testing
-   Gunakan API keys production hanya di server production
-   Monitor penggunaan API keys secara berkala
-   Implementasikan rate limiting dan monitoring

## AI Chat Configuration

AI Chat menggunakan OpenRouter API dengan model DeepSeek. Pastikan API key valid dan memiliki credit yang cukup.

**Model yang digunakan:** `deepseek/deepseek-chat`
**Max tokens:** 1000
**Temperature:** 0.7

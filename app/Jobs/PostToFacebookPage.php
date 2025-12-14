<?php

namespace App\Jobs;

use App\Models\FacebookPage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Throwable;

class PostToFacebookPage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public int $facebookPageId,
        public string $type,
        public ?string $message = null,
        public ?string $mediaPath = null,
    ) {
        $this->onQueue('facebook');
    }

    public function handle(): void
    {
        $page = FacebookPage::findOrFail($this->facebookPageId);
        $token = decrypt($page->page_access_token);

        $payload = [];
        $endpoint = '';
        $http = Http::withToken($token);

        if ($this->message) {
            $payload['message'] = $this->message;
            $payload['caption'] = $this->message;
            $payload['description'] = $this->message;
        }

        switch ($this->type) {
            case 'photo':
                $endpoint = "https://graph.facebook.com/v19.0/{$page->page_id}/photos";
                $http = $this->attachMedia($http);
                break;
            case 'video':
                $endpoint = "https://graph.facebook.com/v19.0/{$page->page_id}/videos";
                $http = $this->attachMedia($http);
                break;
            default:
                $endpoint = "https://graph.facebook.com/v19.0/{$page->page_id}/feed";
        }

        $response = $http->post($endpoint, $payload);

        if (!$response->successful()) {
            Log::warning('Facebook post failed', [
                'page_id' => $page->page_id,
                'type' => $this->type,
                'response' => $response->json(),
            ]);
        }

        $this->cleanupMedia();
    }

    public function failed(Throwable $exception): void
    {
        $this->cleanupMedia();
    }

    protected function attachMedia($http)
    {
        if (!$this->mediaPath) {
            return $http;
        }

        $filePath = Storage::path($this->mediaPath);

        if (!is_readable($filePath)) {
            return $http;
        }

        return $http->attach('source', fopen($filePath, 'r'), basename($filePath));
    }

    protected function cleanupMedia(): void
    {
        if ($this->mediaPath && Storage::exists($this->mediaPath)) {
            Storage::delete($this->mediaPath);
        }
    }
}

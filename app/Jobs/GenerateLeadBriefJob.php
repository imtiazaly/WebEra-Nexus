<?php

namespace App\Jobs;

use App\Models\Client;
use App\Services\Ai\AiServiceInterface;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class GenerateLeadBriefJob implements ShouldQueue
{
    use Queueable;

    public function __construct(public Client $client) {}

    public function handle(AiServiceInterface $aiService): void
    {
        $brief = $aiService->generateLeadBrief($this->client);
        if ($brief) {
            $this->client->update(['ai_brief' => $brief]);
        }
    }
}

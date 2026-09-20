<?php

namespace App\Services\Ai;

use App\Models\Client;
use App\Models\Project;
use App\Models\Setting;
use App\Models\WeeklyReport;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiAiService implements AiServiceInterface
{
    protected string $apiKey;

    protected string $model;

    protected bool $enabled;

    public function __construct()
    {
        // Fetch setting values or fallback to config / env
        $globalEnabled = Setting::where('key', 'ai_global_enabled')->value('value');
        $this->enabled = $globalEnabled === '1' || $globalEnabled === 'true' || $globalEnabled === null;

        $dbApiKey = Setting::where('key', 'ai_api_key')->value('value');
        $this->apiKey = $dbApiKey ?: (string) config('services.gemini.api_key', '');

        $dbModel = Setting::where('key', 'ai_model')->value('value');
        $this->model = $dbModel ?: (string) config('services.gemini.model', 'gemini-1.5-flash');
    }

    public function generateLeadBrief(Client $client): ?string
    {
        if (! $this->isAvailable()) {
            return null;
        }

        $servicesList = $client->services->pluck('name')->implode(', ');
        $prompt = "You are an executive business analyst for WebEra Solutions PK. Generate a concise 2-3 sentence strategic brief for this client lead.\n"
          ."Client Name: {$client->name}\n"
          ."Company: {$client->company_name}\n"
          ."Status: {$client->status}\n"
          .'Services Interested: '.($servicesList ?: 'General Inquiry')."\n"
          ."Notes: {$client->notes}\n"
          .'Provide clear next steps or key business insights.';

        return $this->callGemini($prompt);
    }

    public function summarizeWeeklyReport(WeeklyReport $report): ?string
    {
        if (! $this->isAvailable()) {
            return null;
        }

        $studentName = $report->student->name ?? 'Student';
        $prompt = "You are an internship mentor supervisor. Summarize this weekly report submitted by {$studentName} for Week {$report->week_number} in 2 clear bullet-style sentences.\n"
          ."Tasks Completed: {$report->tasks_completed}\n"
          ."Learnings: {$report->learnings}\n"
          ."Blockers: {$report->blockers}\n"
          .'Highlight key progress and any critical blockers that need mentor attention.';

        return $this->callGemini($prompt);
    }

    public function summarizeProjectStatus(Project $project): ?string
    {
        if (! $this->isAvailable()) {
            return null;
        }

        $clientName = $project->client->name ?? 'Internal Project';
        $prompt = "You are a senior project manager. Provide a single concise executive status sentence for project '{$project->title}'.\n"
          ."Client: {$clientName}\n"
          ."Status: {$project->status}\n"
          ."Progress: {$project->progress}%\n"
          ."Deadline: {$project->deadline}\n"
          ."Description: {$project->description}\n"
          .'Summarize current health, risk level, or milestone status.';

        return $this->callGemini($prompt);
    }

    protected function isAvailable(): bool
    {
        return $this->enabled && ! empty($this->apiKey);
    }

    protected function callGemini(string $prompt): ?string
    {
        try {
            $url = "https://generativelanguage.googleapis.com/v1beta/models/{$this->model}:generateContent?key={$this->apiKey}";

            $response = Http::timeout(15)->post($url, [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $prompt],
                        ],
                    ],
                ],
            ]);

            if ($response->successful()) {
                $data = $response->json();

                return $data['candidates'][0]['content']['parts'][0]['text'] ?? null;
            }

            Log::error('Gemini API Error Response', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return null;
        } catch (\Throwable $e) {
            Log::error('Gemini AI Service Exception: '.$e->getMessage());

            return null;
        }
    }
}

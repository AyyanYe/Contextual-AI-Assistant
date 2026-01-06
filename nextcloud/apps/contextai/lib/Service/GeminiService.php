<?php
namespace OCA\Contextai\Service;

use OCP\Http\Client\IClientService;

class GeminiService {
    private $clientService;
    private $apiKey;

    public function __construct(IClientService $clientService) {
        $this->clientService = $clientService;
        
        $configPath = __DIR__ . '/../config.private.php';
        
        if (file_exists($configPath)) {
            $secrets = require $configPath;
            $this->apiKey = $secrets['gemini_api_key'] ?? '';
        } else {
            $this->apiKey = ''; // Handle missing config gracefully
        }
    }

    public function summarize(string $content, string $mimeType): string {
        if (empty($this->apiKey)) {
            return "Configuration Error: API Key not found. Please create config.private.php.";
        }

        $client = $this->clientService->newClient();
        $url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key=' . $this->apiKey;

        $parts = [];

        // Logic: PDF vs Text
        if ($mimeType === 'application/pdf') {
            $parts[] = [
                'inline_data' => [
                    'mime_type' => 'application/pdf',
                    'data' => base64_encode($content)
                ]
            ];
            $parts[] = ['text' => "Please summarize this document in 3 bullet points."];
        } else {
            $cleanText = mb_convert_encoding($content, 'UTF-8', 'UTF-8');
            $parts[] = ['text' => "Summarize this:\n\n" . $cleanText];
        }

        $payload = ['contents' => [['parts' => $parts]]];
        
        $jsonBody = json_encode($payload);
        if ($jsonBody === false) {
            return "Error: JSON encoding failed.";
        }

        try {
            $response = $client->post($url, [
                'headers' => ['Content-Type' => 'application/json'],
                'body' => $jsonBody
            ]);

            $body = json_decode($response->getBody(), true);
            
            if (isset($body['error'])) {
                return "Gemini Error: " . $body['error']['message'];
            }

            return $body['candidates'][0]['content']['parts'][0]['text'] ?? "Error: No text response.";
            
        } catch (\Exception $e) {
            return "Connection Error: " . $e->getMessage();
        }
    }
}
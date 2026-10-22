<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class GeminiVision
{
    public function extract(UploadedFile $photo, string $instructions, array $properties): mixed
    {
        if (! config('nutrition_ai.key') || ! config('nutrition_ai.model')) {
            throw new RuntimeException('La lecture IA n’est pas configurée. Renseignez GEMINI_API_KEY et GEMINI_VISION_MODEL dans le fichier .env.');
        }
        $response = Http::withHeaders(['x-goog-api-key' => config('nutrition_ai.key')])->acceptJson()->connectTimeout(10)->timeout(50)
            ->post('https://generativelanguage.googleapis.com/v1beta/models/'.rawurlencode(config('nutrition_ai.model')).':generateContent', [
                'systemInstruction' => ['parts' => [['text' => $instructions]]],
                'contents' => [['role' => 'user', 'parts' => [
                    ['text' => 'Analyse cette photo selon les instructions.'],
                    ['inlineData' => ['mimeType' => $photo->getMimeType(), 'data' => base64_encode(file_get_contents($photo->getRealPath()))]],
                ]]],
                'generationConfig' => [
                    'maxOutputTokens' => 4096,
                    'responseMimeType' => 'application/json',
                    'responseJsonSchema' => ['type' => 'object', 'properties' => $properties, 'required' => array_keys($properties), 'additionalProperties' => false],
                ],
            ]);

        if (! $response->successful()) {
            throw new RuntimeException(match ($response->status()) {
                400, 401, 403 => 'L’accès au service IA est refusé. Vérifiez la clé et les autorisations du modèle.',
                429 => 'Le quota du service IA est atteint. Vérifiez votre compte ou réessayez plus tard.',
                default => 'Le service IA est indisponible ou le modèle est incompatible. Réessayez plus tard.',
            });
        }
        if ($response->json('candidates.0.finishReason') !== 'STOP') {
            throw new RuntimeException('La lecture IA est incomplète ou refusée. Essayez une photo plus nette.');
        }
        $text = '';
        foreach ($response->json('candidates.0.content.parts', []) as $part) {
            if (! ($part['thought'] ?? false)) {
                $text .= $part['text'] ?? '';
            }
        }

        return json_decode($text, true);
    }
}

<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Throwable;

// IA du module Distribution (Gemini, clé gratuite GEMINI_API_KEY). Renvoie null si l'IA est indisponible.
class NutriAI
{
    public static function ask(string $prompt, ?array $schema = null): string|array|null
    {
        if (! config('nutrition_ai.key')) {
            return null;
        }
        try {
            $config = ['maxOutputTokens' => 1024, 'thinkingConfig' => ['thinkingBudget' => 0]];
            if ($schema) {
                $config += ['responseMimeType' => 'application/json', 'responseJsonSchema' => $schema];
            }
            $response = Http::withHeaders(['x-goog-api-key' => config('nutrition_ai.key')])->connectTimeout(5)->timeout(20)
                ->post('https://generativelanguage.googleapis.com/v1beta/models/'.config('nutrition_ai.model').':generateContent', ['contents' => [['parts' => [['text' => $prompt]]]], 'generationConfig' => $config]);
            $text = trim((string) $response->json('candidates.0.content.parts.0.text'));

            return $text === '' ? null : ($schema ? json_decode($text, true) : $text);
        } catch (Throwable) {
            return null;
        }
    }

    /** E-mail au client après la réponse HTTP : texte rédigé par l'IA, texte standard en secours. */
    public static function notify(User $user, string $subject, string $facts, string $fallback): void
    {
        defer(function () use ($user, $subject, $facts, $fallback) {
            $body = self::ask("Rédige en français un court e-mail (3 phrases maximum, ton chaleureux et professionnel, vouvoiement) pour {$user->name}, de la part de NutriTrace. N'invente aucune information et ne mets ni objet, ni formule de politesse finale, ni signature. Informations : {$facts}") ?? $fallback;
            try {
                Mail::raw($body."\n\n— L'équipe NutriTrace", fn ($m) => $m->to($user->email)->subject($subject));
            } catch (Throwable $e) {
                report($e);
            }
        });
    }
}

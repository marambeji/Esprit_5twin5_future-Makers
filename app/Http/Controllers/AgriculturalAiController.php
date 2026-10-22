<?php

namespace App\Http\Controllers;

use App\Models\Ferme;
use App\Models\Producteur;
use App\Services\AgriculturalAiException;
use App\Services\AgriculturalAiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

class AgriculturalAiController extends Controller
{
    public function generateDescription(Request $request, AgriculturalAiService $assistant): JsonResponse
    {
        if ($request->routeIs('fermes.generate-description')) {
            $user = $request->user('web');
            abort_unless($user && $user->role === 'user' && $user->hasVerifiedEmail()
                && Producteur::query()->whereRaw('LOWER(email) = ?', [mb_strtolower($user->email)])->exists(), 403,
                'Un compte producteur vérifié est requis pour utiliser l’assistant.');
        }

        $data = $request->validate([
            'nom' => ['required', 'string', 'max:150'],
            'localisation' => ['required', 'string', 'max:255'],
            'superficie' => ['nullable', 'numeric', 'min:0', 'max:99999999.99', 'decimal:0,2'],
            'draft' => ['nullable', 'string', 'max:2000'],
            'mode' => ['nullable', 'in:generate,improve,shorten,lengthen,tags,review'],
            'tone' => ['nullable', 'in:professional,warm,commercial,concise'],
            'length' => ['nullable', 'in:short,medium,detailed'],
            'language' => ['prohibited'],
        ]);

        try {
            $mode = $data['mode'] ?? 'generate';
            $options = $data;
            $result = match ($mode) {
                'improve', 'shorten', 'lengthen' => $assistant->improveText($data, $options),
                'tags' => ['description' => '', 'tags' => $assistant->suggestTags($data, $options)['tags'], 'missingInfo' => []],
                'review' => ['description' => '', 'tags' => [], 'missingInfo' => $assistant->reviewQuality($data, $options)['missingInfo']],
                default => $assistant->generateDescription($data, $options),
            };

            $identity = $request->routeIs('admin.*') ? 'admin' : 'web';
            $historyKey = 'agricultural_ai.versions.'.$identity.'.'.$request->user($identity)?->getAuthIdentifier();
            $history = $request->session()->get($historyKey, []);
            if ($result['description'] !== '') {
                array_unshift($history, $result['description']);
                $history = array_slice(array_values(array_unique($history)), 0, 3);
                $request->session()->put($historyKey, $history);
            }

            return response()->json($result + ['versions' => $history]);
        } catch (AgriculturalAiException $exception) {
            return response()->json(['error' => $exception->errorCode, 'message' => $exception->getMessage()], $exception->httpStatus);
        } catch (Throwable $exception) {
            Log::error('Unexpected agricultural AI failure.', ['exception' => $exception::class]);

            return response()->json(['error' => 'ai_internal_error', 'message' => 'La suggestion n’a pas pu être générée. Votre texte est conservé.'], 500);
        }
    }

    public function ask(Request $request, Ferme $ferme, AgriculturalAiService $assistant): RedirectResponse
    {
        $data = $request->validate(['action' => ['required', 'in:description,summary,comparison,improvements,organization']]);
        try {
            return back()->with('agricultural_ai_result', $assistant->ask($ferme, $data['action']));
        } catch (AgriculturalAiException $exception) {
            return back()->with('agricultural_ai_error', $exception->getMessage());
        } catch (Throwable $exception) {
            Log::error('Unexpected agricultural AI assistant failure.', ['exception' => $exception::class]);

            return back()->with('agricultural_ai_error', 'Une erreur inattendue a empêché la suggestion. Aucune donnée n’a été modifiée.');
        }
    }
}

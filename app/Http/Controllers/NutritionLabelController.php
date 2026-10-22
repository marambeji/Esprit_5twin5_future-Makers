<?php

namespace App\Http\Controllers;

use App\Services\NutritionLabelReader;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Request;
use RuntimeException;

class NutritionLabelController extends Controller
{
    public function __invoke(Request $request, NutritionLabelReader $reader)
    {
        $request->validate([
            'photo' => ['required', 'file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048', 'dimensions:max_width=8000,max_height=8000'],
        ], [
            'photo.required' => 'Choisissez une photo de l’étiquette.',
            'photo.max' => 'La photo ne doit pas dépasser 2 Mo.',
            'photo.image' => 'Le fichier doit être une image valide.',
            'photo.mimes' => 'Formats acceptés : JPEG, PNG et WebP.',
            'photo.dimensions' => 'La photo ne doit pas dépasser 8 000 pixels par côté.',
        ]);

        try {
            return response()->json($reader->read($request->file('photo')))->header('Cache-Control', 'no-store');
        } catch (ConnectionException $exception) {
            return response()->json(['message' => 'Le service IA ne répond pas. Réessayez plus tard.'], 503);
        } catch (RuntimeException $exception) {
            return response()->json(['message' => $exception->getMessage()], 503);
        }
    }
}

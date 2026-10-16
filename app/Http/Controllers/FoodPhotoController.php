<?php

namespace App\Http\Controllers;

use App\Services\FoodPhotoIdentifier;
use App\Services\FoodReferenceSearch;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use RuntimeException;

class FoodPhotoController extends Controller
{
    public function identify(Request $request, FoodPhotoIdentifier $identifier)
    {
        $request->validate(['photo' => ['required', 'file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048', 'dimensions:max_width=8000,max_height=8000']]);
        try {
            return response()->json($identifier->identify($request->file('photo')))->header('Cache-Control', 'no-store');
        } catch (ConnectionException $e) {
            return response()->json(['message' => 'Le service IA ne répond pas. Réessayez.'], 503);
        } catch (RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 503);
        }
    }

    public function search(Request $request, FoodReferenceSearch $references)
    {
        $data = $request->validate(['query' => 'required|string|max:150', 'state' => 'required|in:raw,cooked,processed', 'confirmed' => 'required|accepted']);
        try {
            $foods = $references->search($data['query'], $data['state']);
            foreach ($foods as &$food) {
                $food['token'] = Crypt::encryptString(json_encode(['id' => $food['id'], 'description' => $food['description'], 'admin_id' => $request->user('admin')->id, 'expires' => now()->addHours(2)->timestamp]));
            }

            return response()->json(['foods' => $foods])->header('Cache-Control', 'no-store');
        } catch (ConnectionException $e) {
            return response()->json(['message' => 'La base nutritionnelle ne répond pas. Réessayez.'], 503);
        } catch (RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 503);
        }
    }
}

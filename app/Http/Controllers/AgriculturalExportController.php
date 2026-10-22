<?php

namespace App\Http\Controllers;

use App\Services\AgriculturalListingService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AgriculturalExportController extends Controller
{
    public function producteurs(Request $request, AgriculturalListingService $listing): StreamedResponse
    {
        $producteurs = $listing->producteurs($request)
            ->with('fermes:id,producteur_id,nom,localisation')
            ->lazy(500);

        return $this->csv('producteurs.csv', ['Nom', 'Prénom', 'E-mail', 'Téléphone', 'Adresse', 'Nombre de fermes', 'Fermes', 'Localisations des fermes'], function ($output) use ($producteurs): void {
            foreach ($producteurs as $producteur) {
                fputcsv($output, [
                    $this->safe($producteur->nom),
                    $this->safe($producteur->prenom),
                    $this->safe($producteur->email),
                    $this->safePhone($producteur->telephone),
                    $this->safe($producteur->adresse),
                    $producteur->fermes_count,
                    $this->safe($producteur->fermes->pluck('nom')->implode(' | ')),
                    $this->safe($producteur->fermes->pluck('localisation')->implode(' | ')),
                ], ';', '"', '', "\r\n");
            }
        });
    }

    public function fermes(Request $request, AgriculturalListingService $listing): StreamedResponse
    {
        $fermes = $listing->fermes($request)->lazy(500);

        return $this->csv('fermes.csv', ['Ferme', 'Prénom du producteur', 'Nom du producteur', 'E-mail du producteur', 'Téléphone du producteur', 'Adresse du producteur', 'Localisation', 'Superficie (ha)', 'Latitude', 'Longitude', 'Description'], function ($output) use ($fermes): void {
            foreach ($fermes as $ferme) {
                fputcsv($output, [
                    $this->safe($ferme->nom),
                    $this->safe($ferme->producteur?->prenom),
                    $this->safe($ferme->producteur?->nom),
                    $this->safe($ferme->producteur?->email),
                    $this->safePhone($ferme->producteur?->telephone),
                    $this->safe($ferme->producteur?->adresse),
                    $this->safe($ferme->localisation),
                    $ferme->superficie,
                    $ferme->latitude,
                    $ferme->longitude,
                    $this->safe($ferme->description),
                ], ';', '"', '', "\r\n");
            }
        });
    }

    private function csv(string $filename, array $headers, \Closure $rows): StreamedResponse
    {
        return response()->streamDownload(function () use ($headers, $rows): void {
            $output = fopen('php://output', 'wb');
            fwrite($output, "\xEF\xBB\xBF");
            fputcsv($output, $headers, ';', '"', '', "\r\n");

            try {
                $rows($output);
            } finally {
                fclose($output);
            }
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function safe(?string $value): string
    {
        $value ??= '';

        return preg_match('/^[\s\p{Z}\x{FEFF}]*[=+\-@]/u', $value) ? "'{$value}" : $value;
    }

    private function safePhone(?string $value): string
    {
        if ($value !== null && preg_match('/^\+?[0-9][0-9\s().-]*$/u', $value)) {
            return $value;
        }

        return $this->safe($value);
    }
}

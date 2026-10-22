<?php

namespace App\Services;

use App\Models\Ferme;
use App\Models\Producteur;

class AgriculturalDataQualityService
{
    public function forProducteur(Producteur $producteur): array
    {
        $missing = [];
        $recommendations = [];
        $completedFields = 0;

        if (filled($producteur->telephone)) {
            $completedFields++;
        } else {
            $missing[] = 'Téléphone';
            $recommendations[] = 'Ajoutez un numéro de téléphone pour faciliter la prise de contact.';
        }

        if (filled($producteur->adresse)) {
            $completedFields++;
        } else {
            $missing[] = 'Adresse';
            $recommendations[] = 'Ajoutez une adresse ou une zone d’activité pour mieux situer le producteur.';
        }

        return $this->result($completedFields, 2, $missing, [], $recommendations);
    }

    public function forFerme(Ferme $ferme): array
    {
        $missing = [];
        $invalid = [];
        $warnings = [];
        $recommendations = [];
        $completedFields = 0;

        if (filled($ferme->description)) {
            $completedFields++;
        } else {
            $missing[] = 'Description';
            $recommendations[] = 'Ajoutez une courte description pour présenter l’exploitation.';
        }

        $rawSurface = $ferme->getAttributes()['superficie'] ?? null;
        if ($rawSurface === null || $rawSurface === '') {
            $missing[] = 'Superficie';
            $recommendations[] = 'Indiquez la superficie en hectares lorsque cette information est disponible.';
        } elseif (! is_numeric($rawSurface) || ! is_finite((float) $rawSurface) || (float) $rawSurface <= 0) {
            $invalid[] = 'Superficie';
            $warnings[] = 'La superficie enregistrée doit être un nombre positif.';
            $recommendations[] = 'Vérifiez la superficie enregistrée et saisissez une valeur supérieure à zéro en hectares.';
        } else {
            $completedFields++;
        }

        if (! filled($ferme->localisation)) {
            $missing[] = 'Localisation';
            $recommendations[] = 'Indiquez une ville ou une région; une adresse personnelle n’est pas nécessaire.';
        } elseif ($this->hasPreciseLocation((string) $ferme->localisation)) {
            $completedFields++;
        } else {
            $warnings[] = 'La localisation semble trop générale pour situer la ferme.';
            $recommendations[] = 'Remplacez une mention générale par le nom d’une ville ou d’une région, sans adresse personnelle.';
        }

        $result = $this->result($completedFields, 3, $missing, $warnings, $recommendations);
        $result['invalid'] = $invalid;

        return $result;
    }

    private function hasPreciseLocation(string $location): bool
    {
        $normalised = mb_strtolower(trim(preg_replace('/\s+/u', ' ', $location) ?? $location));
        $normalised = preg_replace('/[.,;]+$/u', '', $normalised) ?? $normalised;

        if (mb_strlen($normalised) < 3) {
            return false;
        }

        return ! in_array($normalised, ['tunisie', 'tunisia', 'afrique', 'inconnu', 'inconnue', 'n/a', 'na', 'à préciser', 'a preciser', 'non précisée', 'non precisee'], true);
    }

    private function result(int $completedFields, int $fieldCount, array $missing, array $warnings, array $recommendations): array
    {
        return [
            'score' => (int) round(($completedFields / $fieldCount) * 100),
            'missing' => $missing,
            'warnings' => $warnings,
            'recommendations' => $recommendations,
        ];
    }
}

<?php

namespace Tests\Unit;

use App\Models\Ferme;
use App\Models\Producteur;
use App\Services\AgriculturalDataQualityService;
use Tests\TestCase;

class AgriculturalDataQualityServiceTest extends TestCase
{
    public function test_producer_score_is_rule_based_and_explains_missing_contact_fields(): void
    {
        $quality = app(AgriculturalDataQualityService::class)->forProducteur(new Producteur(['nom' => 'Test', 'prenom' => 'User', 'email' => 'user@example.test']));
        self::assertSame(0, $quality['score']);
        self::assertSame(['Téléphone', 'Adresse'], $quality['missing']);
        self::assertCount(2, $quality['recommendations']);
    }

    public function test_complete_farm_gets_full_score_from_saved_values(): void
    {
        $quality = app(AgriculturalDataQualityService::class)->forFerme(new Ferme([
            'nom' => 'Ferme test', 'localisation' => 'Sfax', 'superficie' => 12.5, 'description' => 'Exploitation familiale.',
        ]));
        self::assertSame(100, $quality['score']);
        self::assertSame([], $quality['missing']);
        self::assertSame([], $quality['invalid']);
        self::assertSame([], $quality['warnings']);
    }

    public function test_missing_location_and_area_are_separate_actionable_completions(): void
    {
        $quality = app(AgriculturalDataQualityService::class)->forFerme(new Ferme(['description' => 'Ferme.']));
        self::assertSame(33, $quality['score']);
        self::assertSame(['Superficie', 'Localisation'], $quality['missing']);
        self::assertSame([], $quality['invalid']);
        self::assertCount(2, $quality['recommendations']);
    }

    public function test_zero_or_non_numeric_area_and_vague_location_are_flagged_as_invalid_or_imprecise(): void
    {
        $service = app(AgriculturalDataQualityService::class);
        $zero = $service->forFerme(new Ferme(['description' => 'Ferme', 'localisation' => 'Tunisie', 'superficie' => 0]));
        self::assertSame(33, $zero['score']);
        self::assertSame(['Superficie'], $zero['invalid']);
        self::assertSame([], $zero['missing']);
        self::assertCount(2, $zero['recommendations']);

        $invalid = $service->forFerme(new Ferme(['description' => 'Ferme', 'localisation' => 'Sfax', 'superficie' => 'abc']));
        self::assertSame(67, $invalid['score']);
        self::assertSame(['Superficie'], $invalid['invalid']);
        self::assertContains('La superficie enregistrée doit être un nombre positif.', $invalid['warnings']);
    }

    public function test_non_empty_city_name_does_not_need_punctuation_to_be_precise(): void
    {
        $quality = app(AgriculturalDataQualityService::class)->forFerme(new Ferme([
            'description' => 'Ferme', 'localisation' => 'Béja', 'superficie' => 10,
        ]));
        self::assertSame(100, $quality['score']);
        self::assertSame([], $quality['warnings']);
    }
}

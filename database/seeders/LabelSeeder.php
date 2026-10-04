<?php

namespace Database\Seeders;

use App\Models\Certification;
use App\Models\Label;
use Illuminate\Database\Seeder;

class LabelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $bio = Label::firstOrCreate(
            ['nom' => 'Bio'],
            [
                'description' => 'Agriculture biologique',
                'organisme' => 'Ministère Agriculture'
            ]
        );

        $iso = Label::firstOrCreate(
            ['nom' => 'ISO 22000'],
            [
                'description' => 'Sécurité alimentaire',
                'organisme' => 'ISO'
            ]
        );

        $fairtrade = Label::firstOrCreate(
            ['nom' => 'Fairtrade'],
            [
                'description' => 'Commerce équitable',
                'organisme' => 'Fairtrade International'
            ]
        );

        Certification::firstOrCreate(
            ['numero_certificat' => 'CERT-2026-001'],
            [
                'label_id' => $bio->id,
                'nom' => 'Certification Bio Ferme A',
                'date_obtention' => '2026-01-10',
                'date_expiration' => '2027-01-10',
                'statut' => 'valide',
                'description' => 'Certification Bio délivrée à la Ferme A.'
            ]
        );
    }
}

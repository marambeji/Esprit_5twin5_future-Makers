<?php

namespace Database\Seeders;

use App\Models\Ferme;
use App\Models\Producteur;
use Illuminate\Database\Seeder;

class ProducteurSeeder extends Seeder
{
    public function run(): void
    {
        foreach (self::samples() as $producteurData) {
            if (! Producteur::where('email', $producteurData['email'])->exists()) {
                $producteur = Producteur::factory()->create($producteurData);
                Ferme::factory()->count(3)->create(['producteur_id' => $producteur->id]);
            }
        }
    }

    public static function samples(): array
    {
        return [
            ['nom' => 'Ben Salah', 'prenom' => 'Ahmed', 'email' => 'ahmed.bensalah@nutritrace.test', 'telephone' => '+216 71 234 567', 'adresse' => 'Route de Béja, Béja 9000, Tunisie'],
            ['nom' => 'Trabelsi', 'prenom' => 'Fatma', 'email' => 'fatma.trabelsi@nutritrace.test', 'telephone' => '+216 72 345 678', 'adresse' => 'Avenue Habib Bourguiba, Nabeul 8000, Tunisie'],
            ['nom' => 'Chaabane', 'prenom' => 'Mohamed', 'email' => 'mohamed.chaabane@nutritrace.test', 'telephone' => '+216 74 456 789', 'adresse' => 'Route de Sfax, Sfax 3000, Tunisie'],
            ['nom' => 'Mansour', 'prenom' => 'Leila', 'email' => 'leila.mansour@nutritrace.test', 'telephone' => '+216 75 567 890', 'adresse' => 'Oasis de Kébili, Kébili 4200, Tunisie'],
            ['nom' => 'Jebali', 'prenom' => 'Sami', 'email' => 'sami.jebali@nutritrace.test', 'telephone' => '+216 78 678 901', 'adresse' => 'Zone agricole, Zaghouan 1100, Tunisie'],
        ];
    }
}

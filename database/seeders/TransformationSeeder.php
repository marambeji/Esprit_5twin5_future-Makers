<?php

namespace Database\Seeders;

use App\Models\Ferme;
use App\Models\Product;
use App\Models\Transformateur;
use App\Models\Transformation;
use Illuminate\Database\Seeder;

// Données de démonstration du module Transformation : transformateurs tunisiens et étapes liées aux produits du catalogue.
class TransformationSeeder extends Seeder
{
    public function run(): void
    {
        foreach (self::samples() as $data) {
            $etapes = $data['etapes'];
            unset($data['etapes']);
            if (Transformateur::where('email', $data['email'])->exists()) {
                continue;
            }
            $transformateur = Transformateur::create($data);
            foreach ($etapes as [$produit, $procede, $quantite, $joursAvant, $allegation]) {
                $product = Product::where('name', $produit)->first();
                if (! $product) {
                    continue;
                }
                Transformation::create([
                    'transformateur_id'   => $transformateur->id,
                    'product_id'          => $product->id,
                    'ferme_id'            => Ferme::inRandomOrder()->value('id'),
                    'procede'             => $procede,
                    'date_transformation' => now()->subDays($joursAvant)->toDateString(),
                    'quantite_kg'         => $quantite,
                    'allegation'          => $allegation,
                    'verdict_allegation'  => $allegation ? 'non_evaluee' : null,
                ] + Transformation::estimationLocale($procede, $quantite));
            }
        }
    }

    public static function samples(): array
    {
        return [
            ['nom' => 'Laiterie du Cap Bon', 'type_activite' => 'laiterie', 'email' => 'contact@laiterie-capbon.test', 'telephone' => '+216 72 111 222', 'ville' => 'Nabeul', 'adresse' => 'Zone industrielle, Route de Hammamet, Nabeul 8000', 'description' => 'Collecte du lait des fermes du Cap Bon, pasteurisation et fabrication de yaourts et fromages frais.',
                'etapes' => [['Lait', 'pasteurisation', 1200, 12, 'Lait 100 % local'], ['Fromage frais', 'fromagerie', 300, 8, null], ['Yaourt nature', 'pasteurisation', 450, 3, null]]],
            ['nom' => 'Huilerie Zitouna', 'type_activite' => 'huilerie', 'email' => 'contact@huilerie-zitouna.test', 'telephone' => '+216 74 333 444', 'ville' => 'Sfax', 'adresse' => 'Route de Gabès km 5, Sfax 3000', 'description' => 'Extraction à froid d’huile d’olive extra vierge à partir d’olives de la région de Sfax.',
                'etapes' => [['Huile d’olive', 'pressage', 900, 20, 'Zéro impact carbone'], ['Huile de sésame', 'pressage', 150, 6, null]]],
            ['nom' => 'Moulin de Béja', 'type_activite' => 'minoterie', 'email' => 'contact@moulin-beja.test', 'telephone' => '+216 78 555 666', 'ville' => 'Béja', 'adresse' => 'Avenue de la République, Béja 9000', 'description' => 'Mouture de blé dur et d’orge des plaines du Nord-Ouest en semoule et farine.',
                'etapes' => [['Blé dur', 'mouture', 5000, 30, null], ['Orge', 'mouture', 1800, 15, null]]],
            ['nom' => 'Conserverie El Bahia', 'type_activite' => 'conserverie', 'email' => 'contact@conserverie-elbahia.test', 'telephone' => '+216 73 777 888', 'ville' => 'Sousse', 'adresse' => 'Zone industrielle Sidi Abdelhamid, Sousse 4000', 'description' => 'Concentré et conserves de tomates, séchage de fruits et de légumes.',
                'etapes' => [['Tomates', 'appertisation', 2500, 25, 'Produit éco-responsable'], ['Dattes Deglet Nour', 'conditionnement', 700, 10, null], ['Tomates', 'sechage', 400, 5, null]]],
        ];
    }
}

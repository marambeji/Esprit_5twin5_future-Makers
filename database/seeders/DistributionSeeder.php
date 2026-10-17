<?php

namespace Database\Seeders;

use App\Models\Commande;
use App\Models\Distributor;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;

class DistributionSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([['Agro Distribution Tunis', 'Tunis', 'contact@agro-tunis.test', '71 123 456'], ['Fresh Logistics Sfax', 'Sfax', 'contact@fresh-sfax.test', '74 234 567'], ['Sahel Primeurs', 'Sousse', 'contact@sahel-primeurs.test', '73 345 678']] as [$name, $city, $email, $phone]) {
            $d = Distributor::firstOrCreate(['email' => $email], ['name' => $name, 'city' => $city, 'phone' => $phone, 'address' => '12 avenue de la République']);
            if (! $d->deliveries()->exists()) {
                foreach (['livree', 'en_cours', 'planifiee'] as $i => $status) {
                    $d->deliveries()->create(['destination' => 'Marché de gros, '.$city, 'delivery_date' => now()->addDays($i - 1), 'status' => $status]);
                }
            }
        }
        $this->seedDemoAccounts();
    }

    // Comptes et commandes de démonstration, uniquement en local/test. Idempotent : rien n'est supprimé ni dupliqué.
    private function seedDemoAccounts(): void
    {
        if (! app()->environment(['local', 'testing'])) {
            return;
        }
        $client = User::firstOrCreate(['username' => 'client_demo'], ['name' => 'Client Démo', 'email' => 'client.demo@nutritrace.test', 'phone' => '20000001', 'password' => 'password', 'role' => 'user']);
        $livreur = User::firstOrCreate(['username' => 'distributeur_demo'], ['name' => 'Distributeur Démo', 'email' => 'distributeur.demo@nutritrace.test', 'phone' => '20000002', 'password' => 'password', 'role' => 'user']);
        $distributor = Distributor::where('email', 'contact@agro-tunis.test')->first();
        if ($distributor && ! $distributor->user_id && ! Distributor::where('user_id', $livreur->id)->exists()) {
            $distributor->update(['user_id' => $livreur->id]);
        }
        $products = Product::orderBy('id')->take(4)->get();
        if ($products->count() < 4 || Commande::where('user_id', $client->id)->exists()) {
            return;
        }
        $base = ['user_id' => $client->id, 'telephone' => '20 000 001'];
        $adresse = 'Marché de gros, Tunis';
        Commande::create($base + ['product_id' => $products[0]->id, 'quantite' => 5, 'adresse_livraison' => '10 rue de la Liberté, Tunis', 'statut' => 'en_attente']);
        Commande::create($base + ['product_id' => $products[1]->id, 'quantite' => 2, 'adresse_livraison' => '10 rue de la Liberté, Tunis', 'statut' => 'refusee', 'motif_refus' => 'Produit indisponible cette semaine.']);
        Commande::create($base + ['product_id' => $products[2]->id, 'quantite' => 8, 'adresse_livraison' => '10 rue de la Liberté, Tunis', 'statut' => 'confirmee']);
        $livree = Commande::create($base + ['product_id' => $products[3]->id, 'quantite' => 3, 'adresse_livraison' => $adresse, 'statut' => 'confirmee']);
        // Rattache une ancienne livraison de démo à cette commande (aucune création ni suppression).
        $legacy = $distributor?->deliveries()->whereNull('commande_id')->where('destination', $adresse)->where('status', 'en_cours')->first();
        $legacy?->update(['commande_id' => $livree->id]);
    }
}

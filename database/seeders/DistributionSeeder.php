<?php

namespace Database\Seeders;

use App\Models\Delivery;
use App\Models\Distributor;
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
    }
}

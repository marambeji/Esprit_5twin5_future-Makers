<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $email = config('nutritrace.admin_email');
        $password = config('nutritrace.admin_password');
        if ($email && $password) {
            User::firstOrCreate(['email' => $email], ['name' => 'Administrateur NutriTrace', 'password' => $password, 'role' => 'admin']);
        }
        if (app()->environment(['local', 'testing'])) {
            for ($i = 1; $i <= 5; $i++) {
                if (! User::where('email', "utilisateur{$i}@nutritrace.test")->exists()) {
                    User::factory()->create(['email' => "utilisateur{$i}@nutritrace.test", 'password' => Str::random(32), 'role' => 'user']);
                }
            }
        }
    }
}

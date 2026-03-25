<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Str;
use ToneGabes\Filament\Icons\Enums\Phosphor;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Création des catégories métier BTP
        $categories = [
            ['name' => 'Restauration', 'icon' => Phosphor::ForkKnife->getLabel()],
            ['name' => 'Carburant', 'icon' => Phosphor::Truck->getLabel()],
            ['name' => 'Outillage urgent', 'icon' => Phosphor::Wrench->getLabel()],
            ['name' => 'Péage / Parking', 'icon' => Phosphor::Ticket->getLabel()],
            ['name' => 'Hébergement', 'icon' => Phosphor::Bed->getLabel()],
        ];

        foreach ($categories as $cat) {
            Category::create([
                'name' => $cat['name'],
                'slug' => Str::slug($cat['name']),
                'icon' => $cat['icon'],
                'is_active' => true,
            ]);
        }

        $adminRole = Role::create(['name' => 'admin']);
        $salaryRole = Role::create(['name' => 'salary']);

        // Administrateur (Gestion et validation)
        $admin = User::factory()->create([
            'name' => 'Admin BatiStack',
            'email' => 'admin@admin.com',
            'password' => bcrypt('admin'),
        ]);
        $admin->assignRole($adminRole);
    }
}

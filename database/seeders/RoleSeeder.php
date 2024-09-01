<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;

class RoleSeeder extends Seeder
{
    public function run()
    {
        Role::factory()->create(['name' => 'Admin']);
        Role::factory()->create(['name' => 'Boutiquier']);
        Role::factory()->create(['name' => 'Client']);
    }
}

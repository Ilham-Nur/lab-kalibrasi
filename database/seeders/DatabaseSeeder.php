<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['username' => 'admin'],
            [
                'name' => 'Admin Utama',
                'email' => null,
                'password' => Hash::make('password'),
            ]
        );

        $this->call(DocumentCategorySeeder::class);
        $this->call(DocumentSectionSeeder::class);
        $this->call(DivisionPositionSeeder::class);
        $this->call(AssetCategorySeeder::class);
        $this->call(AssetLocationSeeder::class);
        $this->call(RolePermissionSeeder::class);
    }
}

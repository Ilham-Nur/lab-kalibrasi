<?php

namespace Database\Seeders;

use App\Models\AssetCategory;
use Illuminate\Database\Seeder;

class AssetCategorySeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            'Alat Ukur',
            'Alat Standar',
            'Peralatan Lab',
            'Peralatan IT',
            'Furniture',
            'Peralatan Keselamatan',
            'Lainnya',
        ] as $name) {
            AssetCategory::updateOrCreate(['name' => $name], ['description' => null]);
        }
    }
}

<?php

namespace Database\Seeders;

use App\Models\AssetLocation;
use Illuminate\Database\Seeder;

class AssetLocationSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            'Ruang Kalibrasi',
            'Gudang',
            'Ruang Admin',
            'Area Penerimaan Barang',
            'Lainnya',
        ] as $name) {
            AssetLocation::updateOrCreate(['name' => $name], ['description' => null]);
        }
    }
}

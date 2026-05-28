<?php

namespace Database\Seeders;

use App\Models\AssetSupplier;
use Illuminate\Database\Seeder;

class AssetSupplierSeeder extends Seeder
{
    public function run(): void
    {
        $suppliers = [
            ['name' => 'PT Sumber Alat Laboratorium', 'contact_person' => 'Admin Sales', 'phone' => '021-555-0101', 'email' => 'sales@sumberalatlab.test'],
            ['name' => 'CV Kalibrasi Nusantara', 'contact_person' => 'Rina', 'phone' => '021-555-0102', 'email' => 'info@kalibrasinusantara.test'],
            ['name' => 'PT Teknologi Instrumentasi', 'contact_person' => 'Budi', 'phone' => '021-555-0103', 'email' => 'procurement@teknologiinstrumentasi.test'],
        ];

        foreach ($suppliers as $supplier) {
            AssetSupplier::updateOrCreate(
                ['name' => $supplier['name']],
                $supplier + ['status' => 'active']
            );
        }
    }
}

<?php

namespace Database\Seeders;

use App\Models\Division;
use App\Models\Position;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DivisionPositionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $divisionNames = ['Administrasi', 'Keuangan', 'Operator', 'Supervisor', 'Manager'];

        foreach ($divisionNames as $index => $name) {
            Division::updateOrCreate(
                ['name' => $name],
                ['description' => null, 'status' => 'aktif']
            );
        }

        $positions = [
            'Administrasi' => ['Admin Cabang', 'Admin Kantor'],
            'Keuangan' => ['Staff Keuangan'],
            'Operator' => ['Operator Internal', 'Operator Ketebalan', 'Operator UT'],
            'Supervisor' => ['Supervisor'],
            'Manager' => ['Manager'],
        ];

        foreach ($positions as $divisionName => $positionNames) {
            $division = Division::where('name', $divisionName)->first();

            foreach ($positionNames as $positionName) {
                Position::updateOrCreate(
                    ['name' => $positionName],
                    [
                        'division_id' => $division?->id,
                        'description' => null,
                        'status' => 'aktif',
                    ]
                );
            }
        }
    }
}

<?php

namespace Database\Seeders;

use App\Models\DocumentSection;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DocumentSectionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $sections = [
            ['id' => 1, 'parent_id' => null, 'chapter_number' => '1', 'title' => 'Ruang Lingkup', 'order_number' => 1000000],
            ['id' => 2, 'parent_id' => 1, 'chapter_number' => '1.1', 'title' => 'Tujuan dan Penerapan', 'order_number' => 1001000],
            ['id' => 3, 'parent_id' => 1, 'chapter_number' => '1.2', 'title' => 'Acuan Normatif', 'order_number' => 1002000],
            ['id' => 4, 'parent_id' => null, 'chapter_number' => '2', 'title' => 'Persyaratan Manajemen', 'order_number' => 2000000],
            ['id' => 5, 'parent_id' => 4, 'chapter_number' => '2.1', 'title' => 'Pengendalian Dokumen', 'order_number' => 2001000],
            ['id' => 6, 'parent_id' => 4, 'chapter_number' => '2.2', 'title' => 'Audit Internal', 'order_number' => 2002000],
        ];

        foreach ($sections as $section) {
            DocumentSection::updateOrCreate(['id' => $section['id']], $section);
        }
    }
}

<?php

namespace Database\Seeders;

use App\Models\DocumentCategory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DocumentCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            ['id' => 1, 'name' => 'Manual Mutu', 'code' => 'MM', 'parent_id' => null, 'order_number' => 1],
            ['id' => 2, 'name' => 'Quality Procedure', 'code' => 'QP', 'parent_id' => null, 'order_number' => 2],
            ['id' => 3, 'name' => 'Working Instruction', 'code' => 'IK', 'parent_id' => null, 'order_number' => 3],
            ['id' => 4, 'name' => 'Form', 'code' => 'F', 'parent_id' => null, 'order_number' => 4],
        ];

        foreach ($categories as $category) {
            DocumentCategory::updateOrCreate(
                ['id' => $category['id']],
                $category
            );
        }
    }
}

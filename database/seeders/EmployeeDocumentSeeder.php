<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EmployeeDocumentSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('employee_documents')->insert([
            [
                'employee_id' => 1,
                'document_name' => 'CCCD',
                'document_type' => 'cccd',
                'file_path' => '/uploads/cccd1.jpg',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'employee_id' => 1,
                'document_name' => 'CV',
                'document_type' => 'cv',
                'file_path' => '/uploads/cv1.pdf',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}

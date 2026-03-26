<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Signature;

class SignatureSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Signature::firstOrCreate([
            'responsibility_code' => 1,
        ], [
            'title' => 'مسؤول وحدة',
            'name' => 'مسؤول الوحدة',
            'is_active' => true,
        ]);

        Signature::firstOrCreate([
            'responsibility_code' => 2,
        ], [
            'title' => 'مسؤول الشعبة',
            'name' => 'مسؤول الشعبة',
            'is_active' => true,
        ]);

        Signature::firstOrCreate([
            'responsibility_code' => 3,
        ], [
            'title' => 'قسم التدقيق',
            'name' => 'موظف التدقيق المالي',
            'is_active' => true,
        ]);

        Signature::firstOrCreate([
            'responsibility_code' => 4,
        ], [
            'title' => 'رئيس قسم الشؤون المالية',
            'name' => 'رئيس قسم الشؤون المالية',
            'is_active' => true,
        ]);
    }
}

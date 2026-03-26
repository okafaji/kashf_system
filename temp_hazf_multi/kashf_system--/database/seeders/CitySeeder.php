<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Governorate;

class CitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $cairo = Governorate::where('name', 'Cairo')->first();

        if ($cairo) {
            DB::table('cities')->insertOrIgnore([
                ['name' => 'Nasr City', 'governorate_id' => $cairo->id],
                ['name' => 'Heliopolis', 'governorate_id' => $cairo->id],
                ['name' => 'Maadi', 'governorate_id' => $cairo->id],
                ['name' => 'Zamalek', 'governorate_id' => $cairo->id],
                ['name' => 'Downtown', 'governorate_id' => $cairo->id],
            ]);
        }

        $giza = Governorate::where('name', 'Giza')->first();

        if ($giza) {
            DB::table('cities')->insertOrIgnore([
                ['name' => '6th of October', 'governorate_id' => $giza->id],
                ['name' => 'Sheikh Zayed', 'governorate_id' => $giza->id],
                ['name' => 'Haram', 'governorate_id' => $giza->id],
                ['name' => 'Faisal', 'governorate_id' => $giza->id],
            ]);
        }
    }
}

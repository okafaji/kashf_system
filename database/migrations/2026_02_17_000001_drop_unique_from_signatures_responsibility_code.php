<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('signatures', function (Blueprint $table) {
            if (Schema::hasColumn('signatures', 'responsibility_code')) {
                $table->dropUnique('signatures_responsibility_code_unique');
            }
        });
    }

    public function down(): void
    {
        Schema::table('signatures', function (Blueprint $table) {
            if (Schema::hasColumn('signatures', 'responsibility_code')) {
                $table->unique('responsibility_code');
            }
        });
    }
};

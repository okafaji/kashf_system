<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('payrolls') || !Schema::hasTable('mission_types')) {
            return;
        }

        if (Schema::hasColumn('payrolls', 'mission_type_id')) {
            return;
        }

        Schema::table('payrolls', function (Blueprint $table) {
            $column = $table->foreignId('mission_type_id')->nullable();

            if (Schema::hasColumn('payrolls', 'destination')) {
                $column->after('destination');
            }

            $column->constrained('mission_types')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('payrolls') || !Schema::hasColumn('payrolls', 'mission_type_id')) {
            return;
        }

        Schema::table('payrolls', function (Blueprint $table) {
            $table->dropConstrainedForeignId('mission_type_id');
        });
    }
};

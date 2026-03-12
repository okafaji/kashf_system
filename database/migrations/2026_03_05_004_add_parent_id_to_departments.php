<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('departments', function (Blueprint $table) {
            // إضافة parent_id لربط الوحدات بالشعب
            $table->unsignedBigInteger('parent_id')->nullable()->after('name');
            $table->foreign('parent_id')->references('id')->on('departments')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('departments', function (Blueprint $table) {
            $table->dropForeign(['parent_id']);
            $table->dropColumn('parent_id');
        });
    }
};

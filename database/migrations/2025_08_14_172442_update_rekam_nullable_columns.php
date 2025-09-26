<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rekam', function (Blueprint $table) {
            $table->text('pemeriksaan')->nullable()->change();
            $table->text('diagnosa')->nullable()->change();
            $table->text('tindakan')->nullable()->change();
            $table->decimal('biaya_obat', 10, 2)->nullable()->change();
            $table->decimal('total_biaya', 10, 2)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('rekam', function (Blueprint $table) {
            $table->text('pemeriksaan')->change();
            $table->text('diagnosa')->change();
            $table->text('tindakan')->change();
            $table->decimal('biaya_obat', 10, 2)->change();
            $table->decimal('total_biaya', 10, 2)->change();
        });
    }
};

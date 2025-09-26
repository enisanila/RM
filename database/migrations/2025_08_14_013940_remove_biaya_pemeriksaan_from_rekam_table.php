<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('rekam', function (Blueprint $table) {
            $table->dropColumn('biaya_pemeriksaan');
        });
    }

    public function down()
    {
        Schema::table('rekam', function (Blueprint $table) {
            $table->integer('biaya_pemeriksaan')->nullable(); 
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFileToRekamTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
   public function up()
{
    Schema::table('rekam', function (Blueprint $table) {
        $table->string('file')->nullable()->after('diagnosa'); // kolom untuk nama file
    });
}

public function down()
{
    Schema::table('rekam', function (Blueprint $table) {
        $table->dropColumn('file');
    });
}
}

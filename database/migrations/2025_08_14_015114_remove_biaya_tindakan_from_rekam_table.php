<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveBiayaTindakanFromRekamTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
{
    Schema::table('rekam', function (Blueprint $table) {
        $table->dropColumn('biaya_tindakan');
    });
}

public function down()
{
    Schema::table('rekam', function (Blueprint $table) {
        $table->integer('biaya_tindakan')->nullable();
    });
}

}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDeletedAtToPasienTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('pasien', function (Blueprint $table) {
        $table->softDeletes(); // menambah kolom deleted_at tipe timestamp nullable
    });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
       Schema::table('pasien', function (Blueprint $table) {
        $table->dropSoftDeletes(); // menghapus kolom deleted_at
    });
    }
}

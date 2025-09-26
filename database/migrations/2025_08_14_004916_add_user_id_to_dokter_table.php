<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('dokter', function (Blueprint $table) {
            // Tambahkan kolom user_id
            $table->unsignedBigInteger('user_id')->after('id');

            // Relasi ke tabel users
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::table('dokter', function (Blueprint $table) {
            // Hapus relasi dulu, baru kolomnya
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
        });
    }
};


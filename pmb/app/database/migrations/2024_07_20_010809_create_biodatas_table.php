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
        Schema::create('biodatas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
            $table->string('name');
            $table->string('nomor_hp')->min(10);
            $table->string('alamat');
            $table->date('tanggal_lahir');
            $table->string('nik')->min(16);
            $table->string('nama_orangtua');
            $table->string('nomor_hp_orangtua')->min(10);
            $table->string('nik_orangtua')->min(16);
            $table->string('hubungan');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('biodatas');
    }
};

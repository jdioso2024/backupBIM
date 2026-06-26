<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Jalankan migrasi.
     */
    public function up(): void
    {
        Schema::table('biodatas', function (Blueprint $table) {
            $table->string('tempat_lahir')->nullable();
            $table->enum('jenis_kelamin', ['Laki-laki', 'Perempuan'])->nullable();
            $table->string('asal_sekolah')->nullable();
            $table->string('nisn', 10)->nullable();
            $table->integer('no_kip')->nullable();
            $table->string('hubungan')->nullable()->change();
            $table->string('nik_orangtua')->nullable()->change();

            $table->string('parent_work')->nullable();
            $table->decimal('parent_income', 15, 2)->nullable();
            $table->string('emergency_contact')->nullable();
            $table->text('reason_scholarship')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('biodatas', function (Blueprint $table) {
            $table->dropColumn(['tempat_lahir', 'jenis_kelamin', 'asal_sekolah', 'nisn', 'no_kip', 'parent_work', 'parent_income', 'emergency_contact', 'reason_scholarship']);
        });
    }
};

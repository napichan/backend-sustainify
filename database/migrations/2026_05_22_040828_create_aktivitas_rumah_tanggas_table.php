<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
public function up(): void
{
    Schema::create('aktivitas_rumah_tangga', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->constrained()->onDelete('cascade');
        $table->foreignId('aktivitas_id')->constrained('rumah_tangga')->onDelete('cascade');
        $table->float('durasi_jam');
        $table->float('emisi_karbon');
        $table->date('tanggal');
        $table->timestamps();
    });
}

public function down(): void
{
    Schema::dropIfExists('aktivitas_rumah_tangga');
}
};

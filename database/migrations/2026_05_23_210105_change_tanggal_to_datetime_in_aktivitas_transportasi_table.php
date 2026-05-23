<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::statement('ALTER TABLE aktivitas_transportasi MODIFY COLUMN tanggal DATETIME NOT NULL');
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }

    public function down(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::statement('ALTER TABLE aktivitas_transportasi MODIFY COLUMN tanggal DATE NOT NULL');
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }
};
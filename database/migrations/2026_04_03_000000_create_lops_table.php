<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lops', function (Blueprint $table) {
            $table->id();
            $table->string('ma_lop', 50)->unique()->comment('Mã lớp');
            $table->string('ten_lop', 255)->comment('Tên lớp');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lops');
    }
};

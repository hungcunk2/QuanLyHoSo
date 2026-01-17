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
        Schema::create('classes', function (Blueprint $table) {
            $table->id();
            $table->string('ma_lop', 50)->unique()->comment('Mã lớp');
            $table->string('ten_lop', 255)->comment('Tên lớp');
            $table->unsignedBigInteger('giao_vien_chu_nhiem_id')->nullable()->comment('Giáo viên chủ nhiệm');
            $table->timestamps();
            $table->softDeletes();
            
            $table->foreign('giao_vien_chu_nhiem_id')->references('id')->on('teachers')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('classes');
    }
};

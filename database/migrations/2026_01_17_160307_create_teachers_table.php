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
        Schema::create('teachers', function (Blueprint $table) {
            $table->id();
            $table->string('msgv', 50)->unique()->comment('Mã số giáo viên');
            $table->string('ho_ten', 255)->comment('Họ và tên');
            $table->string('chuyen_mon', 255)->nullable()->comment('Chuyên môn');
            $table->string('sdt', 20)->nullable()->comment('Số điện thoại');
            $table->text('dia_chi')->nullable()->comment('Địa chỉ');
            $table->string('email', 255)->unique()->nullable()->comment('Email');
            $table->date('ngay_sinh')->nullable()->comment('Ngày sinh');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teachers');
    }
};

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
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'mssv',
                'name',
                'ho_ten',
                'lop',
                'so_dien_thoai',
                'ngay_sinh',
                'dia_chi',
                'ho_ten_cha',
                'sdt_cha',
                'ho_ten_me',
                'sdt_me',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('mssv', 50)->unique()->nullable()->comment('Mã số sinh viên/học sinh');
            $table->string('name', 255)->nullable();
            $table->string('ho_ten', 255)->nullable()->comment('Họ và tên');
            $table->string('lop', 50)->nullable()->comment('Lớp');
            $table->string('so_dien_thoai', 20)->nullable()->comment('Số điện thoại');
            $table->date('ngay_sinh')->nullable()->comment('Ngày sinh');
            $table->text('dia_chi')->nullable()->comment('Địa chỉ');
            $table->string('ho_ten_cha', 255)->nullable()->comment('Họ tên cha');
            $table->string('sdt_cha', 20)->nullable()->comment('Số điện thoại cha');
            $table->string('ho_ten_me', 255)->nullable()->comment('Họ tên mẹ');
            $table->string('sdt_me', 20)->nullable()->comment('Số điện thoại mẹ');
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('faculty_report_stats', function (Blueprint $table) {
            $table->id();

            /** Mã kỳ báo cáo, VD: HK1-2025-2026 */
            $table->string('hoc_ky', 64)->nullable();

            // Tổng sinh viên khoa theo năm học
            $table->unsignedInteger('sv_nam_1')->default(0);
            $table->unsignedInteger('sv_nam_2')->default(0);
            $table->unsignedInteger('sv_nam_3')->default(0);
            $table->unsignedInteger('sv_nam_4')->default(0);
            $table->unsignedInteger('sv_sau_nam_4')->default(0);

            // Cảnh báo học vụ kỳ hiện tại
            $table->unsignedInteger('canh_bao_lan_1')->default(0);
            $table->unsignedInteger('canh_bao_lan_2')->default(0);
            $table->unsignedInteger('nghi_hoc')->default(0);

            // Lớp học phần kỳ này
            $table->unsignedInteger('lop_hp_mo_ky_nay')->default(0);
            $table->unsignedInteger('lop_hp_da_chot_diem')->default(0);

            // Lớp đã chốt điểm — xếp loại điểm trung bình của lớp
            $table->unsignedInteger('lop_ca_lop_kha')->default(0);
            $table->unsignedInteger('lop_ca_lop_gioi')->default(0);
            $table->unsignedInteger('lop_ca_lop_trung_binh')->default(0);

            // Khác
            $table->unsignedInteger('sv_rot_mon')->default(0);
            $table->unsignedInteger('sv_bao_luu')->default(0);

            $table->text('ghi_chu')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('faculty_report_stats');
    }
};

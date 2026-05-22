@extends('layouts.admin')

@section('title', 'Quản lý đăng ký học phần')
@section('page-title', 'Quản lý đăng ký học phần')

@push('styles')
<style>
    #createCourseOfferingModal.show .modal-dialog {
        max-height: calc(100vh - 2rem);
        margin: 1rem auto;
    }
    #createCourseOfferingModal.show .modal-content {
        max-height: calc(100vh - 2rem);
        display: flex;
        flex-direction: column;
    }
    #createCourseOfferingModal.show .modal-body {
        flex: 1 1 auto;
        min-height: 0;
        overflow-y: auto;
        overflow-x: hidden;
        max-height: min(65vh, 600px);
        -webkit-overflow-scrolling: touch;
    }
    #createCourseOfferingModal.show.modal-edit .modal-dialog {
        max-width: 95vw;
        width: 95vw;
    }
    #createCourseOfferingModal.show.modal-edit .modal-body {
        max-height: 80vh;
    }

    #rescheduleModal.show .modal-dialog {
        max-width: 98vw;
        width: 98vw;
        margin: 0.75rem auto;
        max-height: calc(100vh - 1rem);
    }
    #rescheduleModal.show .modal-content {
        max-height: calc(100vh - 1rem);
    }
    #rescheduleModal.show .modal-body {
        padding: 1rem 1.25rem;
        overflow-y: auto;
    }
    #rescheduleModal.show #rsGridTable th,
    #rescheduleModal.show #rsGridTable td {
        min-width: 140px;
    }
    #rescheduleModal.show #rsGridTable th:first-child,
    #rescheduleModal.show #rsGridTable td:first-child {
        min-width: 90px;
        width: 90px;
    }
    #rescheduleModal.show #rsGridTable tbody tr {
        height: 260px;
    }
    #rescheduleModal.show .rs-slot {
        min-height: 9rem !important;
        padding: 1.1rem 1.1rem !important;
        border-radius: .65rem !important;
    }
    #rescheduleModal.show .rs-slot .fw-semibold {
        font-size: 1.15rem;
        line-height: 1.25;
    }
    #rescheduleModal.show .rs-slot .opacity-90 {
        font-size: 1.02rem !important;
        line-height: 1.3;
        margin-top: .35rem !important;
    }
    #rescheduleModal.show .rs-slot.border-warning {
        box-shadow: 0 0 0 .15rem rgba(255, 193, 7, .35);
    }

    @media (max-width: 767.98px) {
        #rescheduleModal.show #rsGridTable tbody tr {
            height: 120px;
        }
        #rescheduleModal.show .rs-slot {
            min-height: 5rem !important;
            padding: 0.65rem !important;
        }

        #registrationsTable_wrapper {
            min-height: 0 !important;
            height: auto !important;
        }

        #registrationsTable_wrapper .table-responsive {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-12">
            <div class="card card-block">
                <div class="card-body p-0">
                    <div class="d-flex justify-content-between align-items-center p-3 flex-wrap gap-3 admin-page-header">
                        <h5 class="fw-bold mb-0">Danh sách đăng ký học phần</h5>
                        <button type="button" class="btn btn-primary btn-create" id="btnOpenCreateOffering" data-bs-toggle="modal" data-bs-target="#createCourseOfferingModal">
                            <i class="fas fa-plus"></i> Tạo học phần mới
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="row gy-3 admin-toolbar">
            <div class="col-12">
                <div class="input-group input-group-search">
                    <span class="input-group-text" id="addon-wrapping"><i class="fas fa-search"></i></span>
                    <input type="text" class="form-control dt-search" placeholder="Tìm kiếm..."
                        aria-label="Search" aria-describedby="addon-wrapping" aria-controls="registrationsTable">
                </div>
            </div>
        </div>

        <div class="table-responsive mt-3 admin-table-wrap">
            <table id="registrationsTable" class="table table-striped border w-100 mb-0">
                <thead>
                    <tr>
                        <th>Thời gian tạo</th>
                        <th>Tên học phần</th>
                        <th>Môn học</th>
                        <th>Lớp</th>
                        <th>Giáo viên</th>
                        <th>Trạng thái</th>
                        <th>Thời gian học</th>
                        <th>Lịch học</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection

@push('modals')
<!-- Modal Tạo học phần mới -->
<div class="modal fade" id="createCourseOfferingModal" tabindex="-1" aria-labelledby="createCourseOfferingModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable" id="createCourseOfferingModalDialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createCourseOfferingModalLabel">Tạo học phần mới</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="createCourseOfferingForm">
                @csrf
                <input type="hidden" name="offering_id" id="offering_id" value="">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="hoc_ky" class="form-label">Học kì <span class="text-danger">*</span></label>
                            <select class="form-select" id="hoc_ky" name="hoc_ky" required>
                                <option value="1" selected>Học kì 1</option>
                                <option value="2">Học kì 2</option>
                                <option value="3">Học kì 3</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="khoa_hoc" class="form-label">Khóa học <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="khoa_hoc" name="khoa_hoc" required value="{{ now()->year }}-{{ now()->year + 1 }}">
                        </div>
                        <div class="col-md-6">
                            <label for="class_room_id" class="form-label">Phòng học <span class="text-danger">*</span></label>
                            <select class="form-select" id="class_room_id" name="class_room_id" required>
                                <option value="">-- Chọn lớp --</option>
                                @foreach($classes ?? [] as $c)
                                    <option value="{{ $c->id }}">{{ $c->ma_lop }} - {{ $c->ten_lop }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="subject_id" class="form-label">Môn học <span class="text-danger">*</span></label>
                            <select class="form-select" id="subject_id" name="subject_id" required>
                                <option value="">-- Chọn môn học --</option>
                                @foreach($subjects ?? [] as $s)
                                    <option value="{{ $s->id }}">{{ $s->ma_mon_hoc }} - {{ $s->ten_mon_hoc }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="ngay_mo_dang_ky" class="form-label">Ngày bắt đầu mở đăng ký <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="ngay_mo_dang_ky" name="ngay_mo_dang_ky" required>
                        </div>
                        <div class="col-md-6">
                            <label for="ngay_ket_thuc_dang_ky" class="form-label">Ngày kết thúc đăng ký <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="ngay_ket_thuc_dang_ky" name="ngay_ket_thuc_dang_ky" required>
                        </div>
                        <div class="col-md-6">
                            <label for="ngay_bat_dau_hoc" class="form-label">Ngày bắt đầu học <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="ngay_bat_dau_hoc" name="ngay_bat_dau_hoc" required>
                        </div>
                        <div class="col-md-6">
                            <label for="ngay_ket_thuc_hoc" class="form-label">Ngày kết thúc học <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="ngay_ket_thuc_hoc" name="ngay_ket_thuc_hoc" required>
                        </div>
                        <div class="col-12" id="preview-ngay-hoc-wrap" style="display: none;">
                            <div class="card border-primary">
                                <div class="card-header py-2 bg-light small fw-bold">Xem trước các ngày học</div>
                                <div class="card-body py-2 small" id="preview-ngay-hoc"></div>
                            </div>
                        </div>
                    </div>

                    <hr class="my-4">
                    <h6 class="text-primary mb-3"><i class="fas fa-book-reader me-1"></i> Buổi học lý thuyết</h6>
                    <p class="small text-muted mb-2">Thông thường 1 buổi học = 3 tiết liên tiếp. Dùng nút "Thêm buổi lý thuyết" để thêm nhiều buổi.</p>
                    <div id="buoi-ly-thuyet-list">
                        <div class="buoi-ly-thuyet-row border rounded p-3 mb-3 bg-light">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <span class="small fw-bold text-secondary">Buổi 1</span>
                                <button type="button" class="btn btn-sm btn-outline-danger remove-buoi-lt d-none" title="Xóa buổi này"><i class="fas fa-times"></i></button>
                            </div>
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label">Thứ trong tuần <span class="text-danger">*</span></label>
                                    <select class="form-select" name="thu_ly_thuyet[]" required>
                                        @foreach($weekdays ?? [] as $val => $label)
                                            <option value="{{ $val }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Giáo viên (Lý thuyết) <span class="text-danger">*</span></label>
                                    <select class="form-select" name="teacher_id_ly_thuyet[]" required>
                                        <option value="">-- Chọn giáo viên --</option>
                                        @foreach($teachers ?? [] as $t)
                                            <option value="{{ $t->id }}">{{ $t->msgv }} - {{ $t->ho_ten }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Tiết học (3 tiết liên tiếp) <span class="text-danger">*</span></label>
                                    <div class="mb-2">
                                        <div class="btn-group btn-group-sm flex-wrap" role="group">
                                            <button type="button" class="btn btn-outline-secondary quick-lt" data-start="1">1-3</button>
                                            <button type="button" class="btn btn-outline-secondary quick-lt" data-start="4">4-6</button>
                                            <button type="button" class="btn btn-outline-secondary quick-lt" data-start="7">7-9</button>
                                            <button type="button" class="btn btn-outline-secondary quick-lt" data-start="10">10-12</button>
                                            <button type="button" class="btn btn-outline-secondary quick-lt" data-start="13">13-15</button>
                                        </div>
                                    </div>
                                    <div class="border rounded p-2 bg-white">
                                        @foreach([1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16] as $i)
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input tiet-ly-thuyet" type="checkbox" value="{{ $i }}" id="lt_0_{{ $i }}">
                                                <label class="form-check-label small" for="lt_0_{{ $i }}">T{{ $i }}</label>
                                            </div>
                                        @endforeach
                                    </div>
                                    <input type="hidden" name="tiet_ly_thuyet[]" class="tiet-lt-hidden" value="">
                                </div>
                                <div class="col-md-4 mt-2">
                                    <label class="form-label">Thi lý thuyết vào buổi thứ mấy <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" name="ngay_thi_ly_thuyet_buoi_thu[]" min="1" required placeholder="VD: 5">
                                </div>
                            </div>
                        </div>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-primary" id="btn-them-buoi-ly-thuyet"><i class="fas fa-plus me-1"></i> Thêm buổi lý thuyết</button>
                    <div class="row mt-3" id="wrap-si-so-ly-thuyet">
                        <div class="col-md-4">
                            <label for="si_so_ly_thuyet" class="form-label">Sĩ số lớp (lý thuyết) <span class="text-danger si-so-lt-required">*</span></label>
                            <input type="number" class="form-control" id="si_so_ly_thuyet" name="si_so_ly_thuyet" min="1" placeholder="VD: 60">
                            <div class="form-text">Bắt buộc khi học phần không có nhóm thực hành.</div>
                        </div>
                    </div>

                    <!-- Template clone cho buổi lý thuyết (ẩn) -->
                    <template id="tpl-buoi-ly-thuyet">
                        <div class="buoi-ly-thuyet-row border rounded p-3 mb-3 bg-light">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <span class="small fw-bold text-secondary buoi-lt-label">Buổi 2</span>
                                <button type="button" class="btn btn-sm btn-outline-danger remove-buoi-lt" title="Xóa buổi này"><i class="fas fa-times"></i></button>
                            </div>
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label">Thứ trong tuần <span class="text-danger">*</span></label>
                                    <select class="form-select" name="thu_ly_thuyet[]" required>
                                        @foreach($weekdays ?? [] as $val => $label)
                                            <option value="{{ $val }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Giáo viên (Lý thuyết) <span class="text-danger">*</span></label>
                                    <select class="form-select" name="teacher_id_ly_thuyet[]" required>
                                        <option value="">-- Chọn giáo viên --</option>
                                        @foreach($teachers ?? [] as $t)
                                            <option value="{{ $t->id }}">{{ $t->msgv }} - {{ $t->ho_ten }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Tiết học (3 tiết liên tiếp) <span class="text-danger">*</span></label>
                                    <div class="mb-2">
                                        <div class="btn-group btn-group-sm flex-wrap" role="group">
                                            <button type="button" class="btn btn-outline-secondary quick-lt" data-start="1">1-3</button>
                                            <button type="button" class="btn btn-outline-secondary quick-lt" data-start="4">4-6</button>
                                            <button type="button" class="btn btn-outline-secondary quick-lt" data-start="7">7-9</button>
                                            <button type="button" class="btn btn-outline-secondary quick-lt" data-start="10">10-12</button>
                                            <button type="button" class="btn btn-outline-secondary quick-lt" data-start="13">13-15</button>
                                        </div>
                                    </div>
                                    <div class="border rounded p-2 bg-white tiet-lt-checkboxes"></div>
                                    <input type="hidden" name="tiet_ly_thuyet[]" class="tiet-lt-hidden" value="">
                                </div>
                                <div class="col-md-4 mt-2">
                                    <label class="form-label">Thi lý thuyết vào buổi thứ mấy <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" name="ngay_thi_ly_thuyet_buoi_thu[]" min="1" required placeholder="VD: 5">
                                </div>
                            </div>
                        </div>
                    </template>

                    <hr class="my-4">
                    <h6 class="text-primary mb-2"><i class="fas fa-flask me-1"></i> Nhóm học thực hành</h6>
                    <input type="hidden" id="si_so_lop" name="si_so_lop" value="">
                    <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
                        <button type="button" class="btn btn-sm btn-outline-primary" id="btn-them-buoi-thuc-hanh"><i class="fas fa-plus me-1"></i> Thêm nhóm thực hành</button>
                        <span class="small text-muted">Có nhóm TH: sĩ số = tổng các nhóm. Không có TH: nhập sĩ số ở mục lý thuyết.</span>
                    </div>
                    <div id="blockThucHanh">
                        <div id="buoi-thuc-hanh-list">
                            <div class="buoi-thuc-hanh-row border rounded p-3 mb-3 bg-light">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <span class="small fw-bold text-secondary">Nhóm TH 1</span>
                                    <button type="button" class="btn btn-sm btn-outline-danger remove-buoi-th d-none" title="Xóa nhóm này"><i class="fas fa-times"></i></button>
                                </div>
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label class="form-label">Thứ trong tuần</label>
                                        <select class="form-select" name="thu_thuc_hanh[]">
                                            <option value="">-- Chọn thứ --</option>
                                            @foreach($weekdays ?? [] as $val => $label)
                                                <option value="{{ $val }}">{{ $label }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Giáo viên (Thực hành)</label>
                                        <select class="form-select" name="teacher_id_thuc_hanh[]">
                                            <option value="">-- Chọn giáo viên --</option>
                                            @foreach($teachers ?? [] as $t)
                                                <option value="{{ $t->id }}">{{ $t->msgv }} - {{ $t->ho_ten }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Phòng học (Thực hành)</label>
                                        <select class="form-select" name="class_room_id_thuc_hanh[]">
                                            <option value="">-- Chọn phòng --</option>
                                            @foreach($classes ?? [] as $c)
                                                <option value="{{ $c->id }}">{{ $c->ma_lop }} - {{ $c->ten_lop }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Tiết (3 tiết liên tiếp)</label>
                                        <div class="mb-2">
                                            <div class="btn-group btn-group-sm flex-wrap" role="group">
                                                <button type="button" class="btn btn-outline-secondary quick-th" data-start="1">1-3</button>
                                                <button type="button" class="btn btn-outline-secondary quick-th" data-start="4">4-6</button>
                                                <button type="button" class="btn btn-outline-secondary quick-th" data-start="7">7-9</button>
                                                <button type="button" class="btn btn-outline-secondary quick-th" data-start="10">10-12</button>
                                                <button type="button" class="btn btn-outline-secondary quick-th" data-start="13">13-15</button>
                                            </div>
                                        </div>
                                        <div class="border rounded p-2 bg-white">
                                            @foreach([1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16] as $i)
                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input tiet-thuc-hanh" type="checkbox" value="{{ $i }}" id="th_0_{{ $i }}">
                                                    <label class="form-check-label small" for="th_0_{{ $i }}">T{{ $i }}</label>
                                                </div>
                                            @endforeach
                                        </div>
                                        <input type="hidden" name="tiet_thuc_hanh[]" class="tiet-th-hidden" value="">
                                    </div>
                                    <div class="col-md-4 mt-2">
                                        <label class="form-label">Thi TH vào buổi thứ mấy</label>
                                        <input type="number" class="form-control" name="ngay_thi_thuc_hanh_buoi_thu[]" min="1" placeholder="VD: 3">
                                    </div>
                                    <div class="col-md-4 mt-2">
                                        <label class="form-label">Sĩ số</label>
                                        <input type="number" class="form-control" name="si_so_thuc_hanh[]" min="1" placeholder="VD: 25">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <template id="tpl-buoi-thuc-hanh">
                            <div class="buoi-thuc-hanh-row border rounded p-3 mb-3 bg-light">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <span class="small fw-bold text-secondary buoi-th-label">Nhóm TH 2</span>
                                    <button type="button" class="btn btn-sm btn-outline-danger remove-buoi-th" title="Xóa nhóm này"><i class="fas fa-times"></i></button>
                                </div>
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label class="form-label">Thứ trong tuần</label>
                                        <select class="form-select" name="thu_thuc_hanh[]">
                                            <option value="">-- Chọn thứ --</option>
                                            @foreach($weekdays ?? [] as $val => $label)
                                                <option value="{{ $val }}">{{ $label }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Giáo viên (Thực hành)</label>
                                        <select class="form-select" name="teacher_id_thuc_hanh[]">
                                            <option value="">-- Chọn giáo viên --</option>
                                            @foreach($teachers ?? [] as $t)
                                                <option value="{{ $t->id }}">{{ $t->msgv }} - {{ $t->ho_ten }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Phòng học (Thực hành)</label>
                                        <select class="form-select" name="class_room_id_thuc_hanh[]">
                                            <option value="">-- Chọn phòng --</option>
                                            @foreach($classes ?? [] as $c)
                                                <option value="{{ $c->id }}">{{ $c->ma_lop }} - {{ $c->ten_lop }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Tiết (3 tiết liên tiếp)</label>
                                        <div class="mb-2">
                                            <div class="btn-group btn-group-sm flex-wrap" role="group">
                                                <button type="button" class="btn btn-outline-secondary quick-th" data-start="1">1-3</button>
                                                <button type="button" class="btn btn-outline-secondary quick-th" data-start="4">4-6</button>
                                                <button type="button" class="btn btn-outline-secondary quick-th" data-start="7">7-9</button>
                                                <button type="button" class="btn btn-outline-secondary quick-th" data-start="10">10-12</button>
                                                <button type="button" class="btn btn-outline-secondary quick-th" data-start="13">13-15</button>
                                            </div>
                                        </div>
                                        <div class="border rounded p-2 bg-white tiet-th-checkboxes"></div>
                                        <input type="hidden" name="tiet_thuc_hanh[]" class="tiet-th-hidden" value="">
                                    </div>
                                    <div class="col-md-4 mt-2">
                                        <label class="form-label">Thi TH vào buổi thứ mấy</label>
                                        <input type="number" class="form-control" name="ngay_thi_thuc_hanh_buoi_thu[]" min="1" placeholder="VD: 3">
                                    </div>
                                    <div class="col-md-4 mt-2">
                                        <label class="form-label">Sĩ số</label>
                                        <input type="number" class="form-control" name="si_so_thuc_hanh[]" min="1" placeholder="VD: 25">
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    <button type="submit" class="btn btn-primary" id="submitCourseOfferingBtn">Tạo học phần</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Dời lịch -->
<div class="modal fade" id="rescheduleModal" tabindex="-1" aria-labelledby="rescheduleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="rescheduleModalLabel">Dời lịch học phần</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-2 fw-bold" id="rsOfferingName"></div>
                <div class="small text-muted mb-3" id="rsOfferingMeta"></div>

                <div class="row g-3">
                    <div class="col-lg-7">
                        <div class="border rounded">
                            <div class="p-2 border-bottom bg-light d-flex align-items-center justify-content-between gap-2 flex-wrap">
                                <div class="small fw-bold">Lịch tuần (bấm vào buổi cần dời)</div>
                                <div class="d-flex align-items-center gap-2">
                                    <button type="button" class="btn btn-outline-secondary btn-sm" id="rsPrevWeek">&lt; Tuần trước</button>
                                    <div class="small text-muted" id="rsWeekLabel"></div>
                                    <button type="button" class="btn btn-outline-secondary btn-sm" id="rsNextWeek">Tuần sau &gt;</button>
                                </div>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-bordered mb-0 text-center align-middle" id="rsGridTable">
                                    <thead>
                                        <tr style="background-color:#F3F7F9;">
                                            <th style="width:80px;">Ca</th>
                                            <th class="rs-day-head" data-d="0">Thứ 2</th>
                                            <th class="rs-day-head" data-d="1">Thứ 3</th>
                                            <th class="rs-day-head" data-d="2">Thứ 4</th>
                                            <th class="rs-day-head" data-d="3">Thứ 5</th>
                                            <th class="rs-day-head" data-d="4">Thứ 6</th>
                                            <th class="rs-day-head" data-d="5">Thứ 7</th>
                                            <th class="rs-day-head" data-d="6">CN</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr data-session="morning">
                                            <th class="text-start ps-3 align-top pt-3" style="background-color: rgb(255,255,206);">Sáng</th>
                                            @for($i=0;$i<7;$i++) <td class="align-top p-2 rs-cell" data-d="{{ $i }}"></td> @endfor
                                        </tr>
                                        <tr data-session="afternoon">
                                            <th class="text-start ps-3 align-top pt-3" style="background-color: rgb(255,255,206);">Chiều</th>
                                            @for($i=0;$i<7;$i++) <td class="align-top p-2 rs-cell" data-d="{{ $i }}"></td> @endfor
                                        </tr>
                                        <tr data-session="evening">
                                            <th class="text-start ps-3 align-top pt-3" style="background-color: rgb(255,255,206);">Tối</th>
                                            @for($i=0;$i<7;$i++) <td class="align-top p-2 rs-cell" data-d="{{ $i }}"></td> @endfor
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div class="px-3 py-2 border-top bg-white small">
                                <span class="badge" style="background-color:#3498db;color:#fff;">&nbsp;</span> LT
                                <span class="badge ms-3" style="background-color:#27ae60;color:#fff;">&nbsp;</span> TH
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-5">
                        <div class="border rounded p-3">
                            <div class="fw-bold mb-2">Thao tác</div>
                            <div class="small text-muted mb-2">Chọn 1 buổi ở bảng bên trái để dời.</div>

                            <input type="hidden" id="rsOfferingId" value="">
                            <input type="hidden" id="rsSessionKey" value="">
                            <input type="hidden" id="rsDateOld" value="">

                            <div class="mb-2">
                                <div class="small fw-bold">Buổi đang chọn</div>
                                <div id="rsSelectedLabel" class="text-muted small">—</div>
                            </div>

                            <div class="row g-2">
                                <div class="col-md-6">
                                    <label class="form-label small mb-1">Ngày dời (cố định)</label>
                                    <input type="date" class="form-control form-control-sm" id="rsDateNew">
                                    <div class="small text-muted mt-1" id="rsDateNewHint"></div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small mb-1">Tiết mới</label>
                                    <input type="text" class="form-control form-control-sm" id="rsTiet" placeholder="VD: 10,11,12">
                                </div>
                            </div>

                            <div class="mt-2">
                                <div class="btn-group btn-group-sm flex-wrap" role="group">
                                    <button type="button" class="btn btn-outline-secondary rs-quick" data-start="1">1-3</button>
                                    <button type="button" class="btn btn-outline-secondary rs-quick" data-start="4">4-6</button>
                                    <button type="button" class="btn btn-outline-secondary rs-quick" data-start="7">7-9</button>
                                    <button type="button" class="btn btn-outline-secondary rs-quick" data-start="10">10-12</button>
                                    <button type="button" class="btn btn-outline-secondary rs-quick" data-start="13">13-15</button>
                                </div>
                            </div>

                            <div class="text-danger small mt-2 d-none" id="rsError"></div>
                            <div class="mt-3 d-grid">
                                <button type="button" class="btn btn-warning" id="btnSaveReschedule">
                                    Lưu dời lịch
                                </button>
                                <button type="button" class="btn btn-outline-danger mt-2" id="btnForceReschedule">
                                    Ép lịch
                                </button>
                                <button type="button" class="btn btn-danger mt-2" id="btnPauseSession">
                                    Tạm ngưng buổi này
                                </button>
                                <button type="button" class="btn btn-outline-secondary mt-2 d-none" id="btnUnpauseSession">
                                    Bỏ tạm ngưng
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Xác nhận xóa học phần -->
<div class="modal fade" id="deleteCourseOfferingModal" tabindex="-1" aria-labelledby="deleteCourseOfferingModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteCourseOfferingModalLabel">Xác nhận xóa học phần</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
            </div>
            <div class="modal-body">
                <p class="mb-0">Bạn có chắc muốn xóa học phần <strong id="deleteOfferingName"></strong>?</p>
                <p class="text-danger small mt-2 mb-0">Các đăng ký của học sinh/sinh viên liên quan sẽ bị xóa theo. Không thể hoàn tác.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteOfferingBtn">Xóa</button>
            </div>
        </div>
    </div>
</div>

@endpush

@push('scripts')
<script>
    $(document).ready(function() {
        var table = $('#registrationsTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route("admin.subject-registrations.data") }}',
                type: 'GET',
                data: function(d) {
                    d.search = $('.dt-search').val();
                }
            },
            columns: [
                {
                    data: 'created_at_formatted',
                    name: 'created_at',
                    orderable: true,
                    searchable: false
                },
                {
                    data: 'ten_hoc_phan',
                    name: 'ten_hoc_phan',
                    orderable: true,
                    searchable: true
                },
                {
                    data: 'subject_info',
                    name: 'subject_info',
                    orderable: false,
                    searchable: true
                },
                {
                    data: 'class_info',
                    name: 'class_info',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'teacher_info',
                    name: 'teacher_info',
                    orderable: false,
                    searchable: true
                },
                {
                    data: 'offering_status',
                    name: 'offering_status',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'date_range',
                    name: 'date_range',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'schedule_summary',
                    name: 'schedule_summary',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'action',
                    name: 'action',
                    orderable: false,
                    searchable: false
                }
            ],
            responsive: false,
            scrollX: false,
            autoWidth: false,
            order: [[0, 'desc']],
            pageLength: 10,
            language: {
                processing: "Đang xử lý...",
                search: "Tìm kiếm:",
                lengthMenu: "Hiển thị _MENU_ bản ghi",
                info: "Hiển thị _START_ đến _END_ trong tổng số _TOTAL_ bản ghi",
                infoEmpty: "Hiển thị 0 đến 0 trong tổng số 0 bản ghi",
                infoFiltered: "(lọc từ _MAX_ tổng số bản ghi)",
                paginate: {
                    first: "Đầu",
                    last: "Cuối",
                    next: "Sau",
                    previous: "Trước"
                },
                emptyTable: "Chưa có học phần nào. Bấm \"Tạo học phần mới\" để thêm.",
                zeroRecords: "Không tìm thấy kết quả"
            },
            dom: AdminDT.dom
        });

        $('.dt-search').on('keyup', function() {
            table.search(this.value).draw();
        });

        // Xem trước các ngày học: thứ (2-8 VN) → getDay() (0=CN, 1=T2, ..., 6=T7)
        var thuVNToJS = { 2: 1, 3: 2, 4: 3, 5: 4, 6: 5, 7: 6, 8: 0 };
        function getDatesForWeekday(weekdayJS, startStr, endStr) {
            if (!startStr || !endStr) return [];
            var start = new Date(startStr), end = new Date(endStr);
            if (isNaN(start.getTime()) || isNaN(end.getTime()) || start > end) return [];
            var d = new Date(start);
            if (d.getDay() !== weekdayJS) {
                var diff = (weekdayJS - d.getDay() + 7) % 7;
                d.setDate(d.getDate() + diff);
            }
            var list = [];
            while (d <= end) {
                list.push(new Date(d));
                d.setDate(d.getDate() + 7);
            }
            return list;
        }
        function fmt(d) { return ('0' + d.getDate()).slice(-2) + '/' + ('0' + (d.getMonth()+1)).slice(-2) + '/' + d.getFullYear(); }
        function updatePreviewNgayHoc() {
            var startStr = $('#ngay_bat_dau_hoc').val(), endStr = $('#ngay_ket_thuc_hoc').val();
            var html = [];
            var firstLt = $('#buoi-ly-thuyet-list .buoi-ly-thuyet-row').first();
            var thuLt = firstLt.find('select[name="thu_ly_thuyet[]"]').val();
            var tietLt = firstLt.find('.tiet-lt-hidden').val();
            if (thuLt && tietLt && startStr && endStr) {
                var jsDay = thuVNToJS[parseInt(thuLt, 10)];
                var dates = getDatesForWeekday(jsDay, startStr, endStr);
                var label = firstLt.find('select[name="thu_ly_thuyet[]"] option:selected').text();
                html.push('<strong>Lý thuyết</strong> (' + label + ', tiết ' + tietLt + '): ' + (dates.length ? dates.slice(0, 10).map(fmt).join(', ') + (dates.length > 10 ? ' … (tổng ' + dates.length + ' buổi)' : '') : '—'));
            }
            var firstTh = $('#buoi-thuc-hanh-list .buoi-thuc-hanh-row').first();
            var thuTh = firstTh.find('select[name="thu_thuc_hanh[]"]').val();
            var tietTh = firstTh.find('.tiet-th-hidden').val();
            if (thuTh && tietTh && startStr && endStr) {
                var jsDay = thuVNToJS[parseInt(thuTh, 10)];
                var dates = getDatesForWeekday(jsDay, startStr, endStr);
                var label = firstTh.find('select[name="thu_thuc_hanh[]"] option:selected').text();
                html.push('<strong>Thực hành</strong> (' + label + ', tiết ' + tietTh + '): ' + (dates.length ? dates.slice(0, 10).map(fmt).join(', ') + (dates.length > 10 ? ' … (tổng ' + dates.length + ' buổi)' : '') : '—'));
            }
            if (html.length) {
                $('#preview-ngay-hoc').html(html.join('<br>'));
                $('#preview-ngay-hoc-wrap').show();
            } else {
                $('#preview-ngay-hoc-wrap').hide();
            }
        }
        $('#ngay_bat_dau_hoc, #ngay_ket_thuc_hoc').on('change', updatePreviewNgayHoc);
        $(document).on('change', '#buoi-ly-thuyet-list select[name="thu_ly_thuyet[]"], #buoi-thuc-hanh-list select[name="thu_thuc_hanh[]"]', updatePreviewNgayHoc);
        $(document).on('change', '.tiet-ly-thuyet, .tiet-thuc-hanh', function() {
            setTimeout(updatePreviewNgayHoc, 50);
        });
        $(document).on('click', '.quick-lt, .quick-th', function() {
            setTimeout(updatePreviewNgayHoc, 100);
        });

        // Sync tiết theo từng buổi (lý thuyết)
        function syncAllTietLyThuyet() {
            $('#buoi-ly-thuyet-list .buoi-ly-thuyet-row').each(function() {
                var arr = [];
                $(this).find('.tiet-ly-thuyet:checked').each(function() { arr.push($(this).val()); });
                $(this).find('.tiet-lt-hidden').val(arr.sort(function(a,b){return a-b}).join(','));
            });
        }
        $(document).on('change', '.tiet-ly-thuyet', syncAllTietLyThuyet);

        function syncAllTietThucHanh() {
            $('#buoi-thuc-hanh-list .buoi-thuc-hanh-row').each(function() {
                var arr = [];
                $(this).find('.tiet-thuc-hanh:checked').each(function() { arr.push($(this).val()); });
                $(this).find('.tiet-th-hidden').val(arr.sort(function(a,b){return a-b}).join(','));
            });
        }
        $(document).on('change', '.tiet-thuc-hanh', function () {
            syncAllTietThucHanh();
            syncSiSoCapacityUi();
        });

        function countActiveThGroups() {
            var n = 0;
            $('#buoi-thuc-hanh-list .buoi-thuc-hanh-row').each(function () {
                var thu = $(this).find('select[name="thu_thuc_hanh[]"]').val();
                var tiet = $(this).find('.tiet-th-hidden').val();
                if (thu && tiet) n++;
            });
            return n;
        }

        function computeSiSoLop() {
            var totalTh = 0;
            $('#buoi-thuc-hanh-list .buoi-thuc-hanh-row').each(function () {
                var thu = $(this).find('select[name="thu_thuc_hanh[]"]').val();
                var tiet = $(this).find('.tiet-th-hidden').val();
                if (!thu || !tiet) return;
                var n = parseInt($(this).find('input[name="si_so_thuc_hanh[]"]').val() || '0', 10);
                if (!isNaN(n)) totalTh += n;
            });
            if (totalTh > 0) return totalTh;
            var lt = parseInt($('#si_so_ly_thuyet').val() || '0', 10);
            return (!isNaN(lt) && lt > 0) ? lt : 0;
        }

        function syncSiSoCapacityUi() {
            var hasTh = countActiveThGroups() > 0;
            var $wrap = $('#wrap-si-so-ly-thuyet');
            var $lt = $('#si_so_ly_thuyet');
            if (hasTh) {
                $wrap.hide();
                $lt.prop('required', false);
            } else {
                $wrap.show();
                $lt.prop('required', true);
            }
        }

        $(document).on('change', '#buoi-thuc-hanh-list select[name="thu_thuc_hanh[]"]', syncSiSoCapacityUi);
        syncSiSoCapacityUi();

        // Chọn nhanh 3 tiết - chỉ trong cùng 1 buổi (delegate)
        $(document).on('click', '.quick-lt', function() {
            var row = $(this).closest('.buoi-ly-thuyet-row');
            var start = parseInt($(this).data('start'), 10);
            row.find('.tiet-ly-thuyet').prop('checked', false);
            for (var i = start; i < start + 3 && i <= 16; i++) {
                row.find('.tiet-ly-thuyet[value="' + i + '"]').prop('checked', true);
            }
            syncAllTietLyThuyet();
        });
        $(document).on('click', '.quick-th', function() {
            var row = $(this).closest('.buoi-thuc-hanh-row');
            var start = parseInt($(this).data('start'), 10);
            row.find('.tiet-thuc-hanh').prop('checked', false);
            for (var i = start; i < start + 3 && i <= 16; i++) {
                row.find('.tiet-thuc-hanh[value="' + i + '"]').prop('checked', true);
            }
            syncAllTietThucHanh();
            syncSiSoCapacityUi();
        });

        // Thêm buổi lý thuyết
        var buoiLtCounter = 0;
        $('#btn-them-buoi-ly-thuyet').on('click', function() {
            var tpl = document.getElementById('tpl-buoi-ly-thuyet');
            var clone = $(tpl.content.cloneNode(true));
            buoiLtCounter++;
            var rowNum = $('#buoi-ly-thuyet-list .buoi-ly-thuyet-row').length;
            clone.find('.buoi-lt-label').text('Buổi ' + (rowNum + 1));
            var cbHtml = '';
            for (var i = 1; i <= 16; i++) {
                cbHtml += '<div class="form-check form-check-inline"><input class="form-check-input tiet-ly-thuyet" type="checkbox" value="' + i + '" id="lt_' + rowNum + '_' + i + '"><label class="form-check-label small" for="lt_' + rowNum + '_' + i + '">T' + i + '</label></div>';
            }
            clone.find('.tiet-lt-checkboxes').html(cbHtml);
            $('#buoi-ly-thuyet-list').append(clone);
            $('#buoi-ly-thuyet-list .buoi-ly-thuyet-row').each(function(i) {
                $(this).find('.buoi-lt-label').first().text('Buổi ' + (i + 1));
            });
            $('#buoi-ly-thuyet-list .remove-buoi-lt').removeClass('d-none');
        });
        $(document).on('click', '.remove-buoi-lt', function() {
            if ($('#buoi-ly-thuyet-list .buoi-ly-thuyet-row').length <= 1) return;
            $(this).closest('.buoi-ly-thuyet-row').remove();
            $('#buoi-ly-thuyet-list .buoi-ly-thuyet-row').each(function(i) {
                $(this).find('.buoi-lt-label').first().text('Buổi ' + (i + 1));
            });
            if ($('#buoi-ly-thuyet-list .buoi-ly-thuyet-row').length === 1) {
                $('#buoi-ly-thuyet-list .remove-buoi-lt').addClass('d-none');
            }
        });

        // Thêm nhóm thực hành
        $('#btn-them-buoi-thuc-hanh').on('click', function() {
            var tpl = document.getElementById('tpl-buoi-thuc-hanh');
            var clone = $(tpl.content.cloneNode(true));
            var rowNum = $('#buoi-thuc-hanh-list .buoi-thuc-hanh-row').length;
            clone.find('.buoi-th-label').text('Nhóm TH ' + (rowNum + 1));
            var cbHtml = '';
            for (var i = 1; i <= 16; i++) {
                cbHtml += '<div class="form-check form-check-inline"><input class="form-check-input tiet-thuc-hanh" type="checkbox" value="' + i + '" id="th_' + rowNum + '_' + i + '"><label class="form-check-label small" for="th_' + rowNum + '_' + i + '">T' + i + '</label></div>';
            }
            clone.find('.tiet-th-checkboxes').html(cbHtml);
            $('#buoi-thuc-hanh-list').append(clone);
            $('#buoi-thuc-hanh-list .buoi-thuc-hanh-row').each(function(i) {
                $(this).find('.buoi-th-label').first().text('Nhóm TH ' + (i + 1));
            });
            $('#buoi-thuc-hanh-list .remove-buoi-th').removeClass('d-none');
            syncSiSoCapacityUi();
        });
        $(document).on('click', '.remove-buoi-th', function() {
            if ($('#buoi-thuc-hanh-list .buoi-thuc-hanh-row').length <= 1) return;
            $(this).closest('.buoi-thuc-hanh-row').remove();
            $('#buoi-thuc-hanh-list .buoi-thuc-hanh-row').each(function(i) {
                $(this).find('.buoi-th-label').first().text('Nhóm TH ' + (i + 1));
            });
            if ($('#buoi-thuc-hanh-list .buoi-thuc-hanh-row').length === 1) {
                $('#buoi-thuc-hanh-list .remove-buoi-th').addClass('d-none');
            }
            syncSiSoCapacityUi();
        });

        $('#btnOpenCreateOffering').on('click', function() {
            $('#offering_id').val('');
            $('#createCourseOfferingModal').removeClass('modal-edit');
            $('#createCourseOfferingModalLabel').text('Tạo học phần mới');
            $('#submitCourseOfferingBtn').text('Tạo học phần');
            $('#hoc_ky').val('1');
            $('#khoa_hoc').val('{{ now()->year }}-{{ now()->year + 1 }}');
            $('#si_so_ly_thuyet').val('');
            $('#si_so_lop').val('');
            syncSiSoCapacityUi();
        });

        // Chỉnh sửa học phần
        $(document).on('click', '.edit-offering-btn', function() {
            var id = $(this).data('id');
            $('#offering_id').val(id);
            $('#createCourseOfferingModal').addClass('modal-edit');
            $('#createCourseOfferingModalLabel').text('Chỉnh sửa học phần');
            $('#submitCourseOfferingBtn').text('Cập nhật');
            $.ajax({
                url: '{{ url("admin/course-offerings") }}/' + id,
                type: 'GET',
                success: function(res) {
                    $('#hoc_ky').val((res.hoc_ky || '1').toString());
                    $('#khoa_hoc').val(res.khoa_hoc || '{{ now()->year }}-{{ now()->year + 1 }}');
                    $('#class_room_id').val(res.class_room_id);
                    var thRooms = res.class_room_id_thuc_hanh || [];
                    $('#subject_id').val(res.subject_id);
                    var thSizes = res.si_so_thuc_hanh || [];
                    $('#si_so_lop').val(res.si_so_lop || '');
                    $('#si_so_ly_thuyet').val('');
                    $('#ngay_mo_dang_ky').val(res.ngay_mo_dang_ky);
                    $('#ngay_ket_thuc_dang_ky').val(res.ngay_ket_thuc_dang_ky);
                    $('#ngay_bat_dau_hoc').val(res.ngay_bat_dau_hoc);
                    $('#ngay_ket_thuc_hoc').val(res.ngay_ket_thuc_hoc);

                    var thuLt = res.thu_ly_thuyet || [];
                    var tietLt = res.tiet_ly_thuyet || [];
                    var gvLt = res.teacher_id_ly_thuyet || [];
                    var thiLt = res.ngay_thi_ly_thuyet_buoi_thu || [];
                    while ($('#buoi-ly-thuyet-list .buoi-ly-thuyet-row').length > 1) {
                        $('#buoi-ly-thuyet-list .buoi-ly-thuyet-row').last().find('.remove-buoi-lt').click();
                    }
                    for (var i = 1; i < thuLt.length; i++) {
                        $('#btn-them-buoi-ly-thuyet').click();
                    }
                    $('#buoi-ly-thuyet-list .buoi-ly-thuyet-row').each(function(idx) {
                        if (idx >= thuLt.length) return;
                        var row = $(this);
                        row.find('select[name="thu_ly_thuyet[]"]').val(thuLt[idx]);
                        row.find('select[name="teacher_id_ly_thuyet[]"]').val(gvLt[idx] || '');
                        row.find('input[name="ngay_thi_ly_thuyet_buoi_thu[]"]').val(thiLt[idx] || '');
                        var tietStr = (tietLt[idx] || '').toString();
                        row.find('.tiet-ly-thuyet').prop('checked', false);
                        if (tietStr) {
                            tietStr.split(',').forEach(function(t) {
                                row.find('.tiet-ly-thuyet[value="' + t.trim() + '"]').prop('checked', true);
                            });
                        }
                        row.find('.buoi-lt-label').first().text('Buổi ' + (idx + 1));
                    });
                    if (thuLt.length > 1) $('#buoi-ly-thuyet-list .remove-buoi-lt').removeClass('d-none');

                    var thuTh = res.thu_thuc_hanh || [];
                    var tietTh = res.tiet_thuc_hanh || [];
                    var gvTh = res.teacher_id_thuc_hanh || [];
                    var thiTh = res.ngay_thi_thuc_hanh_buoi_thu || [];
                    while ($('#buoi-thuc-hanh-list .buoi-thuc-hanh-row').length > 1) {
                        $('#buoi-thuc-hanh-list .buoi-thuc-hanh-row').last().find('.remove-buoi-th').click();
                    }
                    for (var j = 1; j < thuTh.length; j++) {
                        $('#btn-them-buoi-thuc-hanh').click();
                    }
                    $('#buoi-thuc-hanh-list .buoi-thuc-hanh-row').each(function(idx) {
                        if (idx >= thuTh.length) return;
                        var rowTh = $(this);
                        rowTh.find('select[name="thu_thuc_hanh[]"]').val(thuTh[idx] || '');
                        rowTh.find('select[name="teacher_id_thuc_hanh[]"]').val(gvTh[idx] || '');
                        rowTh.find('select[name="class_room_id_thuc_hanh[]"]').val(thRooms[idx] || '');
                        rowTh.find('input[name="ngay_thi_thuc_hanh_buoi_thu[]"]').val(thiTh[idx] || '');
                        rowTh.find('input[name="si_so_thuc_hanh[]"]').val(thSizes[idx] || '');
                        var tietStrTh = (tietTh[idx] || '').toString();
                        rowTh.find('.tiet-thuc-hanh').prop('checked', false);
                        if (tietStrTh) {
                            tietStrTh.split(',').forEach(function(t) {
                                rowTh.find('.tiet-thuc-hanh[value="' + t.trim() + '"]').prop('checked', true);
                            });
                        }
                        rowTh.find('.buoi-th-label').first().text('Nhóm TH ' + (idx + 1));
                    });
                    if (thuTh.length > 1) $('#buoi-thuc-hanh-list .remove-buoi-th').removeClass('d-none');
                    syncAllTietLyThuyet();
                    syncAllTietThucHanh();
                    syncSiSoCapacityUi();
                    if (countActiveThGroups() === 0) {
                        $('#si_so_ly_thuyet').val(res.si_so_ly_thuyet != null ? res.si_so_ly_thuyet : (res.si_so_lop || ''));
                    }

                    var modal = new bootstrap.Modal(document.getElementById('createCourseOfferingModal'));
                    modal.show();
                },
                error: function() {
                    alert('Không thể tải thông tin học phần.');
                }
            });
        });

        var deleteOfferingId = null;
        $(document).on('click', '.delete-offering-btn', function() {
            deleteOfferingId = $(this).data('id');
            var name = $(this).data('name') || '';
            $('#deleteOfferingName').text(name);
            new bootstrap.Modal(document.getElementById('deleteCourseOfferingModal')).show();
        });
        $('#confirmDeleteOfferingBtn').on('click', function() {
            if (!deleteOfferingId) return;
            $.ajax({
                url: '{{ url("admin/course-offerings") }}/' + deleteOfferingId,
                type: 'DELETE',
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                success: function(res) {
                    var delModal = bootstrap.Modal.getInstance(document.getElementById('deleteCourseOfferingModal'));
                    if (delModal) delModal.hide();
                    deleteOfferingId = null;
                    table.ajax.reload(null, false);
                    alert(res.message || 'Đã xóa.');
                },
                error: function(xhr) {
                    var msg = xhr.responseJSON?.message || 'Không thể xóa học phần.';
                    alert(msg);
                }
            });
        });

        // Submit form tạo / cập nhật học phần
        $('#createCourseOfferingForm').on('submit', function(e) {
            e.preventDefault();
            syncAllTietLyThuyet();
            syncAllTietThucHanh();
            syncSiSoCapacityUi();
            var siSoLop = computeSiSoLop();
            if (siSoLop < 1) {
                if (countActiveThGroups() > 0) {
                    alert('Vui lòng nhập sĩ số cho từng nhóm thực hành.');
                } else {
                    alert('Vui lòng nhập sĩ số lớp (lý thuyết).');
                }
                return;
            }
            $('#si_so_lop').val(siSoLop);
            var invalidLt = false;
            $('#buoi-ly-thuyet-list .buoi-ly-thuyet-row').each(function(i) {
                if (!$(this).find('.tiet-lt-hidden').val()) {
                    invalidLt = true;
                }
            });
            if (invalidLt) {
                alert('Vui lòng chọn 3 tiết liên tiếp cho từng buổi lý thuyết (dùng nút 1-3, 4-6, 7-9, 10-12 hoặc 13-15).');
                return;
            }
            var invalidLtTeacher = false;
            $('#buoi-ly-thuyet-list .buoi-ly-thuyet-row').each(function() {
                if (!$(this).find('select[name="teacher_id_ly_thuyet[]"]').val()) {
                    invalidLtTeacher = true;
                }
            });
            if (invalidLtTeacher) {
                alert('Vui lòng chọn giáo viên cho từng buổi lý thuyết.');
                return;
            }
            var data = $(this).serializeArray();
            var offeringId = $('#offering_id').val();
            var url = offeringId
                ? '{{ url("admin/course-offerings") }}/' + offeringId
                : '{{ route("admin.course-offerings.store") }}';
            var method = offeringId ? 'PUT' : 'POST';
            $.ajax({
                url: url,
                type: method,
                data: $.param(data),
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                success: function(res) {
                    var modal = bootstrap.Modal.getInstance(document.getElementById('createCourseOfferingModal'));
                    if (modal) modal.hide();
                    $('#offering_id').val('');
                    $('#createCourseOfferingForm')[0].reset();
                    $('#buoi-ly-thuyet-list .buoi-ly-thuyet-row:not(:first)').remove();
                    $('#buoi-ly-thuyet-list .buoi-ly-thuyet-row .tiet-ly-thuyet').prop('checked', false);
                    $('#buoi-ly-thuyet-list .tiet-lt-hidden').val('');
                    $('#buoi-ly-thuyet-list .remove-buoi-lt').addClass('d-none');
                    $('#buoi-thuc-hanh-list .buoi-thuc-hanh-row:not(:first)').remove();
                    $('#buoi-thuc-hanh-list .buoi-thuc-hanh-row .tiet-thuc-hanh').prop('checked', false);
                    $('#buoi-thuc-hanh-list .tiet-th-hidden').val('');
                    $('#buoi-thuc-hanh-list .remove-buoi-th').addClass('d-none');
                    $('#si_so_ly_thuyet').val('');
                    $('#si_so_lop').val('');
                    syncSiSoCapacityUi();
                    table.ajax.reload(null, false);
                    alert(res.message || 'Lưu thành công!');
                },
                error: function(xhr) {
                    var msg = xhr.responseJSON?.message || 'Có lỗi xảy ra.';
                    var errors = xhr.responseJSON?.errors || {};
                    if (Object.keys(errors).length) {
                        msg = Object.values(errors).flat().join('\n');
                    }
                    alert(msg);
                }
            });
        });

        // ===== Dời lịch =====
        function fmtDate(iso) {
            if (!iso) return '';
            var p = String(iso).split('-');
            if (p.length !== 3) return String(iso);
            return p[2] + '/' + p[1] + '/' + p[0];
        }
        function weekdayLabelFromThuVn(thu) {
            var m = {2:'Thứ 2',3:'Thứ 3',4:'Thứ 4',5:'Thứ 5',6:'Thứ 6',7:'Thứ 7',8:'Chủ nhật'};
            return m[thu] || ('Thứ ' + thu);
        }
        function thuVnFromIsoDate(iso) {
            if (!iso) return null;
            var d = new Date(iso + 'T00:00:00');
            var dow = d.getDay(); // 0..6 (Sun..Sat)
            return dow === 0 ? 8 : (dow + 1);
        }
        function minPeriodFromTiet(tiet) {
            if (!tiet) return null;
            var parts = String(tiet).split(',');
            for (var i=0;i<parts.length;i++){
                var n = parseInt(String(parts[i]).trim(), 10);
                if (!isNaN(n) && n >= 1 && n <= 16) return n;
            }
            return null;
        }
        function sessionKeyFromMinPeriod(p) {
            if (!p) return 'morning';
            if (p <= 6) return 'morning';
            if (p <= 12) return 'afternoon';
            return 'evening';
        }
        function dIndexFromThuVn(thu) {
            // 2..8 => 0..6 (Mon..Sun)
            if (thu === 8) return 6;
            return Math.max(0, Math.min(6, (thu - 2)));
        }
        function renderRescheduleGrid(sessions) {
            $('#rsGridTable .rs-cell').empty();
            (sessions || []).forEach(function(s){
                var p = minPeriodFromTiet(s.tiet);
                var bucket = sessionKeyFromMinPeriod(p);
                var d = dIndexFromThuVn(parseInt(s.thu, 10));
                var cell = $('#rsGridTable tbody tr[data-session="'+bucket+'"]').find('.rs-cell[data-d="'+d+'"]');
                var color = s.loai === 'ly_thuyet' ? '#3498db' : (s.loai === 'tam_ngung' ? '#e74c3c' : '#27ae60');
                var html = $('<div/>', {
                    class: 'rs-slot mb-2 px-3 py-3 rounded-2 text-white text-start shadow-sm',
                    css: { backgroundColor: color, minHeight: '4.75rem', cursor: 'pointer' },
                    'data-session-key': s.key,
                    'data-date': s.date || '',
                    'data-thu': s.thu,
                    'data-tiet': s.tiet,
                    'data-label': s.label,
                });
                html.append($('<div/>', { class: 'fw-semibold', text: s.label }));
                var meta = (s.room ? (s.room + ' · ') : '') + 'Tiết ' + (s.tiet || '') + (s.teacher ? (' · ' + s.teacher) : '');
                if (s.moved_from) {
                    meta += ' · dời từ ' + s.moved_from;
                }
                html.append($('<div/>', { class: 'opacity-90 mt-1', css: { fontSize: '0.875rem' }, text: meta }));
                cell.append(html);
            });
        }

        var rescheduleModal = new bootstrap.Modal(document.getElementById('rescheduleModal'));
        var currentSessions = [];
        var offeringStart = null;
        var offeringEnd = null;
        var rsCurrentDate = null; // ISO date inside the week being displayed

        function addDaysIso(iso, days) {
            var d = new Date(String(iso) + 'T00:00:00');
            d.setDate(d.getDate() + days);
            return d.toISOString().slice(0,10);
        }
        function startOfWeekIso(iso) {
            var d = new Date(String(iso) + 'T00:00:00'); // local-ish
            var dow = d.getDay(); // 0..6 (Sun..Sat)
            var diff = (dow === 0 ? -6 : (1 - dow)); // Monday start
            d.setDate(d.getDate() + diff);
            return d.toISOString().slice(0,10);
        }
        function renderWeekHeader(weekStartIso) {
            var ws = new Date(String(weekStartIso) + 'T00:00:00');
            var days = [];
            for (var i=0;i<7;i++){
                var d = new Date(ws);
                d.setDate(ws.getDate() + i);
                days.push(d.toISOString().slice(0,10));
            }
            $('#rsGridTable thead .rs-day-head').each(function(){
                var idx = parseInt($(this).data('d'), 10);
                var iso = days[idx];
                var label = idx === 6 ? 'CN' : ('Thứ ' + (idx + 2));
                $(this).html('<div class="fw-bold text-primary">'+label+'</div><div class="fw-bold text-primary">'+fmtDate(iso)+'</div>');
            });
            var we = days[6];
            $('#rsWeekLabel').text(fmtDate(days[0]) + ' → ' + fmtDate(we));
        }

        function loadRescheduleWeek(offeringId, dateIso) {
            var sessionsUrl = '{{ route("admin.subject-registrations.offering-sessions", ["id" => "__ID__"]) }}'
                .replace('__ID__', String(offeringId))
                + '?date=' + encodeURIComponent(dateIso);
            return $.get(sessionsUrl, function(resp){
                var offering = resp.offering || {};
                currentSessions = resp.sessions || [];
                $('#rsOfferingName').text(offering.ten_hoc_phan || '—');
                var meta = [offering.subject, offering.class, ('Học: ' + (offering.date_range || '—'))].filter(Boolean).join(' · ');
                $('#rsOfferingMeta').text(meta);
                offeringStart = offering.start_date || null;
                offeringEnd = offering.end_date || null;

                // date constraints for date_new
                var today = new Date();
                today.setDate(today.getDate() + 1);
                var minIso = today.toISOString().slice(0,10);
                if (offeringStart && offeringStart > minIso) minIso = offeringStart;
                $('#rsDateNew').attr('min', minIso);
                if (offeringEnd) $('#rsDateNew').attr('max', offeringEnd);
                $('#rsDateNewHint').text(offeringEnd ? ('Chỉ chọn từ ' + fmtDate(minIso) + ' đến ' + fmtDate(offeringEnd)) : ('Chỉ chọn từ ' + fmtDate(minIso)));

                var weekStartIso = resp.week_start || startOfWeekIso(dateIso);
                renderWeekHeader(weekStartIso);
                renderRescheduleGrid(currentSessions);
            });
        }

        $(document).on('click', '.reschedule-offering-btn', function() {
            var id = $(this).data('id');
            $('#rsOfferingId').val(id);
            $('#rsSessionKey').val('');
            $('#rsDateOld').val('');
            $('#rsSelectedLabel').text('—');
            $('#rsTiet').val('');
            $('#rsError').addClass('d-none').text('');
            $('#btnUnpauseSession').addClass('d-none');
            $('#btnSaveReschedule').prop('disabled', false);
            $('#btnForceReschedule').prop('disabled', false);
            $('#btnPauseSession').prop('disabled', false);

            rsCurrentDate = new Date().toISOString().slice(0,10);
            $('#rsDateNew').val('');
            loadRescheduleWeek(id, rsCurrentDate).done(function(){
                rescheduleModal.show();
            }).fail(function(){
                alert('Không tải được lịch học phần.');
            });
        });

        $('#rsPrevWeek').on('click', function () {
            var offeringId = $('#rsOfferingId').val();
            if (!offeringId) return;
            rsCurrentDate = addDaysIso(rsCurrentDate || new Date().toISOString().slice(0,10), -7);
            $('#rsSessionKey').val('');
            $('#rsDateOld').val('');
            $('#rsSelectedLabel').text('—');
            $('#rsError').addClass('d-none').text('');
            loadRescheduleWeek(offeringId, rsCurrentDate);
        });
        $('#rsNextWeek').on('click', function () {
            var offeringId = $('#rsOfferingId').val();
            if (!offeringId) return;
            rsCurrentDate = addDaysIso(rsCurrentDate || new Date().toISOString().slice(0,10), 7);
            $('#rsSessionKey').val('');
            $('#rsDateOld').val('');
            $('#rsSelectedLabel').text('—');
            $('#rsError').addClass('d-none').text('');
            loadRescheduleWeek(offeringId, rsCurrentDate);
        });

        $(document).on('click', '.rs-slot', function() {
            var key = String($(this).data('session-key') || '');
            $('.rs-slot').removeClass('border border-3 border-warning');
            $(this).addClass('border border-3 border-warning');
            var dateOld = $(this).data('date') || '';
            $('#rsDateOld').val(String(dateOld || ''));
            var thu = parseInt($(this).data('thu'), 10);
            var tiet = $(this).data('tiet');
            var label = $(this).data('label');
            $('#rsSessionKey').val(key);
            var thuLabel = weekdayLabelFromThuVn(thu);
            $('#rsSelectedLabel').text(label + (dateOld ? (' (' + thuLabel + ' · ' + fmtDate(dateOld) + ', tiết ' + tiet + ')') : (' (' + thuLabel + ', tiết ' + tiet + ')')));
            $('#rsTiet').val(String(tiet || ''));
            $('#rsError').addClass('d-none').text('');

            var isPause = key.startsWith('pause_');
            $('#btnUnpauseSession').toggleClass('d-none', !isPause);
            $('#btnSaveReschedule').prop('disabled', isPause);
            $('#btnForceReschedule').prop('disabled', isPause);
            $('#btnPauseSession').prop('disabled', isPause);
        });

        $(document).on('click', '.rs-quick', function() {
            var start = parseInt($(this).data('start'), 10);
            var arr = [start, start+1, start+2].filter(function(x){ return x <= 16; });
            $('#rsTiet').val(arr.join(','));
        });

        function doReschedule(force) {
            var offeringId = $('#rsOfferingId').val();
            var sessionKey = $('#rsSessionKey').val();
            var dateOld = $('#rsDateOld').val();
            var dateNew = $('#rsDateNew').val();
            var tiet = ($('#rsTiet').val() || '').trim();
            if (!sessionKey) {
                $('#rsError').removeClass('d-none').text('Bạn chưa chọn buổi cần dời.');
                return;
            }
            if (!dateOld) {
                $('#rsError').removeClass('d-none').text('Không xác định được ngày của buổi cũ.');
                return;
            }
            if (!dateNew) {
                $('#rsError').removeClass('d-none').text('Vui lòng chọn ngày dời (cố định).');
                return;
            }
            if (!tiet) {
                $('#rsError').removeClass('d-none').text('Vui lòng nhập tiết mới.');
                return;
            }
            $.ajax({
                url: '{{ route("admin.subject-registrations.reschedule-session", ["id" => "__ID__"]) }}'.replace('__ID__', String(offeringId)),
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    session_key: sessionKey,
                    date_old: dateOld,
                    date_new: dateNew,
                    tiet: tiet,
                    force: force ? 1 : 0
                },
                success: function(resp){
                    rescheduleModal.hide();
                    table.ajax.reload(null, false);
                },
                error: function(xhr){
                    var msg = 'Không dời được lịch.';
                    if (xhr && xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
                    $('#rsError').removeClass('d-none').text(msg);
                }
            });
        }

        $('#btnSaveReschedule').on('click', function() { doReschedule(false); });
        $('#btnForceReschedule').on('click', function() { doReschedule(true); });

        $('#btnPauseSession').on('click', function () {
            var offeringId = $('#rsOfferingId').val();
            var sessionKey = $('#rsSessionKey').val();
            if (!sessionKey) {
                $('#rsError').removeClass('d-none').text('Bạn chưa chọn buổi cần tạm ngưng.');
                return;
            }
            var dateOld = $('#rsDateOld').val();
            if (!dateOld) {
                $('#rsError').removeClass('d-none').text('Không xác định được ngày của buổi cần tạm ngưng.');
                return;
            }
            if (String(sessionKey).startsWith('pause_')) {
                $('#rsError').removeClass('d-none').text('Buổi này đã là tạm ngưng.');
                return;
            }
            if (!confirm('Tạm ngưng buổi này? Buổi học sẽ bị hủy và hiển thị màu đỏ.')) {
                return;
            }
            $.ajax({
                url: '{{ route("admin.subject-registrations.pause-session", ["id" => "__ID__"]) }}'.replace('__ID__', String(offeringId)),
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    session_key: sessionKey,
                    date_old: dateOld
                },
                success: function () {
                    rescheduleModal.hide();
                    table.ajax.reload(null, false);
                },
                error: function (xhr) {
                    var msg = 'Không tạm ngưng được.';
                    if (xhr && xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
                    $('#rsError').removeClass('d-none').text(msg);
                }
            });
        });

        $('#btnUnpauseSession').on('click', function () {
            var offeringId = $('#rsOfferingId').val();
            var key = $('#rsSessionKey').val();
            if (!key || !String(key).startsWith('pause_')) {
                $('#rsError').removeClass('d-none').text('Hãy chọn 1 buổi tạm ngưng (màu đỏ) để bỏ.');
                return;
            }
            if (!confirm('Bỏ tạm ngưng buổi này?')) {
                return;
            }
            $.ajax({
                url: '{{ route("admin.subject-registrations.unpause-session", ["id" => "__ID__"]) }}'.replace('__ID__', String(offeringId)),
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    pause_key: key
                },
                success: function () {
                    rescheduleModal.hide();
                    table.ajax.reload(null, false);
                },
                error: function (xhr) {
                    var msg = 'Không bỏ tạm ngưng được.';
                    if (xhr && xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
                    $('#rsError').removeClass('d-none').text(msg);
                }
            });
        });
    });
</script>
@endpush

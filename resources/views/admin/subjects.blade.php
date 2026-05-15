@extends('layouts.admin')

@section('title', 'Quản lý môn học')
@section('page-title', '')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-12">
            <div class="card card-block card-stretch">
                <div class="card-body p-0">
                    <div class="d-flex justify-content-between align-items-center p-3 flex-wrap gap-3 admin-page-header">
                        <h5 class="fw-bold mb-0">Quản lý môn học</h5>
                        <button type="button" class="btn btn-primary btn-create" data-bs-toggle="modal" data-bs-target="#createSubjectModal">
                            <i class="fas fa-plus"></i> Tạo môn học mới
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
            <div class="col-md-6 col-lg-4 col-xl-3">
                <div class="col-md-12">
                    <form id="quick-action-form" class="form-disabled d-flex gap-3 align-items-center">
                        @csrf
                        <select name="action_type" class="form-select" id="quick-action-type" style="width:150px">
                            <option value="">No Action</option>
                            <option value="delete">Xóa đã chọn</option>
                        </select>
                        <button id="quick-action-apply" class="btn btn-primary" disabled>Áp dụng</button>
                    </form>
                </div>
            </div>
            <div class="col-md-6 col-lg-4 col-xl-3">
                <div class="d-flex align-items-center gap-3 justify-content-end">
                    <div class="d-flex justify-content-end">
                        <div class="input-group input-group-search ms-2">
                            <span class="input-group-text" id="addon-wrapping"><i class="fas fa-search"></i></span>
                            <input type="text" class="form-control dt-search" placeholder="Search..." 
                                aria-label="Search" aria-describedby="addon-wrapping" aria-controls="subjectsTable">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="table-responsive mt-3 admin-table-wrap">
            <table id="subjectsTable" class="table table-striped border w-100 mb-0">
                <thead>
                    <tr>
                        <th width="50">
                            <input type="checkbox" id="select-all-table" class="form-check-input" onclick="selectAllTable(this)">
                        </th>
                        <th>Mã môn học</th>
                        <th>Tên môn học</th>
                        <th>Số tín chỉ</th>
                        <th>Số tiết lý thuyết</th>
                        <th>Số tiết thực hành</th>
                        <th>Nhóm tự chọn</th>
                        <th>Số TC bắt buộc của nhóm</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Create Subject -->
<div class="modal fade" id="createSubjectModal" tabindex="-1" aria-labelledby="createSubjectModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createSubjectModalLabel">Tạo môn học mới</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="createSubjectForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="create_ma_mon_hoc" class="form-label">Mã môn học <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="create_ma_mon_hoc" name="ma_mon_hoc" required>
                    </div>
                    <div class="mb-3">
                        <label for="create_ten_mon_hoc" class="form-label">Tên môn học <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="create_ten_mon_hoc" name="ten_mon_hoc" required>
                    </div>
                    <div class="mb-3">
                        <label for="create_so_tin_chi" class="form-label">Số tín chỉ <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="create_so_tin_chi" name="so_tin_chi" min="0" max="30" required>
                    </div>
                    <div class="mb-3">
                        <label for="create_so_tiet_ly_thuyet" class="form-label">Số tiết lý thuyết <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="create_so_tiet_ly_thuyet" name="so_tiet_ly_thuyet" min="0" max="500" value="0" required>
                    </div>
                    <div class="mb-3">
                        <label for="create_so_tiet_thuc_hanh" class="form-label">Số tiết thực hành <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="create_so_tiet_thuc_hanh" name="so_tiet_thuc_hanh" min="0" max="500" value="0" required>
                    </div>
                    <div class="mb-3">
                        <label for="create_nhom_thuc_hanh" class="form-label">Nhóm tự chọn</label>
                        <input type="number" class="form-control" id="create_nhom_thuc_hanh" name="nhom_thuc_hanh" min="0" max="100" placeholder="Để trống nếu không dùng nhóm">
                        <div class="form-text">Chỉ các môn có cùng số nhóm (&gt;0) mới gộp tính một lần TC khi là học phần tự chọn trong chương trình khung.</div>
                    </div>
                    <div class="mb-3">
                        <label for="create_so_tc_bat_buoc_cua_nhom" class="form-label">Số TC bắt buộc của nhóm</label>
                        <input type="number" class="form-control" id="create_so_tc_bat_buoc_cua_nhom" name="so_tc_bat_buoc_cua_nhom" min="0" max="100" placeholder="Để trống nếu không áp dụng">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    <button type="submit" class="btn btn-primary">Tạo mới</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Edit Subject -->
<div class="modal fade" id="editSubjectModal" tabindex="-1" aria-labelledby="editSubjectModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editSubjectModalLabel">Sửa thông tin môn học</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editSubjectForm">
                <div class="modal-body">
                    <input type="hidden" id="edit_subject_id" name="id">
                    <div class="mb-3">
                        <label for="edit_ma_mon_hoc" class="form-label">Mã môn học <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="edit_ma_mon_hoc" name="ma_mon_hoc" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_ten_mon_hoc" class="form-label">Tên môn học <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="edit_ten_mon_hoc" name="ten_mon_hoc" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_so_tin_chi" class="form-label">Số tín chỉ <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="edit_so_tin_chi" name="so_tin_chi" min="0" max="30" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_so_tiet_ly_thuyet" class="form-label">Số tiết lý thuyết <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="edit_so_tiet_ly_thuyet" name="so_tiet_ly_thuyet" min="0" max="500" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_so_tiet_thuc_hanh" class="form-label">Số tiết thực hành <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="edit_so_tiet_thuc_hanh" name="so_tiet_thuc_hanh" min="0" max="500" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_nhom_thuc_hanh" class="form-label">Nhóm tự chọn</label>
                        <input type="number" class="form-control" id="edit_nhom_thuc_hanh" name="nhom_thuc_hanh" min="0" max="100" placeholder="Để trống nếu không dùng nhóm">
                        <div class="form-text">Chỉ các môn có cùng số nhóm (&gt;0) mới gộp tính một lần TC khi là học phần tự chọn trong chương trình khung.</div>
                    </div>
                    <div class="mb-3">
                        <label for="edit_so_tc_bat_buoc_cua_nhom" class="form-label">Số TC bắt buộc của nhóm</label>
                        <input type="number" class="form-control" id="edit_so_tc_bat_buoc_cua_nhom" name="so_tc_bat_buoc_cua_nhom" min="0" max="100" placeholder="Để trống nếu không áp dụng">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    <button type="submit" class="btn btn-primary">Lưu thay đổi</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Delete Confirmation -->
<div class="modal fade" id="deleteSubjectModal" tabindex="-1" aria-labelledby="deleteSubjectModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteSubjectModalLabel">Xác nhận xóa</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Bạn có chắc chắn muốn xóa môn học này không?</p>
                <p class="text-danger"><strong>Hành động này không thể hoàn tác!</strong></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteSubjectBtn">Xóa</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        var table = $('#subjectsTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route("admin.subjects.data") }}',
                type: 'GET'
            },
            columns: [
                {
                    data: 'check',
                    name: 'check',
                    orderable: false,
                    searchable: false,
                    width: '50px'
                },
                {
                    data: 'ma_mon_hoc',
                    name: 'ma_mon_hoc'
                },
                {
                    data: 'ten_mon_hoc',
                    name: 'ten_mon_hoc'
                },
                {
                    data: 'so_tin_chi',
                    name: 'so_tin_chi'
                },
                {
                    data: 'so_tiet_ly_thuyet',
                    name: 'so_tiet_ly_thuyet'
                },
                {
                    data: 'so_tiet_thuc_hanh',
                    name: 'so_tiet_thuc_hanh'
                },
                {
                    data: 'nhom_thuc_hanh',
                    name: 'nhom_thuc_hanh'
                },
                {
                    data: 'so_tc_bat_buoc_cua_nhom',
                    name: 'so_tc_bat_buoc_cua_nhom'
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
            order: [[1, 'asc']],
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
                emptyTable: "Không có dữ liệu",
                zeroRecords: "Không tìm thấy kết quả"
            },
            dom: AdminDT.dom
        });
        
        $('.dt-search').on('input', function() {
            table.search(this.value).draw();
        });

        // Reset create form when modal is closed
        $('#createSubjectModal').on('hidden.bs.modal', function() {
            $('#createSubjectForm')[0].reset();
        });

        // Create form submit
        $('#createSubjectForm').on('submit', function(e) {
            e.preventDefault();
            var formData = $(this).serialize();

            $.ajax({
                url: '{{ route("admin.subjects.store") }}',
                type: 'POST',
                data: formData,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    var createModal = bootstrap.Modal.getInstance(document.getElementById('createSubjectModal'));
                    createModal.hide();
                    table.ajax.reload();
                    alert('Tạo môn học mới thành công!');
                },
                error: function(xhr) {
                    var errors = xhr.responseJSON?.errors || {};
                    var errorMsg = '';
                    
                    if (Object.keys(errors).length === 0) {
                        errorMsg = xhr.responseJSON?.message || 'Có lỗi xảy ra khi tạo môn học mới!';
                    } else {
                        errorMsg = 'Vui lòng kiểm tra lại thông tin:\n\n';
                        for (var field in errors) {
                            var fieldName = field === 'ma_mon_hoc' ? 'Mã môn học' : 
                                          field === 'ten_mon_hoc' ? 'Tên môn học' :
                                          field === 'so_tin_chi' ? 'Số tín chỉ' :
                                          field === 'so_tiet_ly_thuyet' ? 'Số tiết lý thuyết' :
                                          field === 'so_tiet_thuc_hanh' ? 'Số tiết thực hành' :
                                          field === 'nhom_thuc_hanh' ? 'Nhóm tự chọn' :
                                          field === 'so_tc_bat_buoc_cua_nhom' ? 'Số TC bắt buộc của nhóm' : field;
                            errorMsg += '• ' + fieldName + ': ' + errors[field][0] + '\n';
                        }
                    }
                    alert(errorMsg);
                }
            });
        });

        // Edit button click
        $(document).on('click', '.edit-btn', function() {
            var subjectId = $(this).data('id');
            
            $.ajax({
                url: '{{ url("admin/subjects") }}/' + subjectId,
                type: 'GET',
                success: function(response) {
                    $('#edit_subject_id').val(response.id);
                    $('#edit_ma_mon_hoc').val(response.ma_mon_hoc);
                    $('#edit_ten_mon_hoc').val(response.ten_mon_hoc);
                    $('#edit_so_tin_chi').val(response.so_tin_chi ?? 0);
                    $('#edit_so_tiet_ly_thuyet').val(response.so_tiet_ly_thuyet ?? 0);
                    $('#edit_so_tiet_thuc_hanh').val(response.so_tiet_thuc_hanh ?? 0);
                    $('#edit_nhom_thuc_hanh').val((response.nhom_thuc_hanh ?? 0) > 0 ? response.nhom_thuc_hanh : '');
                    $('#edit_so_tc_bat_buoc_cua_nhom').val((response.so_tc_bat_buoc_cua_nhom ?? 0) > 0 ? response.so_tc_bat_buoc_cua_nhom : '');
                    
                    var editModal = new bootstrap.Modal(document.getElementById('editSubjectModal'));
                    editModal.show();
                },
                error: function() {
                    alert('Không thể tải thông tin môn học!');
                }
            });
        });

        // Update form submit
        $('#editSubjectForm').on('submit', function(e) {
            e.preventDefault();
            var subjectId = $('#edit_subject_id').val();
            var formData = $(this).serialize();

            $.ajax({
                url: '{{ url("admin/subjects") }}/' + subjectId,
                type: 'PUT',
                data: formData,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    var editModal = bootstrap.Modal.getInstance(document.getElementById('editSubjectModal'));
                    editModal.hide();
                    table.ajax.reload();
                    alert('Cập nhật thành công!');
                },
                error: function(xhr) {
                    var errors = xhr.responseJSON?.errors || {};
                    var errorMsg = 'Có lỗi xảy ra:\n';
                    for (var field in errors) {
                        errorMsg += errors[field][0] + '\n';
                    }
                    alert(errorMsg);
                }
            });
        });

        // Delete button click
        var deleteSubjectId = null;
        $(document).on('click', '.delete-btn', function() {
            deleteSubjectId = $(this).data('id');
            var deleteModal = new bootstrap.Modal(document.getElementById('deleteSubjectModal'));
            deleteModal.show();
        });

        // Confirm delete
        $('#confirmDeleteSubjectBtn').on('click', function() {
            if (deleteSubjectId) {
                $.ajax({
                    url: '{{ url("admin/subjects") }}/' + deleteSubjectId,
                    type: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        var deleteModal = bootstrap.Modal.getInstance(document.getElementById('deleteSubjectModal'));
                        deleteModal.hide();
                        table.ajax.reload();
                        alert('Xóa thành công!');
                        deleteSubjectId = null;
                    },
                    error: function() {
                        alert('Không thể xóa môn học!');
                    }
                });
            }
        });
    });
</script>
@endpush

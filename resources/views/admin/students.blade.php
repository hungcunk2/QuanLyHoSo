@extends('layouts.admin')

@section('title', 'Quản lý học sinh')
@section('page-title', '')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-12">
            <div class="card card-block card-stretch">
                <div class="card-body p-0">
                    <div class="d-flex justify-content-between align-items-center p-3 flex-wrap gap-3">
                        <h5 class="fw-bold">Quản lý học sinh</h5>
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createStudentModal">
                            <i class="fas fa-plus"></i> Tạo học sinh mới
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="row justify-content-between gy-3">
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
                                aria-label="Search" aria-describedby="addon-wrapping" aria-controls="studentsTable">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="table-responsive mt-3">
            <table id="studentsTable" class="table table-striped border">
                <thead>
                    <tr>
                        <th width="50">
                            <input type="checkbox" id="select-all-table" class="form-check-input" onclick="selectAllTable(this)">
                        </th>
                        <th>Mã học sinh</th>
                        <th>Họ và tên</th>
                        <th>Email</th>
                        <th>Lớp</th>
                        <th>Số điện thoại</th>
                        <th>Ngày sinh</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Create Student -->
<div class="modal fade" id="createStudentModal" tabindex="-1" aria-labelledby="createStudentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createStudentModalLabel">Tạo học sinh mới</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="createStudentForm">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="create_mssv" class="form-label">Mã số học sinh <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="create_mssv" name="mssv" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="create_email" class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" id="create_email" name="email" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="create_ho_ten" class="form-label">Họ và tên <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="create_ho_ten" name="ho_ten" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="create_lop" class="form-label">Lớp <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="create_lop" name="lop" required>
                        </div>
                    </div>
                    <div class="alert alert-info">
                        <small><i class="fas fa-info-circle"></i> Các thông tin khác (số điện thoại, ngày sinh, địa chỉ, thông tin phụ huynh...) học sinh có thể tự cập nhật sau.</small>
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

<!-- Modal Edit Student -->
<div class="modal fade" id="editStudentModal" tabindex="-1" aria-labelledby="editStudentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editStudentModalLabel">Sửa thông tin học sinh</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editStudentForm">
                <div class="modal-body">
                    <input type="hidden" id="edit_student_id" name="id">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="edit_mssv" class="form-label">Mã số học sinh <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="edit_mssv" name="mssv" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="edit_ho_ten" class="form-label">Họ và tên <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="edit_ho_ten" name="ho_ten" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="edit_lop" class="form-label">Lớp</label>
                            <input type="text" class="form-control" id="edit_lop" name="lop">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="edit_so_dien_thoai" class="form-label">Số điện thoại</label>
                            <input type="text" class="form-control" id="edit_so_dien_thoai" name="so_dien_thoai">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="edit_ngay_sinh" class="form-label">Ngày sinh</label>
                            <input type="date" class="form-control" id="edit_ngay_sinh" name="ngay_sinh">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="edit_email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="edit_email" name="email">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="edit_dia_chi" class="form-label">Địa chỉ</label>
                        <textarea class="form-control" id="edit_dia_chi" name="dia_chi" rows="2"></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="edit_ho_ten_cha" class="form-label">Họ tên cha</label>
                            <input type="text" class="form-control" id="edit_ho_ten_cha" name="ho_ten_cha">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="edit_sdt_cha" class="form-label">SĐT cha</label>
                            <input type="text" class="form-control" id="edit_sdt_cha" name="sdt_cha">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="edit_ho_ten_me" class="form-label">Họ tên mẹ</label>
                            <input type="text" class="form-control" id="edit_ho_ten_me" name="ho_ten_me">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="edit_sdt_me" class="form-label">SĐT mẹ</label>
                            <input type="text" class="form-control" id="edit_sdt_me" name="sdt_me">
                        </div>
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
<div class="modal fade" id="deleteStudentModal" tabindex="-1" aria-labelledby="deleteStudentModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteStudentModalLabel">Xác nhận xóa</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Bạn có chắc chắn muốn xóa học sinh này không?</p>
                <p class="text-danger"><strong>Hành động này không thể hoàn tác!</strong></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Xóa</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        var table = $('#studentsTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route("admin.students.data") }}',
                type: 'GET',
                data: function(d) {
                    d.search = $('.dt-search').val();
                }
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
                    data: 'mssv',
                    name: 'mssv'
                },
                {
                    data: 'ho_ten',
                    name: 'ho_ten'
                },
                {
                    data: 'email',
                    name: 'email'
                },
                {
                    data: 'lop',
                    name: 'lop'
                },
                {
                    data: 'so_dien_thoai',
                    name: 'so_dien_thoai'
                },
                {
                    data: 'ngay_sinh',
                    name: 'ngay_sinh'
                },
                {
                    data: 'action',
                    name: 'action',
                    orderable: false,
                    searchable: false
                }
            ],
            order: [[6, 'desc']],
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
            dom: '<"row align-items-center"><"table-responsive my-3 mt-3 mb-2 pb-1" rt><"row align-items-center data_table_widgets" <"col-md-6" <"d-flex align-items-center flex-wrap gap-3" l i>><"col-md-6" p>><"clear">'
        });
        
        $('.dt-search').on('keyup', function() {
            table.search(this.value).draw();
        });

        // Reset create form when modal is closed
        $('#createStudentModal').on('hidden.bs.modal', function() {
            $('#createStudentForm')[0].reset();
        });

        // Create form submit
        $('#createStudentForm').on('submit', function(e) {
            e.preventDefault();
            var formData = $(this).serialize();

            $.ajax({
                url: '{{ route("admin.students.store") }}',
                type: 'POST',
                data: formData,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    var createModal = bootstrap.Modal.getInstance(document.getElementById('createStudentModal'));
                    createModal.hide();
                    table.ajax.reload();
                    alert('Tạo học sinh mới thành công!');
                },
                error: function(xhr) {
                    var errors = xhr.responseJSON?.errors || {};
                    var errorMsg = '';
                    
                    if (Object.keys(errors).length === 0) {
                        errorMsg = xhr.responseJSON?.message || 'Có lỗi xảy ra khi tạo học sinh mới!';
                    } else {
                        errorMsg = 'Vui lòng kiểm tra lại thông tin:\n\n';
                        for (var field in errors) {
                            var fieldName = field === 'mssv' ? 'Mã số học sinh' : 
                                          field === 'email' ? 'Email' : 
                                          field === 'ho_ten' ? 'Họ và tên' : 
                                          field === 'lop' ? 'Lớp' : field;
                            errorMsg += '• ' + fieldName + ': ' + errors[field][0] + '\n';
                        }
                    }
                    alert(errorMsg);
                }
            });
        });

        // Edit button click
        $(document).on('click', '.edit-btn', function() {
            var studentId = $(this).data('id');
            
            $.ajax({
                url: '{{ url("admin/students") }}/' + studentId,
                type: 'GET',
                success: function(response) {
                    $('#edit_student_id').val(response.id);
                    $('#edit_mssv').val(response.mssv);
                    $('#edit_ho_ten').val(response.ho_ten);
                    $('#edit_lop').val(response.lop);
                    $('#edit_so_dien_thoai').val(response.so_dien_thoai);
                    $('#edit_ngay_sinh').val(response.ngay_sinh ? response.ngay_sinh.split('T')[0] : '');
                    $('#edit_email').val(response.email);
                    $('#edit_dia_chi').val(response.dia_chi);
                    $('#edit_ho_ten_cha').val(response.ho_ten_cha);
                    $('#edit_sdt_cha').val(response.sdt_cha);
                    $('#edit_ho_ten_me').val(response.ho_ten_me);
                    $('#edit_sdt_me').val(response.sdt_me);
                    
                    var editModal = new bootstrap.Modal(document.getElementById('editStudentModal'));
                    editModal.show();
                },
                error: function() {
                    alert('Không thể tải thông tin học sinh!');
                }
            });
        });

        // Update form submit
        $('#editStudentForm').on('submit', function(e) {
            e.preventDefault();
            var studentId = $('#edit_student_id').val();
            var formData = $(this).serialize();

            $.ajax({
                url: '{{ url("admin/students") }}/' + studentId,
                type: 'PUT',
                data: formData,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    var editModal = bootstrap.Modal.getInstance(document.getElementById('editStudentModal'));
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

        // Send email button click
        $(document).on('click', '.send-email-btn', function() {
            var studentId = $(this).data('id');
            var studentEmail = $(this).data('email');
            
            if (!studentEmail) {
                alert('Học sinh này chưa có email!');
                return;
            }
            
            if (!confirm('Bạn có chắc muốn gửi email chào mừng đến ' + studentEmail + '?')) {
                return;
            }
            
            var btn = $(this);
            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');
            
            $.ajax({
                url: '{{ url("admin/students") }}/' + studentId + '/send-email',
                type: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    btn.prop('disabled', false).html('<i class="fas fa-envelope"></i>');
                    alert(response.message || 'Email đã được gửi thành công!');
                },
                error: function(xhr) {
                    btn.prop('disabled', false).html('<i class="fas fa-envelope"></i>');
                    var errorMsg = xhr.responseJSON?.message || 'Không thể gửi email!';
                    alert(errorMsg);
                }
            });
        });

        // Delete button click
        var deleteStudentId = null;
        $(document).on('click', '.delete-btn', function() {
            deleteStudentId = $(this).data('id');
            var deleteModal = new bootstrap.Modal(document.getElementById('deleteStudentModal'));
            deleteModal.show();
        });

        // Confirm delete
        $('#confirmDeleteBtn').on('click', function() {
            if (deleteStudentId) {
                $.ajax({
                    url: '{{ url("admin/students") }}/' + deleteStudentId,
                    type: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        var deleteModal = bootstrap.Modal.getInstance(document.getElementById('deleteStudentModal'));
                        deleteModal.hide();
                        table.ajax.reload();
                        alert('Xóa thành công!');
                        deleteStudentId = null;
                    },
                    error: function() {
                        alert('Không thể xóa học sinh!');
                    }
                });
            }
        });
    });
</script>
@endpush

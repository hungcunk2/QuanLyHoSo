@extends('layouts.admin')

@section('title', 'Quản lý giáo viên')
@section('page-title', '')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-12">
            <div class="card card-block card-stretch">
                <div class="card-body p-0">
                    <div class="d-flex justify-content-between align-items-center p-3 flex-wrap gap-3 admin-page-header">
                        <h5 class="fw-bold mb-0">Quản lý giáo viên</h5>
                        <button type="button" class="btn btn-primary btn-create" data-bs-toggle="modal" data-bs-target="#createTeacherModal">
                            <i class="fas fa-plus"></i> Tạo giáo viên mới
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="admin-toolbar admin-toolbar--one-row">
            <form id="quick-action-form" class="form-disabled quick-action-form admin-toolbar__cell admin-toolbar__cell--action">
                @csrf
                <label for="quick-action-type" class="form-label small mb-1 text-muted">Thao tác</label>
                <div class="admin-toolbar__action-controls">
                    <select name="action_type" class="form-select form-select-sm" id="quick-action-type">
                        <option value="">No Action</option>
                        <option value="delete">Xóa đã chọn</option>
                    </select>
                    <button type="button" id="quick-action-apply" class="btn btn-primary btn-sm" disabled>Áp dụng</button>
                </div>
            </form>
            <div class="admin-toolbar__cell admin-toolbar__cell--search">
                <label for="filter-ho-ten" class="form-label small mb-1 text-muted">Tìm theo tên</label>
                <div class="input-group input-group-sm">
                    <span class="input-group-text"><i class="fas fa-user"></i></span>
                    <input type="text" class="form-control" id="filter-ho-ten" placeholder="Nhập họ và tên..."
                        aria-controls="teachersTable" autocomplete="off">
                </div>
            </div>
            <div class="admin-toolbar__cell admin-toolbar__cell--select">
                <label for="filter-chuyen-mon" class="form-label small mb-1 text-muted">Chuyên môn</label>
                <select class="form-select form-select-sm" id="filter-chuyen-mon" aria-controls="teachersTable">
                    <option value="">Tất cả chuyên môn</option>
                    @foreach (\App\Models\Teacher::chuyenMonOptions() as $chuyenMon)
                        <option value="{{ $chuyenMon }}">{{ $chuyenMon }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="table-responsive mt-3 admin-table-wrap">
            <table id="teachersTable" class="table table-striped border mb-0 admin-dt-table">
                <colgroup>
                    <col style="width: 3%">
                    <col style="width: 9%">
                    <col style="width: 16%">
                    <col style="width: 14%">
                    <col style="width: 22%">
                    <col style="width: 12%">
                    <col style="width: 10%">
                    <col style="width: 14%">
                </colgroup>
                <thead>
                    <tr>
                        <th width="50">
                            <input type="checkbox" id="select-all-table" class="form-check-input" onclick="selectAllTable(this)">
                        </th>
                        <th>Mã giáo viên</th>
                        <th>Họ và tên</th>
                        <th>Chuyên môn</th>
                        <th>Email</th>
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

<!-- Modal Create Teacher -->
<div class="modal fade" id="createTeacherModal" tabindex="-1" aria-labelledby="createTeacherModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createTeacherModalLabel">Tạo giáo viên mới</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="createTeacherForm">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="create_msgv" class="form-label">Mã số giáo viên</label>
                            <input type="text" class="form-control" id="create_msgv" name="msgv">
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
                            <label for="create_chuyen_mon" class="form-label">Chuyên môn <span class="text-danger">*</span></label>
                            <select class="form-select" id="create_chuyen_mon" name="chuyen_mon" required>
                                <option value="" selected disabled>Chọn chuyên môn</option>
                                @foreach (\App\Models\Teacher::chuyenMonOptions() as $chuyenMon)
                                    <option value="{{ $chuyenMon }}">{{ $chuyenMon }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="alert alert-info">
                        <small><i class="fas fa-info-circle"></i> Các thông tin khác (số điện thoại, ngày sinh, địa chỉ...) giáo viên có thể tự cập nhật sau.</small>
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

<!-- Modal Edit Teacher -->
<div class="modal fade" id="editTeacherModal" tabindex="-1" aria-labelledby="editTeacherModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editTeacherModalLabel">Sửa thông tin giáo viên</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editTeacherForm">
                <div class="modal-body">
                    <input type="hidden" id="edit_teacher_id" name="id">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="edit_msgv" class="form-label">Mã số giáo viên <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="edit_msgv" name="msgv" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="edit_ho_ten" class="form-label">Họ và tên <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="edit_ho_ten" name="ho_ten" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="edit_chuyen_mon" class="form-label">Chuyên môn</label>
                            <select class="form-select" id="edit_chuyen_mon" name="chuyen_mon">
                                <option value="">Chọn chuyên môn</option>
                                @foreach (\App\Models\Teacher::chuyenMonOptions() as $chuyenMon)
                                    <option value="{{ $chuyenMon }}">{{ $chuyenMon }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="edit_sdt" class="form-label">Số điện thoại</label>
                            <input type="text" class="form-control" id="edit_sdt" name="sdt">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="edit_email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="edit_email" name="email">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="edit_ngay_sinh" class="form-label">Ngày sinh</label>
                            <input type="date" class="form-control" id="edit_ngay_sinh" name="ngay_sinh">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="edit_dia_chi" class="form-label">Địa chỉ</label>
                        <textarea class="form-control" id="edit_dia_chi" name="dia_chi" rows="2"></textarea>
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
<div class="modal fade" id="deleteTeacherModal" tabindex="-1" aria-labelledby="deleteTeacherModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteTeacherModalLabel">Xác nhận xóa</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Bạn có chắc chắn muốn xóa giáo viên này không?</p>
                <p class="text-danger"><strong>Hành động này không thể hoàn tác!</strong></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteTeacherBtn">Xóa</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Reset Password -->
<div class="modal fade" id="resetPasswordModal" tabindex="-1" aria-labelledby="resetPasswordModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="resetPasswordModalLabel">Đổi mật khẩu giáo viên</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="resetPasswordForm">
                <div class="modal-body">
                    <input type="hidden" id="reset_teacher_id" name="id">
                    <div class="mb-2">
                        <div><strong>Mã GV:</strong> <span id="reset_teacher_msgv"></span></div>
                        <div><strong>Email:</strong> <span id="reset_teacher_email"></span></div>
                    </div>
                    <div class="alert alert-warning mb-3">
                        <small>
                            Hệ thống sẽ đổi mật khẩu và gửi email thông báo mật khẩu mới cho giáo viên.
                        </small>
                    </div>
                    <div class="mb-3">
                        <label for="reset_password" class="form-label">Mật khẩu mới <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="reset_password" name="password" required minlength="6" autocomplete="new-password">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-warning" id="resetPasswordSubmitBtn">Đổi mật khẩu</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const CHUYEN_MON_OPTIONS = @json(\App\Models\Teacher::chuyenMonOptions());

    function selectAllTable(checkbox) {
        const isChecked = checkbox.checked;
        // Only select checkboxes on current page (DataTables redraws)
        $('#teachersTable').find('.row-checkbox').prop('checked', isChecked);
        updateQuickAction();
    }

    function updateQuickAction() {
        const checkedCount = $('#teachersTable').find('.row-checkbox:checked').length;
        if (checkedCount > 0) {
            $('#quick-action-type').prop('disabled', false);
            $('#quick-action-apply').prop('disabled', $('#quick-action-type').val() === '');
        } else {
            $('#quick-action-type').prop('disabled', true).val('');
            $('#quick-action-apply').prop('disabled', true);
            $('#select-all-table').prop('checked', false);
        }
    }

    $(document).ready(function() {
        var table = $('#teachersTable').DataTable({
            processing: true,
            serverSide: true,
            searching: false,
            ajax: {
                url: '{{ route("admin.teachers.data") }}',
                type: 'GET',
                data: function(d) {
                    d.filter_ho_ten = $('#filter-ho-ten').val();
                    d.filter_chuyen_mon = $('#filter-chuyen-mon').val();
                }
            },
            columns: [
                { data: 'check', name: 'check', orderable: false, searchable: false, width: '48px' },
                { data: 'msgv', name: 'msgv', width: '90px' },
                { data: 'ho_ten', name: 'ho_ten', width: '16%' },
                { data: 'chuyen_mon', name: 'chuyen_mon', width: '14%' },
                { data: 'email', name: 'email', width: '22%' },
                { data: 'sdt', name: 'sdt', width: '12%' },
                { data: 'ngay_sinh', name: 'ngay_sinh', width: '10%' },
                { data: 'action', name: 'action', orderable: false, searchable: false, width: '120px' }
            ],
            responsive: false,
            scrollX: false,
            autoWidth: false,
            order: [],
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

        table.on('draw.dt', function () {
            table.columns.adjust();
        });
        
        var filterHoTenTimer = null;
        $('#filter-ho-ten').on('keyup input', function() {
            clearTimeout(filterHoTenTimer);
            filterHoTenTimer = setTimeout(function() {
                table.draw();
            }, 300);
        });
        $('#filter-chuyen-mon').on('change', function() {
            table.draw();
        });

        // Reset select-all on redraw
        table.on('draw', function() {
            $('#select-all-table').prop('checked', false);
            updateQuickAction();
        });

        // Row checkbox change
        $(document).on('change', '#teachersTable .row-checkbox', function() {
            const total = $('#teachersTable').find('.row-checkbox').length;
            const checked = $('#teachersTable').find('.row-checkbox:checked').length;
            $('#select-all-table').prop('checked', total > 0 && total === checked);
            updateQuickAction();
        });

        // Quick action type change
        $('#quick-action-type').on('change', function() {
            updateQuickAction();
        });

        // Apply quick action (bulk delete)
        $('#quick-action-form').on('submit', function(e) {
            e.preventDefault();
        });
        $('#quick-action-apply').on('click', function(e) {
            e.preventDefault();
            const action = $('#quick-action-type').val();
            const ids = $('#teachersTable').find('.row-checkbox:checked').map(function() { return $(this).val(); }).get();
            if (!action || ids.length === 0) return;
            if (action === 'delete') {
                if (!confirm('Bạn có chắc muốn xóa ' + ids.length + ' giáo viên đã chọn?')) return;
                $.ajax({
                    url: '{{ route("admin.teachers.bulk-delete") }}',
                    type: 'POST',
                    data: { selected_ids: ids },
                    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                    success: function(res) {
                        table.ajax.reload();
                        $('#quick-action-type').val('');
                        updateQuickAction();
                        alert(res.message || 'Đã xóa thành công!');
                    },
                    error: function(xhr) {
                        const msg = xhr.responseJSON?.message || 'Không thể xóa hàng loạt!';
                        alert(msg);
                    }
                });
            }
        });

        // Reset create form when modal is closed
        $('#createTeacherModal').on('hidden.bs.modal', function() {
            $('#createTeacherForm')[0].reset();
        });

        // Prefill next teacher code when modal opens
        $('#createTeacherModal').on('shown.bs.modal', function() {
            const $msgv = $('#create_msgv');
            if ($msgv.val()) return;
            $.ajax({
                url: '{{ route("admin.teachers.next-msgv") }}',
                type: 'GET',
                success: function(res) {
                    if (res?.next_msgv) $msgv.val(res.next_msgv);
                }
            });
        });

        // Create form submit
        $('#createTeacherForm').on('submit', function(e) {
            e.preventDefault();
            var formData = $(this).serialize();

            $.ajax({
                url: '{{ route("admin.teachers.store") }}',
                type: 'POST',
                data: formData,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    var createModal = bootstrap.Modal.getInstance(document.getElementById('createTeacherModal'));
                    createModal.hide();
                    table.ajax.reload();
                    alert('Tạo giáo viên mới thành công!');
                },
                error: function(xhr) {
                    var errors = xhr.responseJSON?.errors || {};
                    var errorMsg = '';
                    
                    if (Object.keys(errors).length === 0) {
                        errorMsg = xhr.responseJSON?.message || 'Có lỗi xảy ra khi tạo giáo viên mới!';
                    } else {
                        errorMsg = 'Vui lòng kiểm tra lại thông tin:\n\n';
                        for (var field in errors) {
                            var fieldName = field === 'msgv' ? 'Mã số giáo viên' : 
                                          field === 'email' ? 'Email' : 
                                          field === 'ho_ten' ? 'Họ và tên' : 
                                          field === 'chuyen_mon' ? 'Chuyên môn' : field;
                            errorMsg += '• ' + fieldName + ': ' + errors[field][0] + '\n';
                        }
                    }
                    alert(errorMsg);
                }
            });
        });

        // Reset password button click
        var resetTeacherId = null;
        $(document).on('click', '.reset-password-btn', function() {
            resetTeacherId = $(this).data('id');
            const email = $(this).data('email') || '';
            const msgv = $(this).data('msgv') || '';

            $('#reset_teacher_id').val(resetTeacherId);
            $('#reset_teacher_email').text(email || '(chưa có email)');
            $('#reset_teacher_msgv').text(msgv);
            $('#reset_password').val('');

            var modal = new bootstrap.Modal(document.getElementById('resetPasswordModal'));
            modal.show();
        });

        // Reset password form submit
        $('#resetPasswordForm').on('submit', function(e) {
            e.preventDefault();
            const teacherId = $('#reset_teacher_id').val();
            const password = $('#reset_password').val();
            if (!teacherId) return;

            const btn = $('#resetPasswordSubmitBtn');
            btn.prop('disabled', true).text('Đang đổi...');

            $.ajax({
                url: '{{ url("admin/teachers") }}/' + teacherId + '/reset-password',
                type: 'POST',
                data: { password },
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                success: function(res) {
                    btn.prop('disabled', false).text('Đổi mật khẩu');
                    var modalInstance = bootstrap.Modal.getInstance(document.getElementById('resetPasswordModal'));
                    modalInstance.hide();
                    alert(res.message || 'Đổi mật khẩu thành công!');
                },
                error: function(xhr) {
                    btn.prop('disabled', false).text('Đổi mật khẩu');
                    const msg =
                        xhr.responseJSON?.errors?.password?.[0] ||
                        xhr.responseJSON?.message ||
                        'Không thể đổi mật khẩu!';
                    alert(msg);
                }
            });
        });

        // Edit button click
        $(document).on('click', '.edit-btn', function() {
            var teacherId = $(this).data('id');
            
            $.ajax({
                url: '{{ url("admin/teachers") }}/' + teacherId,
                type: 'GET',
                success: function(response) {
                    $('#edit_teacher_id').val(response.id);
                    $('#edit_msgv').val(response.msgv);
                    $('#edit_ho_ten').val(response.ho_ten);
                    var cm = response.chuyen_mon || '';
                    var $editCm = $('#edit_chuyen_mon');
                    $editCm.find('option.chuyen-mon-legacy').remove();
                    if (cm && CHUYEN_MON_OPTIONS.indexOf(cm) === -1) {
                        $editCm.append($('<option>', { 'class': 'chuyen-mon-legacy', value: cm, text: cm }));
                    }
                    $editCm.val(cm);
                    $('#edit_sdt').val(response.sdt);
                    $('#edit_email').val(response.email);
                    $('#edit_ngay_sinh').val(response.ngay_sinh ? response.ngay_sinh.split('T')[0] : '');
                    $('#edit_dia_chi').val(response.dia_chi);
                    
                    var editModal = new bootstrap.Modal(document.getElementById('editTeacherModal'));
                    editModal.show();
                },
                error: function() {
                    alert('Không thể tải thông tin giáo viên!');
                }
            });
        });

        // Update form submit
        $('#editTeacherForm').on('submit', function(e) {
            e.preventDefault();
            var teacherId = $('#edit_teacher_id').val();
            var formData = $(this).serialize();

            $.ajax({
                url: '{{ url("admin/teachers") }}/' + teacherId,
                type: 'PUT',
                data: formData,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    var editModal = bootstrap.Modal.getInstance(document.getElementById('editTeacherModal'));
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
        var deleteTeacherId = null;
        $(document).on('click', '.delete-btn', function() {
            deleteTeacherId = $(this).data('id');
            var deleteModal = new bootstrap.Modal(document.getElementById('deleteTeacherModal'));
            deleteModal.show();
        });

        // Confirm delete
        $('#confirmDeleteTeacherBtn').on('click', function() {
            if (deleteTeacherId) {
                $.ajax({
                    url: '{{ url("admin/teachers") }}/' + deleteTeacherId,
                    type: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        var deleteModal = bootstrap.Modal.getInstance(document.getElementById('deleteTeacherModal'));
                        deleteModal.hide();
                        table.ajax.reload();
                        alert('Xóa thành công!');
                        deleteTeacherId = null;
                    },
                    error: function() {
                        alert('Không thể xóa giáo viên!');
                    }
                });
            }
        });
    });
</script>
@endpush

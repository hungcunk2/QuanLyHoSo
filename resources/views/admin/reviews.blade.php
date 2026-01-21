@extends('layouts.admin')

@section('title', 'Đánh giá người dùng')
@section('page-title', '')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-12">
            <div class="card card-block card-stretch">
                <div class="card-body p-0">
                    <div class="d-flex justify-content-between align-items-center p-3 flex-wrap gap-3">
                        <h5 class="fw-bold">Đánh giá người dùng</h5>
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
                            <option value="delete">Xóa đánh giá</option>
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
                                aria-label="Search" aria-describedby="addon-wrapping" aria-controls="reviewsTable">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="table-responsive mt-3">
            <table id="reviewsTable" class="table table-striped border">
                <thead>
                    <tr>
                        <th width="50">
                            <input type="checkbox" id="select-all-table" class="form-check-input" onclick="selectAllTable(this)">
                        </th>
                        <th>Customer</th>
                        <th>Rating</th>
                        <th>Review</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>
                            <input type="checkbox" class="form-check-input row-checkbox" name="selected_ids[]" value="1">
                        </td>
                        <td>
                            <div class="customer-info">
                                <div class="customer-avatar">
                                    <i class="fas fa-user"></i>
                                </div>
                                <div class="customer-details">
                                    <div class="customer-name">Hùng V</div>
                                    <div class="customer-email">junette718@powerscrews.com</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="rating-badge">5</span>
                        </td>
                        <td>
                            <div class="review-text">
                                Dự án biệt thự được thiết kế sang trọng, không gian xanh rộng rãi, hạ tầng đồng bộ, mang đến môi trường sống lý tưởng. Chất lượng xây dựng cao, tiến độ đảm bảo, tạo sự hài lòng và tin tưởng cho cư dân tương lai.
                            </div>
                        </td>
                        <td>
                            <button class="btn btn-sm btn-danger delete-btn" data-id="1">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <input type="checkbox" class="form-check-input row-checkbox" name="selected_ids[]" value="2">
                        </td>
                    <td>
                        <div class="customer-info">
                            <div class="customer-avatar">
                                <i class="fas fa-user"></i>
                            </div>
                            <div class="customer-details">
                                <div class="customer-name">Hùng</div>
                                <div class="customer-email">ashlen6@powerscrews.com</div>
                            </div>
                        </div>
                    </td>
                    <td>
                        <span class="rating-badge">5</span>
                    </td>
                    <td>
                        <div class="review-text">
                            Nhà thầu thi công dự án biệt thự chuyên nghiệp, quản lý chặt chẽ tiến độ, đảm bảo chất lượng, thẩm mỹ và an toàn, mang đến sự hài lòng và niềm tin cho khách hàng.
                        </div>
                    </td>
                    <td>
                        <button class="btn btn-sm btn-danger delete-btn" data-id="2">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
                    <tr>
                        <td>
                            <input type="checkbox" class="form-check-input row-checkbox" name="selected_ids[]" value="3">
                        </td>
                    <td>
                        <div class="customer-info">
                            <div class="customer-avatar">
                                <i class="fas fa-user"></i>
                            </div>
                            <div class="customer-details">
                                <div class="customer-name">Nguyệt Nguyễn</div>
                                <div class="customer-email">nguyet@gmail.com</div>
                            </div>
                        </div>
                    </td>
                    <td>
                        <span class="rating-badge">5</span>
                    </td>
                    <td>
                        <div class="review-text">
                            Nhà thầu thi công căn hộ đáng tin cậy, đảm bảo từng chi tiết từ kết cấu đến nội thất. Công trình hoàn thiện hiện đại, tiện nghi, mang lại sự an tâm và hài lòng cho gia chủ.
                        </div>
                    </td>
                    <td>
                        <button class="btn btn-sm btn-danger delete-btn" data-id="3">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
                    <tr>
                        <td>
                            <input type="checkbox" class="form-check-input row-checkbox" name="selected_ids[]" value="4">
                        </td>
                    <td>
                        <div class="customer-info">
                            <div class="customer-avatar">
                                <i class="fas fa-user"></i>
                            </div>
                            <div class="customer-details">
                                <div class="customer-name">Xuân Dương</div>
                                <div class="customer-email">xuanduong@gmail.com</div>
                            </div>
                        </div>
                    </td>
                    <td>
                        <span class="rating-badge">5</span>
                    </td>
                    <td>
                        <div class="review-text">
                            Dự án được thiết kế và thi công rất chuyên nghiệp, đảm bảo chất lượng cao.
                        </div>
                    </td>
                    <td>
                        <button class="btn btn-sm btn-danger delete-btn" data-id="4">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
                    <tr>
                        <td>
                            <input type="checkbox" class="form-check-input row-checkbox" name="selected_ids[]" value="5">
                        </td>
                    <td>
                        <div class="customer-info">
                            <div class="customer-avatar">
                                <i class="fas fa-user"></i>
                            </div>
                            <div class="customer-details">
                                <div class="customer-name">Nam Hải</div>
                                <div class="customer-email">namhai@gmail.com</div>
                            </div>
                        </div>
                    </td>
                    <td>
                        <span class="rating-badge">5</span>
                    </td>
                    <td>
                        <div class="review-text">
                            Chất lượng xây dựng tuyệt vời, tiến độ đúng hẹn, nhân viên nhiệt tình.
                        </div>
                    </td>
                    <td>
                        <button class="btn btn-sm btn-danger delete-btn" data-id="5">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
                    <tr>
                        <td>
                            <input type="checkbox" class="form-check-input row-checkbox" name="selected_ids[]" value="6">
                        </td>
                    <td>
                        <div class="customer-info">
                            <div class="customer-avatar">
                                <i class="fas fa-user"></i>
                            </div>
                            <div class="customer-details">
                                <div class="customer-name">Lý Dương</div>
                                <div class="customer-email">lyduong@gmail.com</div>
                            </div>
                        </div>
                    </td>
                    <td>
                        <span class="rating-badge">5</span>
                    </td>
                    <td>
                        <div class="review-text">
                            Hài lòng với dịch vụ và chất lượng công trình.
                        </div>
                    </td>
                    <td>
                        <button class="btn btn-sm btn-danger delete-btn" data-id="6">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
                    <tr>
                        <td>
                            <input type="checkbox" class="form-check-input row-checkbox" name="selected_ids[]" value="7">
                        </td>
                    <td>
                        <div class="customer-info">
                            <div class="customer-avatar">
                                <i class="fas fa-user"></i>
                            </div>
                            <div class="customer-details">
                                <div class="customer-name">Quách Tĩnh</div>
                                <div class="customer-email">tinh@gmail.com.vn</div>
                            </div>
                        </div>
                    </td>
                    <td>
                        <span class="rating-badge">5</span>
                    </td>
                    <td>
                        <div class="review-text">
                            Dự án đẹp, không gian sống lý tưởng, giá trị đầu tư tốt.
                        </div>
                    </td>
                    <td>
                        <button class="btn btn-sm btn-danger delete-btn" data-id="7">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function selectAllTable(checkbox) {
        const isChecked = checkbox.checked;
        $('.row-checkbox').prop('checked', isChecked);
        updateQuickAction();
    }

    function updateQuickAction() {
        const checkedCount = $('.row-checkbox:checked').length;
        if (checkedCount > 0) {
            $('#quick-action-type').prop('disabled', false);
        } else {
            $('#quick-action-type').prop('disabled', true);
            $('#quick-action-type').val('');
            $('#quick-action-apply').prop('disabled', true);
        }
    }

    $(document).ready(function() {
        // Initialize DataTable
        $('#reviewsTable').DataTable({
            paging: true,
            searching: false,
            ordering: true,
            info: true,
            pageLength: 10,
            language: {
                paginate: {
                    next: 'Next',
                    previous: 'Previous'
                },
                processing: "Đang xử lý..."
            },
            dom: '<"row align-items-center"><"table-responsive my-3 mt-3 mb-2 pb-1" rt><"row align-items-center data_table_widgets" <"col-md-6" <"d-flex align-items-center flex-wrap gap-3" l i>><"col-md-6" p>><"clear">'
        });
        
        // Search functionality
        $('.dt-search').on('keyup', function() {
            $('#reviewsTable').DataTable().search(this.value).draw();
        });
        
        // Row checkbox change
        $(document).on('change', '.row-checkbox', function() {
            updateQuickAction();
        });
        
        // Quick action type change
        $('#quick-action-type').on('change', function() {
            const actionValue = $(this).val();
            if (actionValue && $('.row-checkbox:checked').length > 0) {
                $('#quick-action-apply').prop('disabled', false);
            } else {
                $('#quick-action-apply').prop('disabled', true);
            }
        });
        
        // Delete button
        $(document).on('click', '.delete-btn', function() {
            if (confirm('Bạn có chắc chắn muốn xóa đánh giá này?')) {
                const id = $(this).data('id');
                // Add delete logic here
                console.log('Delete review:', id);
            }
        });
        
        // Bulk action
        $('#quick-action-apply').on('click', function(e) {
            e.preventDefault();
            const action = $('#quick-action-type').val();
            const selected = $('.row-checkbox:checked');
            
            if (!action) {
                alert('Vui lòng chọn hành động');
                return;
            }
            
            if (selected.length === 0) {
                alert('Vui lòng chọn ít nhất một mục');
                return;
            }
            
            if (confirm('Bạn có chắc chắn muốn thực hiện thao tác này?')) {
                const ids = selected.map(function() {
                    return $(this).val();
                }).get();
                
                console.log('Bulk action:', action, 'IDs:', ids);
                // Add bulk action logic here
            }
        });
    });
</script>
@endpush

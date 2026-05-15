@extends('layouts.admin')

@section('title', 'Quản lý lớp')
@section('page-title', '')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-12">
            <div class="card card-block card-stretch">
                <div class="card-body p-0">
                    <div class="d-flex justify-content-between align-items-center p-3 flex-wrap gap-3 admin-page-header">
                        <h5 class="fw-bold mb-0">Quản lý lớp</h5>
                        <button type="button" class="btn btn-primary btn-create" data-bs-toggle="modal" data-bs-target="#lopModal" id="btnOpenCreateLop">
                            <i class="fas fa-plus"></i> Tạo lớp mới
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@include('admin.partials.lop-panel')
@endsection

@include('admin.partials.lop-panel-scripts')

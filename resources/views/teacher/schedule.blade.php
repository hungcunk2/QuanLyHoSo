@extends('layouts.teacher')

@section('title', 'Lịch dạy')
@section('page-title', 'Lịch dạy')

@section('content')
@include('partials.week-schedule-table', [
    'scheduleRouteName' => 'teacher.schedule',
    'currentDate' => $currentDate,
    'scheduleGrid' => $scheduleGrid,
    'headingText' => 'Lịch dạy theo tuần (theo học phần được phân công)',
])
@endsection

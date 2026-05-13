@extends('layouts.teacher')

@section('title', 'Lịch dạy')
@section('page-title', '')

@section('content')
@include('partials.week-schedule-table', [
    'scheduleRouteName' => 'teacher.schedule',
    'currentDate' => $currentDate,
    'scheduleGrid' => $scheduleGrid,
    'headingText' => 'Lịch dạy theo tuần',
])
@endsection

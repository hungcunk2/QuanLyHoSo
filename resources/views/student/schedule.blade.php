@extends('layouts.student')

@section('title', 'Lịch Học')
@section('page-title', '')

@section('content')
@include('partials.week-schedule-table', [
    'scheduleRouteName' => 'student.schedule',
    'currentDate' => $currentDate,
    'scheduleGrid' => $scheduleGrid,
    'headingText' => 'Lịch học, lịch thi theo tuần',
])
@endsection

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SubjectRegistration;
use App\Models\CourseOffering;
use App\Models\ClassRoom;
use App\Models\Subject;
use App\Models\Teacher;
use Illuminate\Http\Request;
use App\Models\CourseOfferingSchedule;
use App\Services\CourseOfferingScheduleConflictService;
use App\Support\OfferingWeekCalendar;
use Illuminate\Support\Facades\Schema;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;

class SubjectRegistrationController extends Controller
{
    public function index()
    {
        $classes = ClassRoom::orderBy('ma_lop')->get(['id', 'ma_lop', 'ten_lop']);
        $subjects = Subject::orderBy('ma_mon_hoc')->get(['id', 'ma_mon_hoc', 'ten_mon_hoc']);
        $teachers = Teacher::orderBy('ho_ten')->get(['id', 'msgv', 'ho_ten']);
        $weekdays = CourseOffering::weekdays();
        $periodLabels = CourseOffering::periodLabels();
        return view('admin.subject-registrations', compact('classes', 'subjects', 'teachers', 'weekdays', 'periodLabels'));
    }

    public function getData(Request $request)
    {
        $query = CourseOffering::with(['subject', 'classRoom', 'teacherLyThuyet', 'teacherThucHanh'])
            ->orderBy('created_at', 'desc');

        $weekdays = CourseOffering::weekdays();

        return DataTables::of($query)
            ->orderColumn('created_at_formatted', 'created_at $1')
            ->addColumn('created_at_formatted', function ($row) {
                return $row->created_at ? $row->created_at->format('d/m/Y H:i') : '—';
            })
            ->addColumn('subject_info', function ($row) {
                return $row->subject ? $row->subject->ma_mon_hoc . ' - ' . $row->subject->ten_mon_hoc : '—';
            })
            ->addColumn('class_info', function ($row) {
                return $row->classRoom ? $row->classRoom->ma_lop . ' - ' . $row->classRoom->ten_lop : '—';
            })
            ->addColumn('teacher_info', function ($row) {
                $lt = $row->teacherLyThuyet?->ho_ten;
                $th = $row->teacherThucHanh?->ho_ten;
                if ($lt && $th && $lt !== $th) {
                    return 'LT: ' . $lt . '<br>TH: ' . $th;
                }
                if ($lt) {
                    return $lt;
                }
                if ($th) {
                    return $th;
                }
                return '—';
            })
            ->addColumn('date_range', function ($row) {
                $start = $row->ngay_bat_dau_hoc ? $row->ngay_bat_dau_hoc->format('d/m/Y') : '—';
                $end = $row->ngay_ket_thuc_hoc ? $row->ngay_ket_thuc_hoc->format('d/m/Y') : '—';
                return $start . ' → ' . $end;
            })
            ->addColumn('schedule_summary', function ($row) use ($weekdays) {
                $parts = [];
                if ($row->thu_ly_thuyet && $row->tiet_ly_thuyet) {
                    $parts[] = 'LT: ' . ($weekdays[$row->thu_ly_thuyet] ?? 'T' . $row->thu_ly_thuyet) . ' tiết ' . $row->tiet_ly_thuyet;
                }
                if ($row->thu_thuc_hanh && $row->tiet_thuc_hanh) {
                    $parts[] = 'TH: ' . ($weekdays[$row->thu_thuc_hanh] ?? 'T' . $row->thu_thuc_hanh) . ' tiết ' . $row->tiet_thuc_hanh;
                }
                return $parts ? implode('; ', $parts) : '—';
            })
            ->addColumn('offering_status', function ($row) {
                $today = Carbon::today();
                if ($row->is_cancelled) {
                    return '<span class="badge bg-danger">Đã hủy</span>';
                }
                if ($row->ngay_bat_dau_hoc && $row->ngay_bat_dau_hoc->lte($today)) {
                    return '<span class="badge bg-success">Đang học</span>';
                }
                if (
                    $row->ngay_mo_dang_ky && $row->ngay_ket_thuc_dang_ky &&
                    $row->ngay_mo_dang_ky->lte($today) && $row->ngay_ket_thuc_dang_ky->gte($today)
                ) {
                    return '<span class="badge bg-warning text-dark">Đang chờ sinh viên đăng kí</span>';
                }
                return '<span class="badge bg-light text-dark">—</span>';
            })
            ->addColumn('action', function ($row) {
                $daBatDau = $row->ngay_bat_dau_hoc && $row->ngay_bat_dau_hoc->lte(Carbon::today());
                $nameAttr = e($row->ten_hoc_phan);

                return '<div class="d-inline-flex gap-2 align-items-center flex-wrap">'
                    . ($daBatDau
                        ? '<span class="text-muted small me-1" title="Đã bắt đầu học"><i class="fas fa-lock me-1"></i>Đã bắt đầu</span>'
                        : '<button type="button" class="btn btn-sm btn-primary edit-offering-btn" data-id="' . $row->id . '" title="Chỉnh sửa"><i class="fas fa-edit"></i></button>'
                    )
                    . '<button type="button" class="btn btn-sm btn-warning reschedule-offering-btn" data-id="' . $row->id . '" title="Dời lịch"><i class="fas fa-random"></i></button>'
                    . '<button type="button" class="btn btn-sm btn-danger delete-offering-btn" data-id="' . $row->id . '" data-name="' . $nameAttr . '" title="Xóa học phần"><i class="fas fa-trash"></i></button>'
                    . '</div>';
            })
            ->rawColumns(['teacher_info', 'offering_status', 'action'])
            ->make(true);
    }

    public function offeringSessions(int $id)
    {
        $offering = CourseOffering::query()
            ->with(['subject', 'classRoom', 'teacherLyThuyet', 'teacherThucHanh', 'schedules.teacher', 'schedules.classRoom'])
            ->findOrFail($id);

        $dateParam = request()->query('date');
        $currentDate = $dateParam ? Carbon::parse($dateParam) : Carbon::today();
        $weekStart = $currentDate->copy()->startOfWeek(Carbon::MONDAY)->startOfDay();
        $weekEnd = $currentDate->copy()->endOfWeek(Carbon::SUNDAY)->endOfDay();

        $weekdays = CourseOffering::weekdays();

        $sessions = [];

        $pausedSet = $offering->schedules
            ->where('loai', 'tam_ngung')
            ->filter(function ($sc) use ($weekStart, $weekEnd) {
                if (! $sc->ngay_ap_dung) {
                    return false;
                }
                $d = Carbon::parse($sc->ngay_ap_dung);
                return $d->betweenIncluded($weekStart, $weekEnd);
            })
            ->map(fn ($sc) => (string) ($sc->paused_session_key ?? ''))
            ->filter(fn ($k) => $k !== '')
            ->values()
            ->all();
        $pausedSet = array_fill_keys($pausedSet, true);

        // Helper: get date in this week for a VN weekday
        $dateOfThu = function (int $thuVn) use ($weekStart): Carbon {
            $offset = $thuVn === 8 ? 6 : max(0, min(6, $thuVn - 2));
            return $weekStart->copy()->addDays($offset);
        };

        if ($offering->thu_ly_thuyet && ($offering->tiet_ly_thuyet ?? '') !== '') {
            $sessionKey = 'base_lt';
            if (! isset($pausedSet[$sessionKey])) {
                $d = $dateOfThu((int) $offering->thu_ly_thuyet);
            $sessions[] = [
                'key' => $sessionKey,
                'loai' => 'ly_thuyet',
                'label' => 'Lý thuyết',
                'date' => $d->toDateString(),
                'thu' => (int) $offering->thu_ly_thuyet,
                'tiet' => (string) $offering->tiet_ly_thuyet,
                'teacher' => $offering->teacherLyThuyet?->ho_ten ?? '',
                'room' => $offering->classRoom?->ma_lop ? ($offering->classRoom->ma_lop.' - '.$offering->classRoom->ten_lop) : '',
                'moved_from' => $offering->lt_moved_from ?: null,
            ];
            }
        }

        $ltExtra = $offering->schedules->where('loai', 'ly_thuyet')->sortBy('id')->values();
        foreach ($ltExtra as $idx => $sc) {
            if (! $sc->thu || ($sc->tiet ?? '') === '') {
                continue;
            }
            if ($sc->ngay_ap_dung) {
                // one-off moved/additional session handled below
                continue;
            }
            $sessionKey = 'sc_'.$sc->id;
            if (isset($pausedSet[$sessionKey])) {
                continue;
            }
            $d = $dateOfThu((int) $sc->thu);
            $sessions[] = [
                'key' => $sessionKey,
                'loai' => 'ly_thuyet',
                'label' => 'Lý thuyết (Buổi '.($idx + 2).')',
                'date' => $d->toDateString(),
                'thu' => (int) $sc->thu,
                'tiet' => (string) $sc->tiet,
                'teacher' => $sc->teacher?->ho_ten ?? '',
                'room' => $offering->classRoom?->ma_lop ? ($offering->classRoom->ma_lop.' - '.$offering->classRoom->ten_lop) : '',
                'moved_from' => $sc->moved_from ?: null,
            ];
        }

        if ($offering->thu_thuc_hanh && ($offering->tiet_thuc_hanh ?? '') !== '') {
            $sessionKey = 'base_th';
            if (! isset($pausedSet[$sessionKey])) {
                $d = $dateOfThu((int) $offering->thu_thuc_hanh);
            $sessions[] = [
                'key' => $sessionKey,
                'loai' => 'thuc_hanh',
                'label' => 'Thực hành (Nhóm 1)',
                'date' => $d->toDateString(),
                'thu' => (int) $offering->thu_thuc_hanh,
                'tiet' => (string) $offering->tiet_thuc_hanh,
                'teacher' => $offering->teacherThucHanh?->ho_ten ?? '',
                'room' => $offering->classRoomThucHanh?->ma_lop ? ($offering->classRoomThucHanh->ma_lop.' - '.$offering->classRoomThucHanh->ten_lop) : '',
                'moved_from' => $offering->th_moved_from ?: null,
            ];
            }
        }

        $thExtra = $offering->schedules->where('loai', 'thuc_hanh')->sortBy('id')->values();
        foreach ($thExtra as $idx => $sc) {
            if (! $sc->thu || ($sc->tiet ?? '') === '') {
                continue;
            }
            if ($sc->ngay_ap_dung) {
                continue;
            }
            $sessionKey = 'sc_'.$sc->id;
            if (isset($pausedSet[$sessionKey])) {
                continue;
            }
            $d = $dateOfThu((int) $sc->thu);
            $sessions[] = [
                'key' => $sessionKey,
                'loai' => 'thuc_hanh',
                'label' => 'Thực hành (Nhóm '.($idx + 2).')',
                'date' => $d->toDateString(),
                'thu' => (int) $sc->thu,
                'tiet' => (string) $sc->tiet,
                'teacher' => $sc->teacher?->ho_ten ?? '',
                'room' => $sc->classRoom?->ma_lop ? ($sc->classRoom->ma_lop.' - '.$sc->classRoom->ten_lop) : '',
                'moved_from' => $sc->moved_from ?: null,
            ];
        }

        // One-off: buổi dời/tạo thêm (có ngay_ap_dung) + buổi tạm ngưng theo ngày
        $oneOff = $offering->schedules
            ->filter(function ($sc) use ($weekStart, $weekEnd) {
                if (! $sc->ngay_ap_dung) {
                    return false;
                }
                $d = Carbon::parse($sc->ngay_ap_dung);
                return $d->betweenIncluded($weekStart, $weekEnd);
            })
            ->sortBy('id')
            ->values();
        foreach ($oneOff as $sc) {
            if (! $sc->thu || ($sc->tiet ?? '') === '') {
                continue;
            }
            $d = Carbon::parse($sc->ngay_ap_dung);
            $sessions[] = [
                'key' => ($sc->loai === 'tam_ngung') ? ('pause_'.$sc->id) : ('one_'.$sc->id),
                'loai' => (string) $sc->loai,
                'label' => $sc->loai === 'tam_ngung' ? 'Tạm ngưng' : (($sc->loai === 'ly_thuyet') ? 'Lý thuyết (Dời)' : 'Thực hành (Dời)'),
                'date' => $d->toDateString(),
                'thu' => (int) $sc->thu,
                'tiet' => (string) $sc->tiet,
                'teacher' => $sc->teacher?->ho_ten ?? '',
                'room' => $sc->classRoom?->ma_lop ? ($sc->classRoom->ma_lop.' - '.$sc->classRoom->ten_lop) : '',
                'moved_from' => $sc->moved_from ?: null,
                'origin_key' => $sc->origin_session_key ?: null,
                'paused_key' => $sc->paused_session_key ?: null,
            ];
        }

        return response()->json([
            'offering' => [
                'id' => $offering->id,
                'ten_hoc_phan' => $offering->ten_hoc_phan,
                'subject' => $offering->subject?->ma_mon_hoc ? ($offering->subject->ma_mon_hoc.' - '.$offering->subject->ten_mon_hoc) : '',
                'class' => $offering->classRoom?->ma_lop ? ($offering->classRoom->ma_lop.' - '.$offering->classRoom->ten_lop) : '',
                'date_range' => (optional($offering->ngay_bat_dau_hoc)->format('d/m/Y') ?? '—').' → '.(optional($offering->ngay_ket_thuc_hoc)->format('d/m/Y') ?? '—'),
                'start_date' => optional($offering->ngay_bat_dau_hoc)->toDateString(),
                'end_date' => optional($offering->ngay_ket_thuc_hoc)->toDateString(),
            ],
            'weekdays' => $weekdays,
            'sessions' => $sessions,
            'week_start' => $weekStart->toDateString(),
        ]);
    }

    public function rescheduleSession(Request $request, int $id)
    {
        $offering = CourseOffering::query()->with('schedules')->findOrFail($id);
        $hasEffectiveDateCols = Schema::hasColumn('course_offering_schedules', 'ngay_ap_dung');

        $data = $request->validate([
            'session_key' => ['required', 'string', 'max:50'],
            'date_old' => ['required', 'date'],
            'date_new' => ['required', 'date'],
            'tiet' => ['required', 'string', 'max:50'],
            'force' => ['nullable', 'boolean'],
        ]);

        $sessionKey = (string) $data['session_key'];
        $dateOld = Carbon::parse((string) $data['date_old'])->toDateString();
        $dateNew = Carbon::parse((string) $data['date_new'])->toDateString();
        $thu = OfferingWeekCalendar::thuVnFromDate(Carbon::parse($dateNew));
        $tiet = trim((string) $data['tiet']);
        $force = (bool) ($data['force'] ?? false);
        if ($tiet === '') {
            return response()->json(['message' => 'Tiết không hợp lệ.'], 422);
        }

        $today = Carbon::today()->toDateString();
        $start = $offering->ngay_bat_dau_hoc?->toDateString();
        $end = $offering->ngay_ket_thuc_hoc?->toDateString();
        if ($end && $dateNew > $end) {
            return response()->json(['message' => 'Ngày dời phải trước hoặc bằng ngày kết thúc học.'], 422);
        }
        if ($start && $dateNew < $start) {
            return response()->json(['message' => 'Ngày dời phải sau hoặc bằng ngày bắt đầu học.'], 422);
        }
        if ($dateNew <= $today) {
            return response()->json(['message' => 'Ngày dời phải sau ngày hiện tại.'], 422);
        }

        // ====== Rule 50% học sinh không trùng (chỉ áp dụng khi không ép lịch) ======
        if (! $force) {
            $newSlot = ['thu' => $thu, 'periods' => CourseOfferingScheduleConflictService::parsePeriods($tiet)];
            if ($newSlot['periods'] === []) {
                return response()->json(['message' => 'Tiết không hợp lệ.'], 422);
            }

            $regs = SubjectRegistration::query()
                ->where('course_offering_id', $offering->id)
                ->where('status', '!=', 'cancelled')
                ->get(['student_id']);

            $studentIds = $regs->pluck('student_id')->unique()->values();
            $total = (int) $studentIds->count();
            if ($total > 0) {
                $allRegs = SubjectRegistration::query()
                    ->whereIn('student_id', $studentIds)
                    ->where('status', '!=', 'cancelled')
                    ->whereNotNull('course_offering_id')
                    ->where('course_offering_id', '!=', $offering->id)
                    ->get(['student_id', 'course_offering_id', 'th_group_index']);

                $otherOfferingIds = $allRegs->pluck('course_offering_id')->unique()->values();
                $otherOfferings = collect();
                if ($otherOfferingIds->isNotEmpty()) {
                    $otherOfferings = CourseOffering::query()
                        ->whereIn('id', $otherOfferingIds)
                        ->with('schedules')
                        ->get()
                        ->keyBy('id');
                }

                // Map: student_id -> [offeringId => th_group_index]
                $map = [];
                foreach ($allRegs as $r) {
                    $sid = (int) $r->student_id;
                    $oid = (int) $r->course_offering_id;
                    if (! isset($map[$sid])) {
                        $map[$sid] = [];
                    }
                    $map[$sid][$oid] = $r->th_group_index === null ? null : (int) $r->th_group_index;
                }

                $startA = $offering->ngay_bat_dau_hoc;
                $endA = $offering->ngay_ket_thuc_hoc;

                $conflictCount = 0;
                foreach ($studentIds as $sid) {
                    $sid = (int) $sid;
                    $studentHasConflict = false;
                    foreach (($map[$sid] ?? []) as $oid => $thIdx) {
                        $other = $otherOfferings->get((int) $oid);
                        if (! $other) {
                            continue;
                        }

                        // One-off theo ngày: nếu ngày dời không nằm trong thời gian học của môn kia thì bỏ qua
                        $startB = $other->ngay_bat_dau_hoc;
                        $endB = $other->ngay_ket_thuc_hoc;
                        if ($startB && $endB) {
                            $dNew = Carbon::parse($dateNew);
                            if ($dNew->lt($startB) || $dNew->gt($endB)) {
                                continue;
                            }
                        }

                        // Build slots của môn khác theo LT + TH (theo nhóm đã chọn nếu có)
                        $ltThu = [$other->thu_ly_thuyet, ...collect($other->schedules ?? [])->where('loai', 'ly_thuyet')->sortBy('id')->pluck('thu')->all()];
                        $ltTiet = [$other->tiet_ly_thuyet, ...collect($other->schedules ?? [])->where('loai', 'ly_thuyet')->sortBy('id')->pluck('tiet')->all()];

                        $thGroups = [];
                        $thSchedules = collect($other->schedules ?? collect())->where('loai', 'thuc_hanh')->sortBy('id')->values();
                        if ($other->thu_thuc_hanh && ($other->tiet_thuc_hanh ?? '') !== '') {
                            $thGroups[] = ['thu' => (int) $other->thu_thuc_hanh, 'tiet' => (string) $other->tiet_thuc_hanh];
                        }
                        foreach ($thSchedules as $sc) {
                            if ($sc->thu && ($sc->tiet ?? '') !== '') {
                                $thGroups[] = ['thu' => (int) $sc->thu, 'tiet' => (string) $sc->tiet];
                            }
                        }

                        $thuTh = [];
                        $tietTh = [];
                        if (count($thGroups) > 0) {
                            if ($thIdx !== null && $thIdx >= 1 && $thIdx <= count($thGroups)) {
                                $thuTh = [$thGroups[$thIdx - 1]['thu']];
                                $tietTh = [$thGroups[$thIdx - 1]['tiet']];
                            } else {
                                // Nếu chưa có nhóm đã chọn -> check tất cả nhóm TH để tránh lọt
                                $thuTh = array_map(fn ($g) => $g['thu'], $thGroups);
                                $tietTh = array_map(fn ($g) => $g['tiet'], $thGroups);
                            }
                        }

                        $otherSlots = CourseOfferingScheduleConflictService::slotsFromRequestArrays($ltThu, $ltTiet, $thuTh, $tietTh);
                        foreach ($otherSlots as $os) {
                            if ((int) $os['thu'] !== (int) $newSlot['thu']) {
                                continue;
                            }
                            $intersect = array_values(array_intersect($os['periods'], $newSlot['periods']));
                            if ($intersect !== []) {
                                $studentHasConflict = true;
                                break 2;
                            }
                        }
                    }
                    if ($studentHasConflict) {
                        $conflictCount++;
                    }
                }

                $nonConflict = $total - $conflictCount;
                if ($nonConflict / max(1, $total) < 0.5) {
                    return response()->json([
                        'message' => 'Không thể dời lịch: chỉ có '.$nonConflict.'/'.$total.' học sinh không bị trùng (cần ≥ 50%). Nếu vẫn muốn, bấm "Ép lịch".',
                    ], 422);
                }
            }
        }

        if ($sessionKey === 'base_lt') {
            $movedFrom = '';
            // Cancel buổi cũ -> tạo schedule loai=tam_ngung ở vị trí cũ
            if ($offering->thu_ly_thuyet && ($offering->tiet_ly_thuyet ?? '') !== '') {
                $movedFrom = (CourseOffering::weekdays()[(int) $offering->thu_ly_thuyet] ?? ('Thứ '.$offering->thu_ly_thuyet)).' · tiết '.(string) $offering->tiet_ly_thuyet;
                $payload = [
                    'course_offering_id' => $offering->id,
                    'teacher_id' => null,
                    'class_room_id' => null,
                    'loai' => 'tam_ngung',
                    'thu' => (int) $offering->thu_ly_thuyet,
                    'tiet' => (string) $offering->tiet_ly_thuyet,
                    'thi_buoi_thu' => null,
                ];
                if ($hasEffectiveDateCols) {
                    $payload['ngay_ap_dung'] = $dateOld;
                    $payload['paused_session_key'] = 'base_lt';
                }
                CourseOfferingSchedule::create($payload);
            }
            // Tạo buổi mới theo ngày (không ảnh hưởng các tuần khác)
            $payload = [
                'course_offering_id' => $offering->id,
                'teacher_id' => $offering->teacher_id_ly_thuyet,
                'class_room_id' => null,
                'loai' => 'ly_thuyet',
                'thu' => $thu,
                'tiet' => $tiet,
                'moved_from' => $movedFrom ?: null,
                'thi_buoi_thu' => null,
            ];
            if ($hasEffectiveDateCols) {
                $payload['ngay_ap_dung'] = $dateNew;
                $payload['origin_session_key'] = 'base_lt';
            }
            CourseOfferingSchedule::create($payload);
        } elseif ($sessionKey === 'base_th') {
            $movedFrom = '';
            if ($offering->thu_thuc_hanh && ($offering->tiet_thuc_hanh ?? '') !== '') {
                $movedFrom = (CourseOffering::weekdays()[(int) $offering->thu_thuc_hanh] ?? ('Thứ '.$offering->thu_thuc_hanh)).' · tiết '.(string) $offering->tiet_thuc_hanh;
                $payload = [
                    'course_offering_id' => $offering->id,
                    'teacher_id' => null,
                    'class_room_id' => null,
                    'loai' => 'tam_ngung',
                    'thu' => (int) $offering->thu_thuc_hanh,
                    'tiet' => (string) $offering->tiet_thuc_hanh,
                    'thi_buoi_thu' => null,
                ];
                if ($hasEffectiveDateCols) {
                    $payload['ngay_ap_dung'] = $dateOld;
                    $payload['paused_session_key'] = 'base_th';
                }
                CourseOfferingSchedule::create($payload);
            }
            $payload = [
                'course_offering_id' => $offering->id,
                'teacher_id' => $offering->teacher_id_thuc_hanh,
                'class_room_id' => $offering->class_room_id_thuc_hanh,
                'loai' => 'thuc_hanh',
                'thu' => $thu,
                'tiet' => $tiet,
                'moved_from' => $movedFrom ?: null,
                'thi_buoi_thu' => null,
            ];
            if ($hasEffectiveDateCols) {
                $payload['ngay_ap_dung'] = $dateNew;
                $payload['origin_session_key'] = 'base_th';
            }
            CourseOfferingSchedule::create($payload);
        } elseif (str_starts_with($sessionKey, 'sc_')) {
            $sid = (int) substr($sessionKey, 3);
            $sc = CourseOfferingSchedule::query()
                ->where('course_offering_id', $offering->id)
                ->where('id', $sid)
                ->firstOrFail();
            $oldLoai = (string) $sc->loai;
            $movedFrom = (CourseOffering::weekdays()[(int) $sc->thu] ?? ('Thứ '.$sc->thu)).' · tiết '.(string) $sc->tiet;
            $payload = [
                'course_offering_id' => $offering->id,
                'teacher_id' => null,
                'class_room_id' => null,
                'loai' => 'tam_ngung',
                'thu' => (int) $sc->thu,
                'tiet' => (string) $sc->tiet,
                'thi_buoi_thu' => null,
                'moved_from' => null,
            ];
            if ($hasEffectiveDateCols) {
                $payload['ngay_ap_dung'] = $dateOld;
                $payload['paused_session_key'] = 'sc_'.$sc->id;
            }
            CourseOfferingSchedule::create($payload);

            // Tạo 1 buổi mới với loai cũ tại vị trí mới (giữ teacher/room nếu có)
            $payload = [
                'course_offering_id' => $offering->id,
                'teacher_id' => $sc->teacher_id,
                'class_room_id' => $sc->class_room_id,
                'loai' => $oldLoai,
                'thu' => $thu,
                'tiet' => $tiet,
                'thi_buoi_thu' => $sc->thi_buoi_thu,
                'moved_from' => $movedFrom,
            ];
            if ($hasEffectiveDateCols) {
                $payload['ngay_ap_dung'] = $dateNew;
                $payload['origin_session_key'] = 'sc_'.$sc->id;
            }
            CourseOfferingSchedule::create($payload);
        } else {
            return response()->json(['message' => 'Buổi cần dời không hợp lệ.'], 422);
        }

        // Reload & chặn nếu tự trùng lịch nội bộ (cùng thứ giao nhau tiết)
        $offering->load('schedules');
        $slots = CourseOfferingScheduleConflictService::slotsFromRequestArrays(
            [$offering->thu_ly_thuyet, ...collect($offering->schedules ?? [])->where('loai', 'ly_thuyet')->pluck('thu')->all()],
            [$offering->tiet_ly_thuyet, ...collect($offering->schedules ?? [])->where('loai', 'ly_thuyet')->pluck('tiet')->all()],
            [$offering->thu_thuc_hanh, ...collect($offering->schedules ?? [])->where('loai', 'thuc_hanh')->pluck('thu')->all()],
            [$offering->tiet_thuc_hanh, ...collect($offering->schedules ?? [])->where('loai', 'thuc_hanh')->pluck('tiet')->all()],
        );
        for ($i = 0; $i < count($slots); $i++) {
            for ($j = $i + 1; $j < count($slots); $j++) {
                if ($slots[$i]['thu'] !== $slots[$j]['thu']) {
                    continue;
                }
                $intersect = array_values(array_intersect($slots[$i]['periods'], $slots[$j]['periods']));
                if ($intersect !== []) {
                    return response()->json(['message' => 'Lịch bị trùng nội bộ sau khi dời (trùng tiết cùng thứ).'], 422);
                }
            }
        }

        return response()->json(['message' => 'Đã dời lịch thành công.']);
    }

    public function pauseSession(Request $request, int $id)
    {
        $offering = CourseOffering::query()->with('schedules')->findOrFail($id);
        $hasEffectiveDateCols = Schema::hasColumn('course_offering_schedules', 'ngay_ap_dung');

        $data = $request->validate([
            'session_key' => ['required', 'string', 'max:50'],
            'date_old' => ['required', 'date'],
        ]);

        $sessionKey = (string) $data['session_key'];
        $dateOld = Carbon::parse((string) $data['date_old'])->toDateString();

        $today = Carbon::today()->toDateString();
        $start = $offering->ngay_bat_dau_hoc?->toDateString();
        $end = $offering->ngay_ket_thuc_hoc?->toDateString();
        if ($end && $dateOld > $end) {
            return response()->json(['message' => 'Ngày tạm ngưng phải trước hoặc bằng ngày kết thúc học.'], 422);
        }
        if ($start && $dateOld < $start) {
            return response()->json(['message' => 'Ngày tạm ngưng phải sau hoặc bằng ngày bắt đầu học.'], 422);
        }
        if ($dateOld <= $today) {
            return response()->json(['message' => 'Chỉ cho tạm ngưng các buổi sau ngày hiện tại.'], 422);
        }

        if ($sessionKey === 'base_lt') {
            if (! $offering->thu_ly_thuyet || ($offering->tiet_ly_thuyet ?? '') === '') {
                return response()->json(['message' => 'Buổi này không tồn tại để tạm ngưng.'], 422);
            }
            $payload = [
                'course_offering_id' => $offering->id,
                'teacher_id' => null,
                'class_room_id' => null,
                'loai' => 'tam_ngung',
                'thu' => (int) $offering->thu_ly_thuyet,
                'tiet' => (string) $offering->tiet_ly_thuyet,
                'thi_buoi_thu' => null,
                'moved_from' => null,
            ];
            if ($hasEffectiveDateCols) {
                $payload['ngay_ap_dung'] = $dateOld;
                $payload['paused_session_key'] = 'base_lt';
            }
            CourseOfferingSchedule::create($payload);
        } elseif ($sessionKey === 'base_th') {
            if (! $offering->thu_thuc_hanh || ($offering->tiet_thuc_hanh ?? '') === '') {
                return response()->json(['message' => 'Buổi này không tồn tại để tạm ngưng.'], 422);
            }
            $payload = [
                'course_offering_id' => $offering->id,
                'teacher_id' => null,
                'class_room_id' => null,
                'loai' => 'tam_ngung',
                'thu' => (int) $offering->thu_thuc_hanh,
                'tiet' => (string) $offering->tiet_thuc_hanh,
                'thi_buoi_thu' => null,
                'moved_from' => null,
            ];
            if ($hasEffectiveDateCols) {
                $payload['ngay_ap_dung'] = $dateOld;
                $payload['paused_session_key'] = 'base_th';
            }
            CourseOfferingSchedule::create($payload);
        } elseif (str_starts_with($sessionKey, 'sc_')) {
            $sid = (int) substr($sessionKey, 3);
            $sc = CourseOfferingSchedule::query()
                ->where('course_offering_id', $offering->id)
                ->where('id', $sid)
                ->firstOrFail();

            // Không overwrite buổi gốc nữa để có thể khôi phục dễ dàng:
            // tạo record tạm ngưng trùng đúng thu/tiet, phần hiển thị sẽ tự ẩn buổi gốc.
            $payload = [
                'course_offering_id' => $offering->id,
                'teacher_id' => null,
                'class_room_id' => null,
                'loai' => 'tam_ngung',
                'thu' => (int) $sc->thu,
                'tiet' => (string) $sc->tiet,
                'thi_buoi_thu' => null,
                'moved_from' => null,
            ];
            if ($hasEffectiveDateCols) {
                $payload['ngay_ap_dung'] = $dateOld;
                $payload['paused_session_key'] = 'sc_'.$sc->id;
            }
            CourseOfferingSchedule::create($payload);
        } else {
            return response()->json(['message' => 'Buổi cần tạm ngưng không hợp lệ.'], 422);
        }

        return response()->json(['message' => 'Đã tạm ngưng buổi học.']);
    }

    public function unpauseSession(Request $request, int $id)
    {
        $offering = CourseOffering::query()->with('schedules')->findOrFail($id);

        $data = $request->validate([
            'pause_key' => ['required', 'string', 'max:50'],
        ]);

        $pauseKey = (string) $data['pause_key'];
        if (! str_starts_with($pauseKey, 'pause_')) {
            return response()->json(['message' => 'Buổi tạm ngưng không hợp lệ.'], 422);
        }
        $sid = (int) substr($pauseKey, 6);
        if ($sid <= 0) {
            return response()->json(['message' => 'Buổi tạm ngưng không hợp lệ.'], 422);
        }

        $sc = CourseOfferingSchedule::query()
            ->where('course_offering_id', $offering->id)
            ->where('id', $sid)
            ->where('loai', 'tam_ngung')
            ->firstOrFail();

        $sc->delete();

        return response()->json(['message' => 'Đã bỏ tạm ngưng.']);
    }
}

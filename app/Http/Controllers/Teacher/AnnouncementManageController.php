<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\CourseOffering;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AnnouncementManageController extends Controller
{
    protected function offeringOptionsForTeacher(): array
    {
        // Lấy các lớp học phần (course_offerings) mà giáo viên đang dạy
        $user = Auth::user();
        if (! $user || $user->role !== 'teacher') {
            return [];
        }

        $teacher = \App\Models\Teacher::where('msgv', $user->username)->first();
        if (! $teacher) {
            return [];
        }

        $offerings = CourseOffering::query()
            ->where(function ($q) use ($teacher) {
                $q->where('teacher_id_ly_thuyet', $teacher->id)
                    ->orWhere('teacher_id_thuc_hanh', $teacher->id)
                    ->orWhereHas('schedules', fn ($sq) => $sq->where('teacher_id', $teacher->id));
            })
            ->with(['subject', 'classRoom', 'classRoomThucHanh'])
            ->orderByDesc('created_at')
            ->limit(300)
            ->get();

        $opts = [];
        foreach ($offerings as $o) {
            $subject = trim((string) ($o->subject?->ten_mon_hoc ?? $o->ten_hoc_phan ?? 'Học phần'));
            $hk = trim((string) ($o->hoc_ky ?? ''));
            $kh = trim((string) ($o->khoa_hoc ?? ''));
            $cls = trim((string) ($o->classRoom?->ten_lop ?? $o->classRoom?->ma_lop ?? ''));
            $label = $subject;
            $meta = trim(implode(' • ', array_values(array_filter([$hk ? ('HK '.$hk) : null, $kh ? ('Khóa '.$kh) : null, $cls ?: null]))));
            if ($meta !== '') $label .= ' ('.$meta.')';
            $opts[(int) $o->id] = $label;
        }

        ksort($opts, SORT_NUMERIC);
        return $opts;
    }

    public function index()
    {
        $items = Announcement::query()
            ->where('created_by_user_id', Auth::id())
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('teacher.notifications-manage.index', compact('items'));
    }

    public function create()
    {
        $item = new Announcement([
            'audience' => 'student',
        ]);

        $offeringOptions = $this->offeringOptionsForTeacher();
        $selectedOfferings = [];
        return view('teacher.notifications-manage.form', compact('item', 'offeringOptions', 'selectedOfferings'));
    }

    public function store(Request $request)
    {
        $offeringOptions = $this->offeringOptionsForTeacher();
        $data = $this->validated($request, array_keys($offeringOptions));
        $data['slug'] = $this->uniqueSlug($data['title']);
        $data['created_by_user_id'] = Auth::id();
        $data['audience'] = 'student';

        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $path = $file->store('announcements', 'public');
            $data['attachment_path'] = $path;
            $data['attachment_mime'] = (string) ($file->getClientMimeType() ?: $file->getMimeType());
        }

        $ann = Announcement::create($data);

        $selected = (array) $request->input('target_offerings', []);
        $selected = array_values(array_unique(array_filter(array_map('intval', $selected))));
        $rows = [];
        $now = now();
        foreach ($selected as $offeringId) {
            $rows[] = ['announcement_id' => $ann->id, 'course_offering_id' => $offeringId, 'created_at' => $now, 'updated_at' => $now];
        }
        if (!empty($rows)) {
            DB::table('announcement_offering_targets')->insert($rows);
        }

        return redirect()->route('teacher.notifications.manage.index')->with('success', 'Đã tạo thông báo.');
    }

    public function edit(Announcement $announcement)
    {
        abort_unless((int) $announcement->created_by_user_id === (int) Auth::id(), 403);
        $item = $announcement;
        $offeringOptions = $this->offeringOptionsForTeacher();
        $selectedOfferings = DB::table('announcement_offering_targets')
            ->where('announcement_id', $announcement->id)
            ->pluck('course_offering_id')
            ->all();
        return view('teacher.notifications-manage.form', compact('item', 'offeringOptions', 'selectedOfferings'));
    }

    public function update(Request $request, Announcement $announcement)
    {
        abort_unless((int) $announcement->created_by_user_id === (int) Auth::id(), 403);
        $offeringOptions = $this->offeringOptionsForTeacher();
        $data = $this->validated($request, array_keys($offeringOptions), $announcement->id);
        $data['audience'] = 'student';

        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $path = $file->store('announcements', 'public');
            $data['attachment_path'] = $path;
            $data['attachment_mime'] = (string) ($file->getClientMimeType() ?: $file->getMimeType());

            if ($announcement->attachment_path) {
                Storage::disk('public')->delete($announcement->attachment_path);
            }
        } elseif ($request->boolean('remove_attachment')) {
            if ($announcement->attachment_path) {
                Storage::disk('public')->delete($announcement->attachment_path);
            }
            $data['attachment_path'] = null;
            $data['attachment_mime'] = null;
        }

        $announcement->update($data);

        $selected = (array) $request->input('target_offerings', []);
        $selected = array_values(array_unique(array_filter(array_map('intval', $selected))));
        DB::table('announcement_offering_targets')->where('announcement_id', $announcement->id)->delete();
        $rows = [];
        $now = now();
        foreach ($selected as $offeringId) {
            $rows[] = ['announcement_id' => $announcement->id, 'course_offering_id' => $offeringId, 'created_at' => $now, 'updated_at' => $now];
        }
        if (!empty($rows)) {
            DB::table('announcement_offering_targets')->insert($rows);
        }

        return redirect()->route('teacher.notifications.manage.index')->with('success', 'Đã cập nhật thông báo.');
    }

    public function destroy(Announcement $announcement)
    {
        abort_unless((int) $announcement->created_by_user_id === (int) Auth::id(), 403);
        if ($announcement->attachment_path) {
            Storage::disk('public')->delete($announcement->attachment_path);
        }
        $announcement->delete();

        return redirect()->route('teacher.notifications.manage.index')->with('success', 'Đã xóa thông báo.');
    }

    protected function validated(Request $request, array $allowedOfferings, ?int $ignoreId = null): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'summary' => ['nullable', 'string', 'max:255'],
            'content' => ['nullable', 'string'],
            'target_offerings' => ['required', 'array', 'min:1'],
            'target_offerings.*' => ['integer', Rule::in($allowedOfferings)],
            'attachment' => ['nullable', 'file', 'mimes:pdf', 'max:10240'],
            'remove_attachment' => ['sometimes', 'boolean'],
        ], [
            'title.required' => 'Vui lòng nhập tiêu đề.',
            'target_offerings.required' => 'Vui lòng chọn ít nhất 1 lớp học phần.',
            'attachment.mimes' => 'Chỉ hỗ trợ file PDF.',
        ]);
    }

    protected function uniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $base = Str::slug($title);
        if ($base === '') $base = 'thong-bao';
        $slug = $base;
        $i = 2;
        while (
            Announcement::query()
                ->where('slug', $slug)
                ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
                ->exists()
        ) {
            $slug = $base . '-' . $i;
            $i++;
        }
        return $slug;
    }
}


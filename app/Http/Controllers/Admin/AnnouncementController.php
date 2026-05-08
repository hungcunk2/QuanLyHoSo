<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AnnouncementController extends Controller
{
    public function index()
    {
        $items = Announcement::query()
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('admin.notifications.index', compact('items'));
    }

    public function create()
    {
        $item = new Announcement([
            'audience' => 'all',
        ]);

        return view('admin.notifications.form', compact('item'));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['slug'] = $this->uniqueSlug($data['title']);
        $data['created_by_user_id'] = Auth::id();

        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $path = $file->store('announcements', 'public');
            $data['attachment_path'] = $path;
            $data['attachment_mime'] = (string) ($file->getClientMimeType() ?: $file->getMimeType());
        }

        $item = Announcement::create($data);

        return redirect()->route('admin.notifications.index')->with('success', 'Đã tạo thông báo.');
    }

    public function edit(Announcement $announcement)
    {
        $item = $announcement;
        return view('admin.notifications.form', compact('item'));
    }

    public function update(Request $request, Announcement $announcement)
    {
        $data = $this->validated($request, $announcement->id);

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

        // Only regenerate slug when title changed AND slug empty (keep stable URLs by default)
        if (($data['title'] ?? '') !== ($announcement->title ?? '') && empty($announcement->slug)) {
            $data['slug'] = $this->uniqueSlug($data['title'], $announcement->id);
        }

        $announcement->update($data);

        return redirect()->route('admin.notifications.index')->with('success', 'Đã cập nhật thông báo.');
    }

    public function destroy(Announcement $announcement)
    {
        if ($announcement->attachment_path) {
            Storage::disk('public')->delete($announcement->attachment_path);
        }
        $announcement->delete();

        return redirect()->route('admin.notifications.index')->with('success', 'Đã xóa thông báo.');
    }

    protected function validated(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'summary' => ['nullable', 'string', 'max:255'],
            'content' => ['nullable', 'string'],
            'audience' => ['required', 'string', Rule::in(['all', 'student', 'teacher'])],
            'attachment' => ['nullable', 'file', 'mimes:pdf', 'max:10240'],
            'remove_attachment' => ['sometimes', 'boolean'],
        ], [
            'title.required' => 'Vui lòng nhập tiêu đề.',
            'attachment.mimes' => 'Chỉ hỗ trợ file PDF.',
        ]);
    }

    protected function uniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $base = Str::slug($title);
        if ($base === '') {
            $base = 'thong-bao';
        }

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


<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AnnouncementController extends Controller
{
    protected function allowedAudiencesForCurrentUser(): array
    {
        if (!Auth::check()) {
            // Trang đăng nhập luôn được xem tất cả thông báo
            return ['all', 'student', 'teacher'];
        }

        $role = Auth::user()->role ?? null; // student | teacher | admin | null
        $audiences = ['all'];
        if ($role === 'student') $audiences[] = 'student';
        if ($role === 'teacher') $audiences[] = 'teacher';
        if ($role === 'admin') $audiences = ['all', 'student', 'teacher'];

        return $audiences;
    }

    public function index(Request $request)
    {
        $audiences = $this->allowedAudiencesForCurrentUser();

        $items = Announcement::query()
            ->whereIn('audience', $audiences)
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('announcements.index', compact('items'));
    }

    public function show(string $slug)
    {
        $audiences = $this->allowedAudiencesForCurrentUser();

        $item = Announcement::query()
            ->where('slug', $slug)
            ->whereIn('audience', $audiences)
            ->firstOrFail();

        if (Auth::check()) {
            DB::table('announcement_reads')->updateOrInsert(
                [
                    'announcement_id' => $item->id,
                    'user_id' => Auth::id(),
                ],
                [
                    'read_at' => now(),
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        }

        return view('announcements.show', compact('item'));
    }
}


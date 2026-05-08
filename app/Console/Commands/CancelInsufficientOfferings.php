<?php

namespace App\Console\Commands;

use App\Models\CourseOffering;
use App\Models\SubjectRegistration;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CancelInsufficientOfferings extends Command
{
    protected $signature = 'course-offerings:cancel-insufficient {--date= : Override "today" (Y-m-d)}';

    protected $description = 'Cancel course offerings after registration closes if <25% enrolled.';

    public function handle(): int
    {
        $today = $this->option('date')
            ? Carbon::parse((string) $this->option('date'))->startOfDay()
            : Carbon::today();

        // Ensure cancelled offerings have registrations cancelled as well
        $now = Carbon::now();
        DB::transaction(function () use ($now) {
            $cancelledIds = CourseOffering::query()
                ->where('is_cancelled', true)
                ->pluck('id')
                ->all();
            if ($cancelledIds === []) {
                return;
            }
            SubjectRegistration::query()
                ->whereIn('course_offering_id', $cancelledIds)
                ->where('status', '!=', 'cancelled')
                ->update([
                    'status' => 'cancelled',
                    'updated_at' => $now,
                ]);
        });

        $candidates = CourseOffering::query()
            ->where('is_cancelled', false)
            ->whereDate('ngay_ket_thuc_dang_ky', '<', $today->toDateString())
            ->get(['id', 'si_so_lop']);

        if ($candidates->isEmpty()) {
            $this->info('No offerings to check.');
            return self::SUCCESS;
        }

        $ids = $candidates->pluck('id')->all();

        $counts = SubjectRegistration::query()
            ->whereIn('course_offering_id', $ids)
            ->where('status', '!=', 'cancelled')
            ->selectRaw('course_offering_id, COUNT(*) as cnt')
            ->groupBy('course_offering_id')
            ->get()
            ->keyBy('course_offering_id')
            ->map(fn ($r) => (int) $r->cnt);

        $cancelIds = [];
        foreach ($candidates as $o) {
            $siSo = max(0, (int) $o->si_so_lop);
            $minRequired = (int) ceil($siSo * 0.25);
            $enrolled = (int) ($counts[$o->id] ?? 0);
            if ($enrolled < $minRequired) {
                $cancelIds[] = (int) $o->id;
            }
        }

        if ($cancelIds === []) {
            $this->info('All offerings meet minimum enrollment.');
            return self::SUCCESS;
        }

        DB::transaction(function () use ($cancelIds, $now) {
            CourseOffering::query()
                ->whereIn('id', $cancelIds)
                ->update([
                    'is_cancelled' => true,
                    'cancel_reason' => 'Hủy do không đủ học sinh (dưới 25% sĩ số)',
                    'cancelled_at' => $now,
                    'updated_at' => $now,
                ]);

            // Treat like there was no registration: cancel all registrations
            SubjectRegistration::query()
                ->whereIn('course_offering_id', $cancelIds)
                ->where('status', '!=', 'cancelled')
                ->update([
                    'status' => 'cancelled',
                    'updated_at' => $now,
                ]);
        });

        $this->info('Cancelled offerings: '.implode(', ', $cancelIds));
        return self::SUCCESS;
    }
}


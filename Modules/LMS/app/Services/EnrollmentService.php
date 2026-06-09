<?php

declare(strict_types=1);

namespace Modules\LMS\app\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Modules\LMS\app\Models\Cohort;
use Modules\LMS\app\Models\Enrollment;

class EnrollmentService
{
    public function enroll(Cohort $cohort, User $student, int $enrolledBy): Enrollment
    {
        abort_if(
            ! $cohort->hasCapacity(),
            422, 'This cohort has reached its maximum capacity.'
        );

        abort_if(
            Enrollment::where('cohort_id', $cohort->id)
                      ->where('student_id', $student->id)
                      ->exists(),
            422, "{$student->name} is already enrolled in this cohort."
        );

        return Enrollment::create([
            'cohort_id'   => $cohort->id,
            'student_id'  => $student->id,
            'enrolled_by' => $enrolledBy,
            'status'      => 'active',
        ]);
    }

    public function bulkEnroll(Cohort $cohort, array $studentIds, int $enrolledBy): array
    {
        $results = ['enrolled' => [], 'skipped' => []];

        foreach ($studentIds as $studentId) {
            $student = User::find($studentId);
            if (! $student) continue;

            try {
                $this->enroll($cohort, $student, $enrolledBy);
                $results['enrolled'][] = $student->name;
            } catch (\Throwable $e) {
                $results['skipped'][] = ['name' => $student->name, 'reason' => $e->getMessage()];
            }
        }

        return $results;
    }

    public function withdraw(Enrollment $enrollment): void
    {
        $enrollment->update(['status' => 'withdrawn']);
    }
}

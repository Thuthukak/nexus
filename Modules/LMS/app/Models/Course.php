<?php

declare(strict_types=1);

namespace Modules\LMS\app\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Course extends Model
{
    use HasUuids, SoftDeletes;

    protected $table    = 'lms_courses';
    protected $fillable = [
        'title', 'description', 'thumbnail_path', 'category',
        'status', 'difficulty', 'estimated_hours',
        'certificate_enabled', 'require_sequential', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'certificate_enabled'  => 'boolean',
            'require_sequential'   => 'boolean',
        ];
    }

    public function sections(): HasMany
    {
        return $this->hasMany(Section::class, 'course_id')->orderBy('order');
    }

    public function cohorts(): HasMany
    {
        return $this->hasMany(Cohort::class, 'course_id');
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(Assignment::class, 'course_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    public function getLessonCountAttribute(): int
    {
        return $this->sections->flatMap->lessons->count();
    }
}

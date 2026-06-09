<?php

declare(strict_types=1);

namespace Modules\LMS\app\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Section extends Model
{
    use HasUuids;

    protected $table    = 'lms_sections';
    protected $fillable = ['course_id', 'title', 'description', 'order'];

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class, 'course_id');
    }

    public function lessons(): HasMany
    {
        return $this->hasMany(Lesson::class, 'section_id')->orderBy('order');
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(Assignment::class, 'section_id');
    }
}

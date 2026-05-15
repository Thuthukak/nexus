<?php

declare(strict_types=1);

namespace Modules\HR\app\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class JobTitle extends Model
{
    use HasUuids;

    protected $table    = 'hr_job_titles';
    protected $fillable = ['name', 'grade', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }
}

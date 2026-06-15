<?php

declare(strict_types=1);

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class StrongPassword implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (strlen($value) < 8) {
            $fail('Password must be at least 8 characters.');
            return;
        }

        if (! preg_match('/[A-Za-z]/', $value)) {
            $fail('Password must contain at least one letter.');
            return;
        }

        if (! preg_match('/[0-9]/', $value)) {
            $fail('Password must contain at least one number.');
            return;
        }

        if (! preg_match('/[\W_]/', $value)) {
            $fail('Password must contain at least one special character (e.g. @, #, !, %).');
            return;
        }
    }
}

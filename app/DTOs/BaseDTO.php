<?php

declare(strict_types=1);

namespace App\DTOs;

abstract class BaseDTO
{
    public static function from(array $data): static
    {
        return new static(...array_values($data));
    }

    public function toArray(): array
    {
        return get_object_vars($this);
    }
}

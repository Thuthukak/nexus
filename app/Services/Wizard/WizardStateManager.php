<?php

declare(strict_types=1);

namespace App\Services\Wizard;

class WizardStateManager
{
    private string $statePath;

    public function __construct()
    {
        $dir = storage_path('app/wizard');
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        $this->statePath = $dir . '/state.json';
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->load()[$key] ?? $default;
    }

    public function set(string $key, mixed $value): void
    {
        $state       = $this->load();
        $state[$key] = $value;
        $this->save($state);
    }

    public function setMany(array $data): void
    {
        $this->save(array_merge($this->load(), $data));
    }

    public function completedSteps(): array
    {
        return $this->get('completed_steps', []);
    }

    public function markStepComplete(int $step): void
    {
        $completed   = $this->completedSteps();
        $completed[] = $step;
        $this->set('completed_steps', array_unique($completed));
    }

    public function isStepComplete(int $step): bool
    {
        return in_array($step, $this->completedSteps(), true);
    }

    public function canAccessStep(int $step): bool
    {
        if ($step === 1) return true;
        return $this->isStepComplete($step - 1);
    }

    public function clear(): void
    {
        if (file_exists($this->statePath)) {
            unlink($this->statePath);
        }
    }

    private function load(): array
    {
        if (! file_exists($this->statePath)) return [];
        $content = file_get_contents($this->statePath);
        return json_decode($content, true) ?? [];
    }

    private function save(array $state): void
    {
        file_put_contents(
            $this->statePath,
            json_encode($state, JSON_PRETTY_PRINT),
            LOCK_EX
        );
    }
}

<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\Factory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ModelEventService
{
    public function register(): void
    {
        $modelClasses = [Factory::class, Employee::class];

        foreach ($modelClasses as $modelClass) {
            $modelClass::created(fn (Model $model) => $this->logEvent('created', $model));
            $modelClass::updated(fn (Model $model) => $this->logEvent('updated', $model));
            $modelClass::deleted(fn (Model $model) => $this->logEvent('deleted', $model));
        }
    }

    protected function logEvent(string $action, Model $model): void
    {
        $payload = [
            'action' => $action,
            'model' => class_basename($model),
            'record_id' => $model->getKey(),
            'user_id' => Auth::id(),
        ];

        if ($action === 'updated') {
            $payload['changes'] = $this->buildUpdateChanges($model);
        }

        Log::info('Model event logger', $payload);
    }

    protected function buildUpdateChanges(Model $model): array
    {
        $changes = [];
        $dirty = collect($model->getChanges())->except(['updated_at'])->toArray();

        foreach ($dirty as $attribute => $newValue) {
            $changes[$attribute] = [
                'old' => $model->getOriginal($attribute),
                'new' => $newValue,
            ];
        }

        return $changes;
    }
}

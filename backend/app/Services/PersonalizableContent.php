<?php

namespace App\Services;

use App\Models\Career;
use App\Models\Multimedia;
use App\Models\Resource;
use App\Models\SuccessStory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

class PersonalizableContent
{
    private const TYPES = ['career' => Career::class, 'resource' => Resource::class, 'multimedia' => Multimedia::class, 'story' => SuccessStory::class];

    public function resolve(string $type, int $id): Model
    {
        $model = self::TYPES[$type] ?? null;
        if (! $model) {
            throw ValidationException::withMessages(['type' => 'This content type cannot be saved.']);
        }

        $query = $model::query()->whereKey($id);
        if (in_array($model, [Career::class, Resource::class, Multimedia::class], true)) {
            $query->where('is_published', true);
        } elseif ($model === SuccessStory::class) {
            $query->where('status', 'approved');
        }

        return $query->firstOrFail();
    }

    public function typeFor(Model $model): string
    {
        return array_search($model::class, self::TYPES, true) ?: 'unknown';
    }

    public function classFor(string $type): ?string
    {
        return self::TYPES[$type] ?? null;
    }
}

<?php

namespace App\Attributes;

use Attribute;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

#[Attribute(Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
class Filter
{
    private static ?Builder $query = null;

    public function __construct(
        private string $model,
        private string $operator,
        private array|string $accepted
    ) {
        if (!(
            class_exists($this->model) &&
            new $this->model instanceof Model
        )) {
            throw new Exception('Model class "'.$this->model.'" not found.');
        }
    }

    public function setup($request): void
    {
        if (!self::$query) {
            self::$query = $this->model::query();
        }

        $param = collect($request->query)->only($this->accepted);

        self::$query = match($this->operator) {
            'and' => self::$query->where($param->toArray()),
            'or' => self::$query->orWhere($param->toArray()),
            'like' => $this->whereLike($param, 'and'),
            'like:or' => $this->whereLike($param, 'or'),
            'with' => self::$query->with($this->accepted),
        };
    }

    public static function get(): ?Collection
    {
        return self::$query?->get();
    }

    private function whereLike($query, $boolean): Builder
    {
        return self::$query->where(
            array_values($query->map(fn ($value, $name) => [$name, 'like', $value, $boolean])->all())
        );
    }
}

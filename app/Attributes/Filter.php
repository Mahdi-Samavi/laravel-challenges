<?php

namespace App\Attributes;

use Attribute;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

#[Attribute(Attribute::TARGET_METHOD)]
class Filter
{
    public function __construct(
        private string $model,
        private array $accepted
    ) {
    }

    public function run($request): Collection
    {
        if (!(
            class_exists($this->model) &&
            new $this->model instanceof Model
        )) {
            throw new Exception('Model class "'.$this->model.'" not found.');
        }

        return $this->model::where(
            collect($request->query)->only($this->accepted)->toArray()
        )->get();
    }
}

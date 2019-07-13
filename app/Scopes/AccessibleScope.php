<?php

namespace App\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class AccessibleScope implements Scope
{
    protected $relation;

    /**
     * AccessibleScope constructor.
     * @param $relation
     */
    public function __construct($relation)
    {
        $this->relation = $relation;
    }

    /**
     * Apply the scope to a given Eloquent query builder.
     *
     * @param  \Illuminate\Database\Eloquent\Builder $builder
     * @param  \Illuminate\Database\Eloquent\Model $model
     * @return void
     */
    public function apply(Builder $builder, Model $model)
    {
        $builder->whereHas($this->relation, function (Builder $builder) {
            $builder->public();
        });
    }

}
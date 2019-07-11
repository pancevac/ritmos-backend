<?php

namespace App;


use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class ActivatedOwnerScope implements Scope
{
    /**
     * Relation name.
     *
     * @var string
     */
    protected $relation;

    /**
     * ActivatedOwnerScope constructor.
     * @param string $relation
     */
    public function __construct(string $relation)
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
            $builder->activated();
        });
    }
}
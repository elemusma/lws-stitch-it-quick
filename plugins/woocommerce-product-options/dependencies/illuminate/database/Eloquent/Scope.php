<?php

namespace Barn2\Plugin\WC_Product_Options\Dependencies\Illuminate\Database\Eloquent;

/** @internal */
interface Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return void
     */
    public function apply(Builder $builder, Model $model);
}

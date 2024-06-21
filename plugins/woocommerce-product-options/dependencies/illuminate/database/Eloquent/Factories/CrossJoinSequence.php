<?php

namespace Barn2\Plugin\WC_Product_Options\Dependencies\Illuminate\Database\Eloquent\Factories;

use Barn2\Plugin\WC_Product_Options\Dependencies\Illuminate\Support\Arr;
/** @internal */
class CrossJoinSequence extends Sequence
{
    /**
     * Create a new cross join sequence instance.
     *
     * @param  array  $sequences
     * @return void
     */
    public function __construct(...$sequences)
    {
        $crossJoined = \array_map(function ($a) {
            return \array_merge(...$a);
        }, Arr::crossJoin(...$sequences));
        parent::__construct(...$crossJoined);
    }
}

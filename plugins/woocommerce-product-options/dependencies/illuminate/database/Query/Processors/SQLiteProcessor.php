<?php

namespace Barn2\Plugin\WC_Product_Options\Dependencies\Illuminate\Database\Query\Processors;

/** @internal */
class SQLiteProcessor extends Processor
{
    /**
     * Process the results of a column listing query.
     *
     * @param  array  $results
     * @return array
     */
    public function processColumnListing($results)
    {
        return \array_map(function ($result) {
            return ((object) $result)->name;
        }, $results);
    }
}

<?php

namespace Barn2\Plugin\WC_Product_Options\Dependencies\Illuminate\Support;

use Barn2\Plugin\WC_Product_Options\Dependencies\Carbon\Carbon as BaseCarbon;
use Barn2\Plugin\WC_Product_Options\Dependencies\Carbon\CarbonImmutable as BaseCarbonImmutable;
/** @internal */
class Carbon extends BaseCarbon
{
    /**
     * {@inheritdoc}
     */
    public static function setTestNow($testNow = null)
    {
        BaseCarbon::setTestNow($testNow);
        BaseCarbonImmutable::setTestNow($testNow);
    }
}

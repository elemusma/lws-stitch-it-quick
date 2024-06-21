<?php

namespace Barn2\Plugin\WC_Product_Options\Dependencies\Illuminate\Contracts\Support;

/** @internal */
interface MessageProvider
{
    /**
     * Get the messages for the instance.
     *
     * @return \Illuminate\Contracts\Support\MessageBag
     */
    public function getMessageBag();
}

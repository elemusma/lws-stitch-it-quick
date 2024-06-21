<?php

namespace Barn2\Plugin\WC_Product_Options\Dependencies\Illuminate\Database\Eloquent\Casts;

use Barn2\Plugin\WC_Product_Options\Dependencies\Illuminate\Contracts\Database\Eloquent\Castable;
use Barn2\Plugin\WC_Product_Options\Dependencies\Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Barn2\Plugin\WC_Product_Options\Dependencies\Illuminate\Support\Facades\Crypt;
/** @internal */
class AsEncryptedArrayObject implements Castable
{
    /**
     * Get the caster class to use when casting from / to this cast target.
     *
     * @param  array  $arguments
     * @return object|string
     */
    public static function castUsing(array $arguments)
    {
        return new class implements CastsAttributes
        {
            public function get($model, $key, $value, $attributes)
            {
                if (isset($attributes[$key])) {
                    return new ArrayObject(\json_decode(Crypt::decryptString($attributes[$key]), \true));
                }
                return null;
            }
            public function set($model, $key, $value, $attributes)
            {
                if (!\is_null($value)) {
                    return [$key => Crypt::encryptString(\json_encode($value))];
                }
                return null;
            }
            public function serialize($model, string $key, $value, array $attributes)
            {
                return !\is_null($value) ? $value->getArrayCopy() : null;
            }
        };
    }
}

<?php

// phpcs:ignore WordPress.Files.FileName
/**
 * Aliases helper methods.
 *
 * @package   Sematico\fluent-query
 * @author    Alessandro Tesoro <alessandro.tesoro@icloud.com>
 * @copyright Alessandro Tesoro
 * @license   MIT
 */
namespace Barn2\Plugin\WC_Product_Options\Dependencies\Sematico\FluentQuery\Concerns;

use Barn2\Plugin\WC_Product_Options\Dependencies\Illuminate\Support\Arr;
/** @internal */
trait HasAliases
{
    /**
     * @return array
     */
    public static function getAliases()
    {
        if (isset(parent::$aliases) && \count(parent::$aliases)) {
            return \array_merge(parent::$aliases, static::$aliases);
        }
        return static::$aliases;
    }
    /**
     * @param string $new
     * @param string $old
     */
    public static function addAlias($new, $old)
    {
        static::$aliases[$new] = $old;
    }
    /**
     * @param string $key
     * @return mixed
     */
    public function getAttribute($key)
    {
        $value = parent::getAttribute($key);
        if ($value === null && \count(static::getAliases())) {
            if ($value = Arr::get(static::getAliases(), $key)) {
                if (\is_array($value)) {
                    $meta = Arr::get($value, 'meta');
                    return $meta ? $this->meta->{$meta} : null;
                }
                return parent::getAttribute($value);
            }
        }
        return $value;
    }
    /**
     * Get alias value from mutator or directly from attribute
     *
     * @param  string $key
     * @param  mixed  $value
     * @return mixed
     */
    public function mutateAttribute($key, $value)
    {
        if ($this->hasGetMutator($key)) {
            return parent::mutateAttribute($key, $value);
        }
        return $this->getAttribute($key);
    }
}
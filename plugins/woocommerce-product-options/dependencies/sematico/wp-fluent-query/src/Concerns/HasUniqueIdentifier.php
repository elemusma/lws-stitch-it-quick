<?php

// phpcs:ignore WordPress.Files.FileName
/**
 * ID helper methods.
 *
 * @package   Sematico\fluent-query
 * @author    Alessandro Tesoro <alessandro.tesoro@icloud.com>
 * @copyright Alessandro Tesoro
 * @license   MIT
 */
namespace Barn2\Plugin\WC_Product_Options\Dependencies\Sematico\FluentQuery\Concerns;

/**
 * ID helper methods
 * @internal
 */
trait HasUniqueIdentifier
{
    /**
     * Get model id.
     *
     * @return int
     */
    public function getID()
    {
        return (int) $this->attributes[$this->primaryKey];
    }
}

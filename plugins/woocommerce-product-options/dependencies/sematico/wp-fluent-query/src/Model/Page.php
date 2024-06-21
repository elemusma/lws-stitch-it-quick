<?php

// phpcs:ignore WordPress.Files.FileName
/**
 * Page model.
 *
 * @package   Sematico\fluent-query
 * @author    Alessandro Tesoro <alessandro.tesoro@icloud.com>
 * @copyright Alessandro Tesoro
 * @license   MIT
 */
namespace Barn2\Plugin\WC_Product_Options\Dependencies\Sematico\FluentQuery\Model;

use Barn2\Plugin\WC_Product_Options\Dependencies\Sematico\FluentQuery\Scope\PageScope;
/**
 * WordPress page model.
 * @internal
 */
class Page extends Post
{
    /**
     * Automatically adjust the query to load pages.
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();
        static::addGlobalScope(new PageScope());
    }
}

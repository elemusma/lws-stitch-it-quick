<?php

// phpcs:ignore WordPress.Files.FileName
/**
 * Term model.
 *
 * @package   Sematico\fluent-query
 * @author    Alessandro Tesoro <alessandro.tesoro@icloud.com>
 * @copyright Alessandro Tesoro
 * @license   MIT
 */
namespace Barn2\Plugin\WC_Product_Options\Dependencies\Sematico\FluentQuery\Model;

use Barn2\Plugin\WC_Product_Options\Dependencies\Illuminate\Database\Eloquent\Model;
use Barn2\Plugin\WC_Product_Options\Dependencies\Sematico\FluentQuery\Concerns\HasMetaFields;
use Barn2\Plugin\WC_Product_Options\Dependencies\Sematico\FluentQuery\Concerns\HasUniqueIdentifier;
/**
 * WordPress term model.
 * @internal
 */
class Term extends Model
{
    use HasMetaFields;
    use HasUniqueIdentifier;
    /**
     * @var string
     */
    protected $table = 'terms';
    /**
     * @var string
     */
    protected $primaryKey = 'term_id';
    /**
     * @var bool
     */
    public $timestamps = \false;
    /**
     * @return HasOne
     */
    public function taxonomy()
    {
        return $this->hasOne(TermTaxonomy::class, 'term_id');
    }
}

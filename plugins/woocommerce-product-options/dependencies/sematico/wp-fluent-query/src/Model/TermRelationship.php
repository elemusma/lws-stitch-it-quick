<?php

// phpcs:ignore WordPress.Files.FileName
/**
 * Term relationship model.
 *
 * @package   Sematico\fluent-query
 * @author    Alessandro Tesoro <alessandro.tesoro@icloud.com>
 * @copyright Alessandro Tesoro
 * @license   MIT
 */
namespace Barn2\Plugin\WC_Product_Options\Dependencies\Sematico\FluentQuery\Model;

use Barn2\Plugin\WC_Product_Options\Dependencies\Illuminate\Database\Eloquent\Model;
/**
 * Term relationship taxonomy model.
 * @internal
 */
class TermRelationship extends Model
{
    /**
     * @var string
     */
    protected $table = 'term_relationships';
    /**
     * @var array
     */
    protected $primaryKey = ['object_id', 'term_taxonomy_id'];
    /**
     * @var bool
     */
    public $timestamps = \false;
    /**
     * @var bool
     */
    public $incrementing = \false;
    /**
     * @return BelongsTo
     */
    public function post()
    {
        return $this->belongsTo(Post::class, 'object_id');
    }
    /**
     * @return BelongsTo
     */
    public function taxonomy()
    {
        return $this->belongsTo(TermTaxonomy::class, 'term_taxonomy_id');
    }
}

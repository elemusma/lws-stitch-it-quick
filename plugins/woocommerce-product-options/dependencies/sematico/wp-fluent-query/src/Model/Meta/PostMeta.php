<?php

// phpcs:ignore WordPress.Files.FileName
/**
 * Metadata class.
 *
 * @package   Sematico\fluent-query
 * @author    Alessandro Tesoro <alessandro.tesoro@icloud.com>
 * @copyright Alessandro Tesoro
 * @license   MIT
 */
namespace Barn2\Plugin\WC_Product_Options\Dependencies\Sematico\FluentQuery\Model\Meta;

use Barn2\Plugin\WC_Product_Options\Dependencies\Illuminate\Database\Eloquent\Relations\BelongsTo;
use Barn2\Plugin\WC_Product_Options\Dependencies\Sematico\FluentQuery\Model\Post;
/**
 * Post metadata model.
 * @internal
 */
class PostMeta extends BaseMeta
{
    /**
     * @var string
     */
    protected $table = 'postmeta';
    /**
     * @var array
     */
    protected $fillable = ['meta_key', 'meta_value', 'post_id'];
    /**
     * @return BelongsTo
     */
    public function post()
    {
        return $this->belongsTo(Post::class, 'post_id');
    }
}

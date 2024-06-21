<?php

/**
 * This file is part of the Carbon package.
 *
 * (c) Brian Nesbitt <brian@nesbot.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Barn2\Plugin\WC_Product_Options\Dependencies\Carbon;

use Barn2\Plugin\WC_Product_Options\Dependencies\Symfony\Component\Translation\MessageCatalogueInterface;
/**
 * Mark translator using strong type from symfony/translation >= 6.
 * @internal
 */
interface TranslatorStrongTypeInterface
{
    public function getFromCatalogue(MessageCatalogueInterface $catalogue, string $id, string $domain = 'messages');
}
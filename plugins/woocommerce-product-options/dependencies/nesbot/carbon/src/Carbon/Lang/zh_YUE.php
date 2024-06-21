<?php

namespace Barn2\Plugin\WC_Product_Options\Dependencies;

/**
 * This file is part of the Carbon package.
 *
 * (c) Brian Nesbitt <brian@nesbot.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
/*
 * Authors:
 * - IBM Globalization Center of Competency, Yamato Software Laboratory    bug-glibc-locales@gnu.org
 */
return \array_replace_recursive(require __DIR__ . '/zh.php', ['formats' => ['L' => 'YYYY-MM-DD']]);
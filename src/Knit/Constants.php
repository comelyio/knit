<?php
/**
 * This file is part of Knit package.
 * https://github.com/comelyio/knit
 *
 * Copyright (c) Furqan Ahmed Siddiqui
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code or visit following link:
 * https://github.com/comelyio/knit/blob/master/LICENSE
 */

declare(strict_types=1);

namespace Comely\Knit;

/**
 * Interface Constants
 * @package Comely\Knit
 */
interface Constants
{
    /** string Version (Major.Minor.Release) */
    const VERSION   =   "1.0.0";
    /** int Version (Major * 10000 + Minor * 100 + Release) */
    const VERSION_ID    =   10000;

    const DS    =   DIRECTORY_SEPARATOR;
    const EOL   =   PHP_EOL;

    const FILES =   ["knit", "tpl"];
    const DELIMITERS    =   ["{", "}"];

    const CACHE_STATIC  =   2;
    const CACHE_DYNAMIC =   4;
}
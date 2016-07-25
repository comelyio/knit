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

namespace Comely\Knit\Modifiers;

use Comely\Knit\AbstractModifier;

/**
 * Class Htmlentities
 * @package Comely\Knit\Modifiers
 */
class Htmlentities extends AbstractModifier
{
    const CALL  =   "htmlentities";
    const MIN_ARGS  =   0;
    const MAX_ARGS  =   3;
    const ARGS  =   [
        ["int", ENT_COMPAT|ENT_HTML5],
        ["str", "UTF-8"],
        ["bool", true]
    ];
}
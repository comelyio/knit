<?php
/**
 * This file is part of Knit package.
 * https://github.com/comelyio/knit
 *
 *  Copyright (c) 2017 Furqan A. Siddiqui <hello@furqansiddiqui.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code or visit following link:
 * https://github.com/comelyio/knit/blob/master/LICENSE
 */

declare(strict_types=1);

namespace Comely\Knit\Modifiers;

use Comely\Knit\AbstractModifier;

/**
 * Class NumberFormat
 * @package Comely\Knit\Modifiers
 */
class NumberFormat extends AbstractModifier
{
    const CALL  =   "number_format";
    const MIN_ARGS  =   1;
    const MAX_ARGS  =   3;
    const ARGS  =   [
        ["int", 0],
        ["str", "."],
        ["str", ","]
    ];
}
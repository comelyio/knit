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
 * Class Basename
 * @package Comely\Knit\Modifiers
 */
class Basename extends AbstractModifier
{
    const CALL  =   "basename";
    const MIN_ARGS  =   0;
    const MAX_ARGS  =   0;
    const ARGS  =   [];
}
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
 * Class Capitalize
 * @package Comely\Knit\Modifiers
 */
class Capitalize extends AbstractModifier
{
    const CALL  =   null;
    const MIN_ARGS  =   0;
    const MAX_ARGS  =   1;
    const ARGS  =   [
        ["bool", false]
    ];

    /**
     * @param string $input
     * @param array $args
     * @return string
     */
    public function apply(string $input, array $args) : string
    {
        $args   =   $this->assertArgs($args);
        $call   =   $args[0]    === true ? "ucwords" : "ucfirst";
        return sprintf('%s(%s)', $call, $input);
    }
}
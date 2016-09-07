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
 * Class Translate
 * @package Comely\Knit\Modifiers
 */
class Translate extends AbstractModifier
{
    const CALL  =   "__";
    const MIN_ARGS  =   1;
    const MAX_ARGS  =   1;
    const ARGS  =   [
        ["str"]
    ];

    /**
     * @param string $input
     * @param array $args
     * @return string
     * @throws \Comely\KnitException
     */
    public function apply(string $input, array $args) : string
    {
        $args   =   $this->assertArgs($args);
        return sprintf("%s('%s')", self::CALL, $args[0]);
    }
}
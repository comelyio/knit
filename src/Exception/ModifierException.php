<?php
/**
 * This file is part of Knit package.
 * https://github.com/comelyio/knit
 *
 *  Copyright (c) 2018 Furqan A. Siddiqui <hello@furqansiddiqui.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code or visit following link:
 * https://github.com/comelyio/knit/blob/master/LICENSE
 */

declare(strict_types=1);

namespace Comely\Knit\Exception;

/**
 * Class ModifierException
 * @package Comely\Knit\Exception
 */
class ModifierException extends ParseException
{
    /**
     * @param string $var
     * @param string $modifier
     * @param int $argument
     * @param string $expected
     * @param string $given
     * @return ModifierException
     */
    public static function TypeError(string $var, string $modifier, int $argument, string $expected, string $given): self
    {
        return new self(
            sprintf(
                'Modifier "%s" used near "%s" requires argument %d to be of type "%s", given "%s"',
                $modifier,
                $var,
                $argument,
                $expected,
                $given
            )
        );
    }
}
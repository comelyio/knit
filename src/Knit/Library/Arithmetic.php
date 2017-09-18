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

namespace Comely\Knit\Library;

/**
 * Class Arithmetic
 * @package Comely\Knit\Library
 */
class Arithmetic
{
    /**
     * @param $val1
     * @param $val2
     * @return mixed
     */
    public static function add($val1, $val2)
    {
        $val1   =   static::assertVal($val1);
        $val2   =   static::assertVal($val2);
        return $val1+$val2;
    }

    /**
     * @param $val1
     * @param $val2
     * @return mixed
     */
    public static function subtract($val1, $val2)
    {
        $val1   =   static::assertVal($val1);
        $val2   =   static::assertVal($val2);
        return $val1-$val2;
    }

    /**
     * @param $val1
     * @param $val2
     * @return mixed
     */
    public static function multiply($val1, $val2)
    {
        $val1   =   static::assertVal($val1);
        $val2   =   static::assertVal($val2);
        return $val1*$val2;
    }

    /**
     * @param $val1
     * @param $val2
     * @return mixed
     */
    public static function divide($val1, $val2)
    {
        $val1   =   static::assertVal($val1);
        $val2   =   static::assertVal($val2);
        return $val1/$val2;
    }

    /**
     * @param $val
     * @return float|int
     */
    private static function assertVal($val)
    {
        // Convert to type float if value is not integer
        if(!is_int($val)    &&  !is_float($val)) {
            return (float) $val;
        }

        return $val;
    }
}
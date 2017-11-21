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

namespace Comely\Knit;

use Comely\KnitException;

/**
 * Class AbstractModifier
 * @package Knit
 */
abstract class AbstractModifier
{
    const CALL  =   "";
    const MIN_ARGS  =   0;
    const MAX_ARGS  =   0;
    const ARGS  =   [];

    /**
     * @param string $input
     * @param array $args
     * @return string
     * @throws KnitException
     */
    public function apply(string $input, array $args) : string
    {
        $args   =   $this->assertArgs($args);
        return sprintf('%s(%s%s)', static::CALL, $input, $this->buildArgsString($args));
    }

    /**
     * @param array $args
     * @return array
     * @throws KnitException
     */
    protected function assertArgs(array $args) : array
    {
        // Count passed argument
        $numArgs    =   count($args);

        // Minimum number of arguments
        if(static::MIN_ARGS   >   $numArgs) {
            throw KnitException::parseError(
                sprintf('expects minimum of %d args, %d passed', static::MIN_ARGS, $numArgs)
            );
        }

        // Maximum number of arguments
        if(static::MAX_ARGS   <   $numArgs) {
            throw KnitException::parseError(
                sprintf('expects maximum of %d args, %d passed', static::MAX_ARGS, $numArgs)
            );
        }

        // Typecast
        $asserted   =   [];
        foreach(static::ARGS as $index => $arg) {
            // Check if arg. was passed
            if(!array_key_exists($index, $args)) {
                // Not passed, check if default value is available
                if(!array_key_exists(1, $arg)) {
                    // No default value, throw exception
                    throw KnitException::parseError(
                        sprintf('missing argument %d', ($index+1))
                    );
                } else {
                    // Set default value
                    $value  =   $arg[1];
                }
            } else {
                // Passed value
                $value  =   $args[$index];
            }

            // Check if casts match
            $valueType  =   $this->getType($value);
            if($arg[0]  !== "~") {
                if($valueType   === "str"   &&  preg_match('/^\$[a-z\_][a-z0-9\_\[\]\"\'\-\>]+$/i', $value)) {
                    // Variable, ...do nothing?
                } else {
                    $value  =   call_user_func($arg[0] . "val", $value); // Cast
                }
            }

            $asserted[] =   $value;
        }

        // Return args
        return $asserted;
    }

    /**
     * @param array $args
     * @return string
     */
    protected function buildArgsString(array $args) : string
    {
        $args   =   array_map(function($arg) {
            switch(gettype($arg)) {
                case "NULL":
                    return "null";
                case "boolean":
                    return $arg ? "true" : "false";
                case "integer":
                    return $arg;
                case "double":
                    return $arg;
                default:
                    if(preg_match('/^\$[a-z\_][a-z0-9\_\[\]\"\'\-\>]+$/i', $arg)) {
                        return $arg;
                    } else {
                        return sprintf("'%s'", $arg);
                    }
            }
        }, $args);
        return count($args) >   0 ? ", " . implode(", ", $args) : "";
    }

    /**
     * @param $val
     * @return string
     */
    private function getType($val) : string
    {
        switch(gettype($val)) {
            case "boolean":
                return "bool";
            case "integer":
                return "int";
            case "double":
                return "float";
            case "string":
                return "str";
            default:
                return "str";
        }
    }
}
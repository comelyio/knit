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

namespace Comely\Knit;

use Comely\Knit\Exception\KnitException;
use Comely\Knit\Exception\ModifierException;

/**
 * Class Modifiers
 * @package Comely\Knit
 */
class Modifiers
{
    /** @var array */
    private $modifiers;

    /**
     * Modifiers constructor.
     */
    public function __construct()
    {
        $this->modifiers = [];
        $this->registerCoreModifiers();
    }

    /**
     * @throws KnitException
     */
    public function registerCoreModifiers(): void
    {
        $this->register("isset");
        $this->register("var_dump");
        $this->register("count");

        // Date
        $this->register("date", function (string $var, array $args) {
            $format = $args[0] ?? null;
            if (!is_string($format)) {
                throw ModifierException::TypeError($var, "date", 1, "string", gettype($format));
            }

            return sprintf('date(%s, %s)', $format, $var);
        });
    }

    /**
     * @throws KnitException
     */
    public function registerTranslationModifiers(): void
    {
        // Translate
        $this->register("translate", function (string $var, array $args) {
            // Translation by $knit var
            if (preg_match('/^\$this\-\>data\[(\"|\')knit(\"|\')\]$/', $var)) {
                $translatable = $args[0] ?? null;
                if (!is_string($translatable)) {
                    throw ModifierException::TypeError($var, "translate", 1, "string", gettype($translatable));
                }

                $dynamicKey = $args[1] ?? null;
                if (!$dynamicKey) {
                    return sprintf("__(%s)", $translatable);
                }

                if (!is_string($dynamicKey)) {
                    throw ModifierException::TypeError($var, "translate", 2, "string", gettype($dynamicKey));
                }

                return sprintf("__(sprintf(%s, %s))", $translatable, $dynamicKey);
            }

            // Direct variable translation
            $dynamicKey = $args[0] ?? null;
            if (!$dynamicKey) {
                return sprintf("__(%s)", $var);
            }

            if (!is_string($dynamicKey)) {
                throw ModifierException::TypeError($var, "translate", 1, "string", gettype($dynamicKey));
            }

            return sprintf("__(sprintf(%s, %s))", $var, $dynamicKey);
        });
    }

    /**
     * @throws KnitException
     */
    public function registerDefaultModifiers(): void
    {
        $this->register("trim");
        $this->register("strtolower");
        $this->register("strtoupper");
        $this->register("is_array");
        $this->register("is_string");
        $this->register("is_bool");
        $this->register("is_float");
        $this->register("is_int");
        $this->register("is_null");
        $this->register("strip_tags");
        $this->register("nl2br");
        $this->register("ucfirst");
        $this->register("ucwords");
        $this->register("basename");
        $this->register("addslashes");
        $this->register("strlen");
        $this->register("urlencode");
        $this->register("urldecode");

        // Round
        $this->register("round", function (string $var, array $args) {
            $precision = $args[0] ?? null;
            if (!is_int($precision)) {
                throw ModifierException::TypeError($var, "round", 1, "int", gettype($precision));
            }

            return sprintf('round(%s, %d)', $var, $precision);
        });

        // Number Format
        $this->register("number_format", function (string $var, array $args) {
            $decimals = $args[0] ?? null;
            if (!is_int($decimals)) {
                if (!is_string($decimals) || !preg_match('/^\$.*/', $decimals)) {
                    throw ModifierException::TypeError($var, "number_format", 1, "int", gettype($decimals));
                }
            }

            $decimalPoint = $args[1] ?? '.';
            if (!is_string($decimalPoint)) {
                throw ModifierException::TypeError($var, "number_format", 2, "string", gettype($decimalPoint));
            }

            $thousandsSep = $args[2] ?? ',';
            if (!is_string($thousandsSep)) {
                throw ModifierException::TypeError($var, "number_format", 3, "string", gettype($thousandsSep));
            }

            return sprintf('number_format(%s, %d, %s, %s)', $var, $decimals, $decimalPoint, $thousandsSep);
        });

        // Substr
        $this->register("substr", function (string $var, array $args) {
            $start = $args[0] ?? null;
            if (!is_int($start)) {
                throw ModifierException::TypeError($var, "substr", 1, "int", gettype($start));
            }

            if (array_key_exists(1, $args)) {
                $length = $args[1] ?? null;
                throw ModifierException::TypeError($var, "substr", 2, "int", gettype($length));
            }

            if (isset($length)) {
                return sprintf('substr(%s, %d, %d)', $var, $start, $length);
            } else {
                return sprintf('substr(%s, %d)', $var, $start);
            }
        });
    }

    /**
     * @param string $name
     * @param \Closure $closure
     * @throws KnitException
     */
    public function register(string $name, ?\Closure $closure = null): void
    {
        $name = strtolower($name);
        if (!preg_match('/^[a-z0-9\_]+$/', $name)) {
            throw new KnitException('Cannot register modifier, invalid name');
        }

        if (!$closure) {
            $this->register($name, function (string $var) use ($name) {
                return sprintf('%s(%s)', $name, $var);
            });
            return;
        }

        $this->modifiers[$name] = $closure;
    }

    /**
     * @param string $name
     * @return \Closure|null
     */
    public function get(string $name): ?\Closure
    {
        $name = strtolower($name);
        return $this->modifiers[$name] ?? null;
    }
}
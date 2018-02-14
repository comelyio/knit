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

        // Round
        $this->register("round", function (string $var, array $args) {
            $precision = $args[0] ?? null;
            if (!is_int($precision)) {
                throw ModifierException::TypeError($var, "round", 1, "int", gettype($precision));
            }

            return sprintf('round(%s, %d)', $var, $precision);
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
            $closure = function (string $var) use ($name) {
                return sprintf('%s(%s)', $name, $var);
            };
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
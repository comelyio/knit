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
     * @param string $name
     * @param \Closure $closure
     * @throws KnitException
     */
    public function register(string $name, \Closure $closure): void
    {
        if (!preg_match('/^[a-zA-Z0-9\_]+$/', $name)) {
            throw new KnitException('Cannot register modifier, invalid name');
        }

        $this->modifiers[$name] = $closure;
    }
}
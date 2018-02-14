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

namespace Comely\Knit\Compiler\Parser;

use Comely\Knit\Exception\ParseException;

/**
 * Class Variables
 * @package Comely\Knit\Compiler\Parser
 */
class Variables
{
    /** @var array */
    private $vars;

    /**
     * Variables constructor.
     */
    public function __construct()
    {
        $this->vars = [];
    }

    /**
     * @param string $var
     */
    public function add(string $var): void
    {
        if (in_array(strtolower($var), ["this", "knit"])) {
            throw new ParseException(sprintf('Variable "%s" cannot be reserved', $var));
        }

        $this->vars[] = $var;
    }

    /**
     * @param string $var
     */
    public function delete(string $var): void
    {
        $index = array_search($var, $this->vars);
        if ($index !== false) {
            unset($this->vars[$index]);
        }
    }

    /**
     * @param string $var
     * @return bool
     */
    public function has(string $var): bool
    {
        return in_array($var, $this->vars);
    }
}
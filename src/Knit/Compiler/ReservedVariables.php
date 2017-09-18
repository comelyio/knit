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

namespace Comely\Knit\Compiler;

/**
 * Class ReservedVariables
 * @package Comely\Knit\Compiler
 */
class ReservedVariables
{
    /** @var array */
    private $vars   =   [];

    /**
     * @param string $var
     * @return bool
     */
    public function has(string $var) : bool
    {
        return in_array($var, $this->vars);
    }

    /**
     * @param string $var
     * @return ReservedVariables
     */
    public function add(string $var) : self
    {
        array_push($this->vars, $var);
        return $this;
    }

    /**
     * @param string $var
     * @return ReservedVariables
     */
    public function delete(string $var) : self
    {
        $key    =   array_search($var, $this->vars);
        if($key !== false) {
            unset($this->vars[$key]);
            $this->vars =   array_values($this->vars); // Reindex?
        }

        return $this;
    }
}
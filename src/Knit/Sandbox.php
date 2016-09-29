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

namespace Comely\Knit;

use Comely\Knit;
use Comely\KnitException;

/**
 * Class Sandbox
 * @package Comely\Knit
 */
class Sandbox
{
    /** @var array */
    private $data;
    /** @var string */
    private $output;
    /** @var array */
    private $timers;

    /**
     * Sandbox constructor.
     * @param string $knitCompiledPhp
     * @param array $data
     * @throws KnitException
     */
    public function __construct(string $knitCompiledPhp, array $data = [])
    {
        $this->data =   $data;

        $startTimer =   microtime(true);
        ob_start();
        include $knitCompiledPhp;
        $this->output =   ob_get_contents();
        ob_end_clean();

        if(
            !defined("COMELY_KNIT")  ||
            !defined("COMELY_KNIT_PARSE_TIMER") ||
            !defined("COMELY_KNIT_COMPILED_ON")
        ) {
            throw KnitException::sandBoxError("Bad or incomplete Knit compiled script");
        }

        if(!is_float(COMELY_KNIT_PARSE_TIMER)   ||  !is_float(COMELY_KNIT_COMPILED_ON)) {
            throw KnitException::sandBoxError("Compiled PHP script is missing timestamps");
        }

        $this->timers   =   [
            COMELY_KNIT_PARSE_TIMER,
            COMELY_KNIT_COMPILED_ON,
            microtime(true)-$startTimer
        ];
    }

    /**
     * Prepare for serializing
     * @return array
     */
    public function __sleep()
    {
        $this->output   =   base64_encode($this->output);
        return ["output","timers"];
    }

    /**
     * Unserialize
     */
    public function __wakeup()
    {
        $this->output   =   base64_decode($this->output);
        $this->data =   [];
    }

    /**
     * @param float $timer
     * @return Sandbox
     */
    public function feedTimer(float $timer)  : self
    {
        $this->timers[2]    =   $timer;
        return $this;
    }

    /**
     * Gets the time it took compiler to parse template into PHP script
     * @return float
     */
    public function getParseTimer() : float
    {
        return $this->timers[0];
    }

    /**
     * Gets timestamp of when this compiled script was generated
     * @return float
     */
    public function getCompiledOn() : float
    {
        return $this->timers[1];
    }

    /**
     * Gets timer of sandbox + parse timer
     * @return float
     */
    public function getTimer() : float
    {
        return $this->timers[2] + $this->timers[0];
    }

    /**
     * Get template
     * @return string
     */
    public function getOutput() : string
    {
        return trim($this->output);
    }
}
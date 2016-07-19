<?php
declare(strict_types=1);

namespace Comely\Knit\Compiler;

use Comely\Knit;
use Comely\Knit\Compiler;

/**
 * Class Template
 * @package Comely\Knit\Compiler
 */
class Template
{
    private $file;
    private $parsed;
    private $timer;
    private $timerStart;

    /**
     * Template constructor.
     * @param Compiler $compiler
     * @param string $file
     */
    public function __construct(Compiler $compiler, string $file)
    {
        $this->file =   $file;
        $this->timer    =   0;
        $this->timerStart   =   microtime(true);
        $this->parsed   =   $this->parse(
            $compiler->read($file)
        );
    }

    /**
     * @return string
     */
    public function getFile() : string
    {
        return $this->file;
    }

    /**
     * @return string
     */
    public function getParsed() : string
    {
        return $this->parsed;
    }

    /**
     * @return float
     */
    public function getTimer() : float
    {
        return $this->timer;
    }

    /**
     * @param string $contents
     * @return string
     */
    private function parse(string $contents) : string
    {
        // Parse line by line
        $line   =   0;
        $lines  =   preg_split("/[\r\n]+/", $contents);
        var_dump($lines);
    }

    private function parseLine() : string
    {

    }
}
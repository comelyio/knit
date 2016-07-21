<?php
declare(strict_types=1);

namespace Comely\Knit\Compiler;

use Comely\Knit;
use Comely\Knit\Compiler;

/**
 * Class Template
 * @package Comely\Knit\Compiler
 */
class Template extends AbstractParser
{
    protected $compiler;
    protected $delimiters;
    protected $file;
    protected $parsed;
    protected $source;
    protected $timer;

    /**
     * Template constructor.
     * @param Compiler $knit
     * @param string $file
     */
    public function __construct(Compiler $knit, string $file)
    {
        $knit->checkPaths(__METHOD__); // Verifies that proper paths are set

        $this->compiler =   $knit;
        $this->delimiters   =   $knit->getDelimiters();
        $this->file =   $file;
        $this->parsed   =   "";
        $this->source   =   $knit->read($file);
        $this->timer    =   0;

        parent::__construct();
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
}
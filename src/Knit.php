<?php
declare(strict_types=1);

namespace Comely;

use Comely\Knit\Compiler;
use Comely\Knit\Constants;
use Comely\Knit\Data;

/**
 * Class Knit
 * @package Comely
 */
class Knit extends Compiler implements Constants
{
    /**
     * Get prepared template
     * Compile a template as PHP file, execute it, and return prepared template
     *
     * @param string $file
     * @return string
     * @throws KnitException
     */
    public function prepare(string $file) : string
    {
        // Make sure necessary Disk instances are setup
        parent::checkPaths(__METHOD__);

        // Cache

        // Fresh Compile
        $parsed   =   parent::compile($file);
        
        return $parsed;
    }
}
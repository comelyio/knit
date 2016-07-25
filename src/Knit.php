<?php
declare(strict_types=1);

namespace Comely;

use Comely\Knit\Compiler;
use Comely\Knit\Constants;
use Comely\Knit\Sandbox;

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
     * @param int $ttl
     * @return Sandbox
     * @throws KnitException
     */
    public function prepare(string $file , int $ttl = 0) : Sandbox
    {
        // Make sure necessary Disk instances are setup
        parent::checkPaths(__METHOD__);

        // Knit filename
        $knittedFileName    =   $this->getKnittedFilename($file);
        
        // Cache
        if($ttl >   0) {
            $sandBox    =   $this->cacheRead($knittedFileName, $ttl);
            if($sandBox instanceof Sandbox) {
                return $sandBox;
            }
        }

        // Fresh Compile
        $compiled   =   parent::compile($file, $knittedFileName);
        $sandBox    =   $this->runSandbox($compiled, $this->data->getArray());

        // Write to cache
        if($ttl >   0) {
            $this->cacheWrite(clone $sandBox, basename($compiled), $knittedFileName);
        }

        // Delete compiled script
        $this->diskCompiler->delete(basename($compiled));

        // Return instanceof sandBox
        return $sandBox;
    }

    /**
     * @param string $file
     * @param int $ttl
     */
    public function print(string $file , int $ttl = 0)
    {
        print $this->prepare($file, $ttl)->getOutput();
    }
}
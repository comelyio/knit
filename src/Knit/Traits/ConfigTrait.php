<?php
declare(strict_types=1);

namespace Comely\Knit\Traits;

use Comely\IO\Filesystem\Disk;
use Comely\Knit;
use Comely\KnitException;

/**
 * Class ConfigTrait
 * @package Comely\Knit\Traits
 */
trait ConfigTrait
{
    private $diskTemplate;
    private $diskCompiler;
    private $diskCache;

    /**
     * @param string|Disk $path
     * @return Knit
     * @throws KnitException
     */
    public function setTemplatePath($path) : Knit
    {
        if(is_string($path)) {
            $disk   =   new Disk($path);
        } elseif(is_object($path)   &&  $path instanceof Disk) {
            $disk   =   $path;
        } else {
            throw KnitException::badPath(
                __METHOD__,
                "Provide a valid directory path or a Comely\\IO\\Filesystem\\Disk instance"
            );
        }

        if(strpos($disk->diskPrivileges(), "r") === false) {
            throw KnitException::badPath(
                __METHOD__,
                sprintf('Directory "%1$s" is not readable', $disk->getPath())
            );
        }

        $this->diskTemplate =   $disk;
        return $this;
    }

    /**
     * @param string|Disk $path
     * @return Knit
     * @throws KnitException
     */
    public function setCompilerPath($path) : Knit
    {
        if(is_string($path)) {
            $disk   =   new Disk($path);
        } elseif(is_object($path)   &&  $path instanceof Disk) {
            $disk   =   $path;
        } else {
            throw KnitException::badPath(
                __METHOD__,
                "Provide a valid directory path or a Comely\\IO\\Filesystem\\Disk instance"
            );
        }

        if($disk->diskPrivileges()  !== "rw") {
            throw KnitException::badPath(
                __METHOD__,
                sprintf('Directory "%1$s" must have read+write privileges', $disk->getPath())
            );
        }

        $this->diskCompiler =   $disk;
        return $this;
    }

    /**
     * @param string|Disk $path
     * @return Knit
     * @throws KnitException
     */
    public function setCachePath($path) : Knit
    {
        if(is_string($path)) {
            $disk   =   new Disk($path);
        } elseif(is_object($path)   &&  $path instanceof Disk) {
            $disk   =   $path;
        } else {
            throw KnitException::badPath(
                __METHOD__,
                "Provide a valid directory path or a Comely\\IO\\Filesystem\\Disk instance"
            );
        }

        if($disk->diskPrivileges()  !== "rw") {
            throw KnitException::badPath(
                __METHOD__,
                sprintf('Directory "%1$s" must have read+write privileges', $disk->getPath())
            );
        }

        $this->diskCache =   $disk;
        return $this;
    }

    /**
     * Makes sure that template and compiler paths are set
     * @param string $method
     * @throws KnitException
     */
    protected function checkPaths(string $method)
    {
        // Path to template files
        if(!isset($this->diskTemplate)) {
            throw KnitException::pathNotSet($method, "template", "setTemplatePath");
        }

        // Path to compiler's working directory
        if(!isset($this->diskCompiler)) {
            throw KnitException::pathNotSet($method, "compiler", "setCompilerPath");
        }
    }
}
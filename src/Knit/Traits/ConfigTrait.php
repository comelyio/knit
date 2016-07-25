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

namespace Comely\Knit\Traits;

use Comely\IO\Filesystem\Disk;
use Comely\IO\Session\ComelySession\Proxy;
use Comely\Knit;
use Comely\KnitException;

/**
 * Class ConfigTrait
 * @package Comely\Knit\Traits
 */
trait ConfigTrait
{
    protected $caching;
    protected $diskTemplate;
    protected $diskCompiler;
    protected $diskCache;
    protected $session;

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
     * @return string
     * @throws KnitException
     */
    public function getTemplatePath() : string
    {
        if(!isset($this->diskTemplate)) {
            throw KnitException::pathNotSet(__METHOD__, "template", "setTemplatePath");
        }

        return $this->diskTemplate->getPath();
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
     * @return string
     * @throws KnitException
     */
    public function getCompilerPath() : string
    {
        if(!isset($this->diskCompiler)) {
            throw KnitException::pathNotSet(__METHOD__, "compiler", "setCompilerPath");
        }

        return $this->diskCompiler->getPath();
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
     * @return string
     * @throws KnitException
     */
    public function getCachePath() : string
    {
        if(!isset($this->diskCache)) {
            throw KnitException::pathNotSet(__METHOD__, "cache", "setCachePath");
        }
        
        return $this->diskCache->getPath();
    }

    /**
     * @param int $flag
     * @return Knit
     */
    public function setCaching(int $flag) : Knit
    {
        $this->caching  =   ($flag   === Knit::CACHE_DYNAMIC) ? Knit::CACHE_DYNAMIC : Knit::CACHE_STATIC;
        return $this;
    }

    /**
     * @param Proxy $session
     * @return Knit
     */
    public function useComelySession(Proxy $session) : Knit
    {
        $this->session  =   $session;
        $bag    =   $session->getBags()->getArray();
        $this->data->setSessionData($bag);
        return $this;
    }
}
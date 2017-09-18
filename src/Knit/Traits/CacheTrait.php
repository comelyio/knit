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

namespace Comely\Knit\Traits;

use Comely\IO\Filesystem\Disk;
use Comely\IO\Filesystem\Exception\DiskException;
use Comely\Knit;
use Comely\Knit\Sandbox;
use Comely\KnitException;

/**
 * Class CacheTrait
 * @package Comely\Knit\Traits
 */
trait CacheTrait
{
    /**
     * @param string $knitted
     * @param int $ttl
     * @return bool|mixed
     * @throws DiskException
     * @throws KnitException
     * @throws \Throwable
     */
    protected function cacheRead(string $knitted , int $ttl)
    {
        if(!isset($this->diskCache)) {
            throw KnitException::pathNotSet(__METHOD__, "cache", "setCachePath");
        }

        if($ttl <=  0) {
            throw KnitException::cacheError('Expects a positive integer for TTL');
        }

        $timerStart =   microtime(true);
        $extension  =   $this->caching  === Knit::CACHE_DYNAMIC ? ".php" : ".php.knit";
        $knitted    .=  $extension;

        if($this->diskCache->hasFile($knitted)) {
            try {
                $lastModified   =   @filemtime($this->diskCache->getPath() . $knitted);
                if($lastModified    &&  (time()-$lastModified)  >   $ttl) {
                    throw KnitException::cacheError("Cached file has expired");
                }

                if($extension   === ".php.knit") {
                    // Statically cached
                    $sandBox    =   @unserialize($this->diskCache->read($knitted), [
                        "allowed_classes"   =>  [
                            "Comely\\Knit\\Sandbox"
                        ]
                    ]);

                    $sandBox->feedTimer(microtime(true)-$timerStart);
                } else {
                    // Dynamically cached
                    $sandBox    =   $this->runSandbox(
                        $this->diskCache->getPath() . $knitted,
                        $this->data->getArray()
                    );
                }

                return $sandBox;
            } catch(\Throwable $e) {
                // Delete bad cached file, silently
                $this->cacheDelete($knitted, true);
            }
        }

        return false;
    }

    /**
     * @param string $knitted
     * @param bool $silent
     * @throws DiskException
     */
    protected function cacheDelete(string $knitted, bool $silent = false)
    {
        try {
            $this->diskCache->delete($knitted);
        } catch(DiskException $e) {
            if(!$silent) throw $e;
        }
    }

    /**
     * @param Sandbox $sandbox
     * @param string $compiled
     * @param string $knitted
     * @throws KnitException
     */
    protected function cacheWrite(Sandbox $sandbox, string $compiled, string $knitted)
    {
        if(!isset($this->diskCache)) {
            throw KnitException::pathNotSet(__METHOD__, "cache", "setCachePath");
        }

        if($this->caching  === Knit::CACHE_DYNAMIC) {
            $this->diskCache->write(
                $knitted . ".php",
                $this->diskCompiler->read($compiled),
                Disk::WRITE_FLOCK
            );
        } else {
            $this->diskCache->write(
                $knitted . ".php.knit",
                serialize($sandbox),
                Disk::WRITE_FLOCK
            );
        }
    }

    /**
     * @return Knit
     * @throws KnitException
     */
    public function flushCache() : Knit
    {
        if(!isset($this->diskCache)) {
            throw KnitException::pathNotSet(__METHOD__, "cache", "setCachePath");
        }

        $cacheFiles =   $this->diskCache->find("knit_*");
        foreach($cacheFiles as $file) {
           $this->diskCache->delete($file);
        }

        return $this;
    }
}
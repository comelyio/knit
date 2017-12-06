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

namespace Comely\Knit;

use Comely\IO\DependencyInjection\Repository;
use Comely\IO\Filesystem\Disk;
use Comely\IO\Filesystem\Exception\DiskException;
use Comely\IO\Session\ComelySession;
use Comely\Knit;
use Comely\Knit\Compiler\ReservedVariables;
use Comely\KnitException;

/**
 * Class Compiler
 * @package Comely\Knit
 */
abstract class Compiler
{
    /** @var Repository */
    private $modifiers;
    /** @var ReservedVariables */
    private $reserved;
    /** @var int */
    protected $caching;
    /** @var null|Disk */
    protected $diskTemplate;
    /** @var null|Disk */
    protected $diskCompiler;
    /** @var null|Disk */
    protected $diskCache;
    /** @var null|ComelySession */
    protected $session;
    /** @var Data */
    protected $data;

    /**
     * Compiler constructor.
     */
    public function __construct()
    {
        $this->data =   new Data();
        $this->modifiers    =   new Repository();
        $this->reserved =   new ReservedVariables();
        $this->caching  =   Knit::CACHE_NONE;
    }

    /*
     * Merge: DataTrait
     */

    /**
     * @param string $key
     * @param $value
     * @return Knit
     * @throws KnitException
     */
    public function assign(string $key, $value) : Knit
    {
        $this->data->set($key, $value);
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this;
    }

    /**
     * @return Compiler
     */
    public function flushData() : self
    {
        $this->data->flush();
        return $this;
    }

    /*
     * Merge: ConfigTrait
     */

    /**
     * @param $path
     * @return Compiler
     * @throws KnitException
     */
    public function setTemplatePath($path) : self
    {
        if(is_string($path)) {
            $disk   =   new Disk($path);
        } elseif(is_object($path)   &&  $path instanceof Disk) {
            $disk   =   $path;
        } else {
            throw KnitException::badPath(
                __METHOD__,
                'Provide a valid directory path or a Comely\IO\Filesystem\Disk instance'
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
     * @param $path
     * @return Compiler
     * @throws KnitException
     */
    public function setCompilerPath($path) : self
    {
        if(is_string($path)) {
            $disk   =   new Disk($path);
        } elseif(is_object($path)   &&  $path instanceof Disk) {
            $disk   =   $path;
        } else {
            throw KnitException::badPath(
                __METHOD__,
                'Provide a valid directory path or a Comely\IO\Filesystem\Disk instance'
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
     * @param $path
     * @return Compiler
     * @throws KnitException
     */
    public function setCachePath($path) : self
    {
        if(is_string($path)) {
            $disk   =   new Disk($path);
        } elseif(is_object($path)   &&  $path instanceof Disk) {
            $disk   =   $path;
        } else {
            throw KnitException::badPath(
                __METHOD__,
                'Provide a valid directory path or a Comely\IO\Filesystem\Disk instance'
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
     * @return Compiler
     */
    public function setCaching(int $flag) : self
    {
        $this->caching  =   ($flag   === Knit::CACHE_DYNAMIC) ? Knit::CACHE_DYNAMIC : Knit::CACHE_STATIC;
        return $this;
    }

    /**
     * @param ComelySession $session
     * @return Compiler
     */
    public function useSession(ComelySession $session) : self
    {
        $this->session  =   $session;
        $bag    =   $session->getBags()->getArray();
        $this->data->setSessionData($bag);
        return $this;
    }

    /*
     * Merge: CacheTrait
     */

    /**
     * @param string $knitted
     * @param int $ttl
     * @return bool|Sandbox
     * @throws KnitException
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
                            'Comely\Knit\Sandbox'
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
     * @return string
     */
    protected function getSessionId() : string
    {
        if(isset($this->session)) {
            return $this->session->getId();
        }

        return function_exists("session_id") ? session_id() : "";
    }

    /**
     * @param string $tplFile
     * @return string
     */
    protected function getKnittedFilename(string $tplFile) : string
    {
        $suffix  =   "";
        if(isset($this->diskCache)  &&  $this->caching  === Knit::CACHE_STATIC) {
            // Static caching should be suffixed by session Id for security reasons
            $suffix =   $this->getSessionId();
        }

        return sprintf('knit_%1$s_%2$s', hash('sha1', $tplFile), $suffix);
    }

    /**
     * Makes sure that template and compiler paths are set
     * @param string $method
     * @throws KnitException
     */
    public function checkPaths(string $method)
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
     * @return Compiler
     * @throws KnitException
     */
    public function flushCache() : self
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

    /*
     * Finish Merger
     */


    /**
     * Reads a template file
     *
     * @param string $file
     * @return string
     * @throws KnitException
     */
    public function read(string $file) : string
    {
        // Get pathinfo of template file
        $fileInfo   =   pathinfo($file);

        // Check extension
        $extension  =   $fileInfo["extension"] ?? "";
        if(!in_array($extension, Knit::FILES)) {
            throw KnitException::readError(sprintf('Template files cannot have "%1$s" extension', $extension));
        }

        // Read
        try {
            $tpl    =   $this->diskTemplate->read($file);

            // Return template contents after cleansing
            return str_replace(["<?","?>"], ["&lt;?","?&gt;"], $tpl);
        } catch(DiskException $e) {
            throw KnitException::readError($e->getMessage());
        }
    }

    /**
     * @return array
     */
    public function getDelimiters() : array
    {
        return Knit::DELIMITERS;
    }

    /**
     * @return Repository
     */
    public function getModifiers() : Repository
    {
        return $this->modifiers;
    }

    /**
     * @return ReservedVariables
     */
    public function getReservedVariables() : ReservedVariables
    {
        return $this->reserved;
    }

    /**
     * @param string $tplFile
     * @param string $outputScript
     * @return string
     */
    protected function compile(string $tplFile, string $outputScript) : string
    {
        // Parse template file
        $parser =   new Knit\Compiler\Template($this, $tplFile);

        // Prepend parsed template
        $parsed =   sprintf('<?php%2$sdefine("COMELY_KNIT", "%1$s");%2$s', Knit::VERSION, Knit::EOL);
        $parsed .=  sprintf('define("COMELY_KNIT_PARSE_TIMER", %1$s);%2$s', $parser->getTimer(), Knit::EOL);
        $parsed .=  sprintf('define("COMELY_KNIT_COMPILED_ON", %1$s);%2$s?>', microtime(true), Knit::EOL);
        $parsed .=  $parser->getParsed();

        // Write compiled PHP script
        $outputScript   .=  sprintf("_%d.php", mt_rand(0,100));
        $this->diskCompiler->write($outputScript, $parsed, Disk::WRITE_FLOCK);

        return $this->diskCompiler->getPath() . $outputScript;
    }

    /**
     * @param string $script
     * @param array $data
     * @return Sandbox
     * @throws KnitException
     */
    protected function runSandbox(string $script, array $data) : Sandbox
    {
        try {
            return new Sandbox($script, $data);
        } catch(\Throwable $e) {
            throw KnitException::sandBoxError($e->getMessage());
        }
    }
}
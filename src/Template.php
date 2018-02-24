<?php
/**
 * This file is part of Knit package.
 * https://github.com/comelyio/knit
 *
 *  Copyright (c) 2018 Furqan A. Siddiqui <hello@furqansiddiqui.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code or visit following link:
 * https://github.com/comelyio/knit/blob/master/LICENSE
 */

declare(strict_types=1);

namespace Comely\Knit;

use Comely\IO\FileSystem\Disk\Directory;
use Comely\IO\FileSystem\Exception\DiskException;
use Comely\Knit\Exception\CachingException;
use Comely\Knit\Exception\CompilerException;
use Comely\Knit\Exception\SandboxException;
use Comely\Knit\Exception\TemplateException;
use Comely\Knit\Template\Data;
use Comely\Knit\Template\Metadata;
use Comely\Knit\Template\Sandbox;

/**
 * Class Template
 * @package Comely\Knit
 */
class Template
{
    /** @var Knit */
    private $knit;
    /** @var Data */
    private $data;
    /** @var Metadata */
    private $metadata;
    /** @var null|Caching */
    private $caching;
    /** @var Directory */
    private $directory;
    /** @var string */
    private $fileName;

    /**
     * Template constructor.
     * @param Knit $knit
     * @param Directory $directory
     * @param string $fileName
     */
    public function __construct(Knit $knit, Directory $directory, string $fileName)
    {
        if (!preg_match('/^([a-z0-9\_\-\.]+(\/|\\\))*[a-z0-9\_\-\.]+\.knit$/', $fileName)) {
            throw new TemplateException('Invalid knit template file');
        }

        $this->knit = $knit;
        $this->data = new Data();
        $this->metadata = new Metadata();
        $this->directory = $directory;
        $this->fileName = $fileName;
    }

    /**
     * @return Caching
     * @throws CachingException
     */
    public function caching(): Caching
    {
        if ($this->caching) {
            return $this->caching;
        }

        if (!$this->knit->directories()->_caching) {
            throw new CachingException('Caching directory not defined');
        }

        // Clone Knit's caching instance
        $this->caching = clone $this->knit->caching();
        return $this->caching;
    }

    /**
     * @param string $key
     * @param $value
     * @return Template
     * @throws TemplateException
     */
    public function assign(string $key, $value): self
    {
        $this->data->push($key, $value);
        return $this;
    }

    /**
     * @param string $key
     * @param Metadata\MetaValueInterface $value
     * @return Template
     */
    public function metadata(string $key, Metadata\MetaValueInterface $value): self
    {
        $this->metadata->add($key, $value);
        return $this;
    }

    /**
     * @return null|string
     * @throws CachingException
     */
    private function cached(): ?string
    {
        if (!$this->caching) {
            return null;
        }

        // Get cached file ID and directory
        $cacheFileId = md5($this->fileName);
        $cachedFileName = null;
        $cachingType = null;
        $cachingDirectory = $this->knit->directories()->_caching;
        if (!$cachingDirectory) {
            throw new CachingException('Caching directory not defined');
        }

        // Determine cached file name
        $sessionId = $this->caching->_sessionId;
        if ($this->caching->_type === Caching::AGGRESSIVE && $sessionId) {
            $cachingType = Caching::AGGRESSIVE;
            $cachedFileName = sprintf('knit_%s-%s.knit', $cacheFileId, $sessionId);
        } elseif ($this->caching->_type === Caching::NORMAL) {
            $cachingType = Caching::NORMAL;
            $cachedFileName = sprintf('knit_%s.php', $cacheFileId);
        }

        if ($cachingType && $cachedFileName) {
            try {
                $cachedFile = $cachingDirectory->file($cachedFileName);
                if (!$cachedFile->permissions()->read) {
                    throw new CachingException('Cached knit template file is not readable');
                }

                // Check for expiry
                if ($this->caching->_ttl) {
                    $cachedFileTime = $cachedFile->lastModified();
                    if ((time() - $cachedFileTime) >= $this->caching->_ttl) {
                        throw new \Exception('Cached template file has expired');
                    }
                }

                if ($cachingType === Caching::AGGRESSIVE) {
                    // Read
                    try {
                        $cachedTemplate = $cachedFile->read();
                    } catch (DiskException $e) {
                        throw new CachingException('Cached file could not be read');
                    }

                    $cachedStart = substr($cachedTemplate, 0, 6);
                    $cachedEnd = substr($cachedTemplate, -6);
                    if (!$cachedStart !== "~knit:" || $cachedEnd !== ":knit~") {
                        throw new CachingException('Bad or incomplete cached knit template');
                    }

                    return substr($cachedTemplate, 6, -6);
                } elseif ($cachingType === Caching::NORMAL) {
                    // Run in sandbox
                    try {
                        return (new Sandbox($cachedFile, $this->data))
                            ->run();
                    } catch (SandboxException $e) {
                        throw new CachingException($e->getMessage());
                    }
                }

            } catch (\Exception $e) {
                // CachingException messages will be triggered as E_USER_WARNING error
                // All other exceptions will be ignored
                if ($e instanceof CachingException) {
                    trigger_error($e->getMessage(), E_USER_WARNING);
                }

                // Check if cache file exists
                if (isset($cachedFile)) {
                    // Not being used indicates this needs to be deleted
                    try {
                        $cachedFile->delete();
                    } catch (DiskException $e) {
                        trigger_error('Failed to delete cached template file', E_USER_WARNING);
                    }
                }
            }
        }

        return null;
    }

    /**
     * @return string
     * @throws CompilerException
     * @throws SandboxException
     */
    private function compile(): string
    {
        // Compile knit template
        $compiled = (new Compiler($this->knit, $this->directory, $this->fileName))
            ->compile();

        // Get compiled file
        try {
            $compiledFile = $this->knit->directories()->_compiler->file($compiled->compiledFile);
        } catch (DiskException $e) {
            throw new CompilerException(
                sprintf('Failed to located compiled knit template file "%s"', $compiled->compiledFile)
            );
        }

        // Run in sandbox and return output
        $output = (new Sandbox($compiledFile, $this->data))
            ->run();

        // Caching
        if ($this->caching) {
            $cacheFileId = md5($this->fileName);
            $sessionId = $this->caching->_sessionId;
            if ($this->caching->_type === Caching::AGGRESSIVE && $sessionId) {
                $cacheFileName = sprintf('knit_%s-%s.knit', $cacheFileId, $sessionId);
                $cacheContents = "~knit:" . $output . ":knit~";
            } elseif ($this->caching->_type === Caching::NORMAL) {
                $cacheFileName = sprintf('knit_%s.php', $cacheFileId);
                try {
                    $cacheContents = $compiledFile->read();
                } catch (DiskException $e) {
                    trigger_error('Failed to read compiled knit file for cache', E_USER_WARNING);
                }
            }

            // Write cache
            if (isset($cacheFileName, $cacheContents)) {
                $cachingDirectory = $this->knit->directories()->_caching;
                if (!$cachingDirectory) {
                    trigger_error('Failed to cache knit template, caching directory is not set', E_USER_WARNING);
                }

                try {
                    $cachingDirectory->write($cacheFileName, $cacheContents, false, true);
                } catch (DiskException $e) {
                    trigger_error('Failed to write knit cache template file', E_USER_WARNING);
                }
            }
        }

        // Metadata
        $this->metadata("timer.compile", new Metadata\MetaVariable($compiled->timer));

        // Delete compiled file
        try {
            $compiledFile->delete();
        } catch (DiskException $e) {
            trigger_error('Failed to delete compiled knit template PHP file', E_USER_WARNING);
        }

        // Return output string
        return $output;
    }

    /**
     * @return string
     * @throws CachingException
     * @throws CompilerException
     * @throws SandboxException
     * @throws TemplateException
     */
    public function knit(): string
    {
        $timer = microtime(true);
        $template = $this->cached() ?? $this->compile() ?? null;
        if (!is_string($template)) {
            throw new TemplateException('Failed to read cached or compile fresh knit template');
        }

        // Process metadata
        foreach ($this->metadata as $key => $value) {
            $metaValue = null;
            if ($value instanceof Metadata\MetaVariable) {
                $metaValue = $value->value();
            } elseif ($value instanceof Metadata\MetaTemplate) {
                try {
                    $metaTemplate = $this->knit->template($value->template());
                    $metaTemplate->caching()->disable(); // Disable caching
                    $value->assignData($metaTemplate);
                    $metaValue = $metaTemplate->knit();
                } catch (TemplateException $e) {
                    $metaValue = sprintf(
                        'An error occurred while parsing meta template "%s". [%s] %s',
                        $value->template(),
                        get_class($e),
                        $e->getMessage()
                    );
                }
            }

            if ($metaValue) {
                $template = str_replace('%[%' . $key . '%]%', $metaValue, $template);
            }
        }

        // Timer
        $template = str_replace('%[%timer%]%', (microtime(true) - $timer), $template);

        // Return processed template
        return $template;
    }
}
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

use Comely\IO\FileSystem\Disk\File;
use Comely\IO\FileSystem\Exception\DiskException;
use Comely\Knit\Exception\CachingException;
use Comely\Knit\Exception\TemplateException;
use Comely\Knit\Template\Data;
use Comely\Knit\Template\Metadata;

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
    /** @var File */
    private $file;
    /** @var null|Caching */
    private $caching;

    /**
     * Template constructor.
     * @param Knit $knit
     * @param string $fileName
     * @throws TemplateException
     */
    public function __construct(Knit $knit, string $fileName)
    {
        $templatesDirectory = $knit->directories()->_templates;
        if (!$templatesDirectory) {
            throw new TemplateException('Cannot create Template instance, base templates directory not set');
        }

        try {
            $file = $templatesDirectory->file($fileName);
            if (!$file->permissions()->read) {
                throw new TemplateException(sprintf('Template file "%s" is not readable', $fileName));
            }
        } catch (DiskException $e) {
            throw new TemplateException(sprintf('Template file "%s" not found', $fileName));
        }

        $this->knit = $knit;
        $this->file = $file;
        $this->data = new Data();
        $this->metadata = new Metadata();
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

    public function meta(string $key, $value): self
    {
        $this->metadata;
        return $this;
    }

    public function compile(): string
    {
        $compiler = new Compiler($this->knit, $this->file, $this->data);
    }

    public function knit(): string
    {
        // Look in cache

        // Compile a fresh copy
    }
}
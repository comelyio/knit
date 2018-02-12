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

/**
 * Class Directories
 * @package Comely\Knit
 * @property null|Directory $_templates
 * @property null|Directory $_compiler
 * @property null|Directory $_caching
 */
class Directories
{
    /** @var null|Directory */
    private $templates;
    /** @var null|Directory */
    private $compiler;
    /** @var null|Directory */
    private $caching;

    /**
     * @param $prop
     * @return bool|Directory|null
     */
    public function __get($prop)
    {
        switch ($prop) {
            case "_templates":
                return $this->templates;
            case "_compiler":
                return $this->compiler;
            case "_caching":
                return $this->caching;
        }

        return false;
    }

    /**
     * @param $prop
     * @param $value
     * @return bool
     */
    public function __set($prop, $value)
    {
        return false;
    }

    /**
     * @param Directory $dir
     * @return Directories
     */
    public function templates(Directory $dir): self
    {
        $this->templates = $dir;
        return $this;
    }

    /**
     * @param Directory $dir
     * @return Directories
     */
    public function compiler(Directory $dir): self
    {
        $this->compiler = $dir;
        return $this;
    }

    /**
     * @param Directory $dir
     * @return Directories
     */
    public function caching(Directory $dir): self
    {
        $this->caching = $dir;
        return $this;
    }
}
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

use Comely\Knit\Exception\KnitException;
use Comely\Knit\Exception\TemplateException;

/**
 * Class Knit
 * @package Comely\Knit
 */
class Knit
{
    /** @var Caching */
    private $caching;
    /** @var Directories */
    private $directories;
    /** @var Modifiers */
    private $modifiers;

    /**
     * Knit constructor.
     */
    public function __construct()
    {
        $this->caching = new Caching();
        $this->directories = new Directories();
        $this->modifiers = new Modifiers();
    }

    /**
     * @return Caching
     */
    public function caching(): Caching
    {
        if (!$this->directories->_caching) {
            throw new KnitException('Caching directory not defined');
        }

        return $this->caching;
    }

    /**
     * @return Directories
     */
    public function directories(): Directories
    {
        return $this->directories;
    }

    /**
     * @return Modifiers
     */
    public function modifiers(): Modifiers
    {
        return $this->modifiers;
    }

    /**
     * @param string $fileName
     * @return Template
     */
    public function template(string $fileName): Template
    {
        return new Template($this, $fileName);
    }
}
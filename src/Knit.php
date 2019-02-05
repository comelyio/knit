<?php
/**
 * This file is part of Knit package.
 * https://github.com/comelyio/knit
 *
 *  Copyright (c) 2019 Furqan A. Siddiqui <hello@furqansiddiqui.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code or visit following link:
 * https://github.com/comelyio/knit/blob/master/LICENSE
 */

declare(strict_types=1);

namespace Comely\Knit;

use Comely\IO\Events\EventsHandler;
use Comely\Knit\Exception\CachingException;
use Comely\Knit\Exception\TemplateException;

/**
 * Class Knit
 * @package Comely\Knit
 */
class Knit implements Constants
{
    /** @var Caching */
    private $caching;
    /** @var Directories */
    private $directories;
    /** @var Modifiers */
    private $modifiers;
    /** @var EventsHandler */
    private $events;

    /**
     * Knit constructor.
     */
    public function __construct()
    {
        $this->caching = new Caching();
        $this->directories = new Directories();
        $this->modifiers = new Modifiers();
        $this->events = new EventsHandler();
    }

    /**
     * @return Caching
     */
    public function caching(): Caching
    {
        if (!$this->directories->_caching) {
            throw new CachingException('Caching directory not defined');
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
     * @return EventsHandler
     */
    public function events(): EventsHandler
    {
        return $this->events;
    }

    /**
     * @param string $fileName
     * @return Template
     * @throws TemplateException
     */
    public function template(string $fileName): Template
    {
        $templatesDirectory = $this->directories()->_templates;
        if (!$templatesDirectory) {
            throw new TemplateException('Knit base templates directory not set');
        }

        return new Template($this, $templatesDirectory, $fileName);
    }
}
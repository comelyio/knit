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

use Comely\Knit;
use Comely\KnitException;

/**
 * Class ActionsTrait
 * @package Comely\Knit\Traits
 */
trait ActionsTrait
{
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
}
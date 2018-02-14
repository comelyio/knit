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
use Comely\Knit\Compiler\CompiledTemplate;
use Comely\Knit\Compiler\Parser;
use Comely\Knit\Exception\CompilerException;
use Comely\Knit\Template\Data;

/**
 * Class Compiler
 * @package Comely\Knit
 */
class Compiler
{
    /** @var Knit */
    private $knit;
    /** @var File */
    private $file;
    /** @var Data */
    private $data;

    /**
     * Compiler constructor.
     * @param Knit $knit
     * @param File $file
     * @param Data $data
     */
    public function __construct(Knit $knit, File $file, Data $data)
    {
        $this->knit = $knit;
        $this->file = $file;
        $this->data = $data;
    }

    public function compile(): CompiledTemplate
    {

    }


}
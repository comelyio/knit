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

namespace Comely\Knit\Template;

use Comely\IO\FileSystem\Disk\File;
use Comely\Knit\Exception\SandboxException;

/**
 * Class Sandbox
 * @package Comely\Knit\Template
 */
class Sandbox
{
    /** @var array */
    private $data;
    /** @var File */
    private $file;

    /**
     * Sandbox constructor.
     * @param File $compiledFile
     * @param Data $data
     */
    public function __construct(File $compiledFile, Data $data)
    {
        $this->data = $data->array();
        $this->file = $compiledFile;
    }

    /**
     * @return string
     * @throws SandboxException
     */
    public function run(): string
    {
        ob_start();
        /** @noinspection PhpIncludeInspection */
        include($this->file->path());
        $output = ob_get_contents();
        ob_end_clean();

        if (!$output) {
            throw new SandboxException('Sandbox failed to generate template output');
        }

        if (!defined("COMELY_KNIT")) {
            throw new SandboxException('Bad or incorrectly compiled knit template');
        }

        return $output;
    }
}
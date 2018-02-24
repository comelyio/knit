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
use Comely\IO\FileSystem\Disk\File;
use Comely\IO\FileSystem\Exception\DiskException;
use Comely\Knit\Compiler\CompiledTemplate;
use Comely\Knit\Compiler\Parser;
use Comely\Knit\Exception\CompilerException;
use Comely\Knit\Exception\ParseException;

/**
 * Class Compiler
 * @package Comely\Knit
 */
class Compiler implements Constants
{
    /** @var Knit */
    private $knit;
    /** @var Directory */
    private $directory;
    /** @var File */
    private $file;
    /** @var string */
    private $fileName;
    /** @var string */
    private $eolChar;

    /**
     * Compiler constructor.
     * @param Knit $knit
     * @param Directory $directory
     * @param string $fileName
     * @throws CompilerException
     */
    public function __construct(Knit $knit, Directory $directory, string $fileName)
    {
        if (!$directory->permissions()->read) {
            throw new CompilerException(sprintf('Template "%s" directory is not readable', $fileName));
        }

        try {
            $file = $directory->file($fileName);
            if (!$file->permissions()->read) {
                throw new CompilerException(sprintf('Template file "%s" is not readable', $fileName));
            }
        } catch (DiskException $e) {
            throw new CompilerException(sprintf('Template file "%s" not found', $fileName));
        }

        $this->knit = $knit;
        $this->directory = $directory;
        $this->file = $file;
        $this->fileName = $fileName;
        $this->eolChar = PHP_EOL;
    }

    /**
     * @param Parser\Variables|null $variables
     * @return string
     * @throws CompilerException
     */
    public function parse(?Parser\Variables $variables = null): string
    {
        try {
            return (new Parser($this->knit, $this->file->read(), $variables))
                ->parse();
        } catch (DiskException $e) {
            throw new CompilerException(
                sprintf('An error occurred while reading template file "%s"', $this->fileName)
            );
        } catch (ParseException $e) {
            throw new CompilerException(
                sprintf(
                    'Parsing error "%s" in template file "%s" on line %d near "%s"',
                    $e->getMessage(),
                    $this->fileName,
                    $e->line(),
                    $e->token()
                )
            );
        }
    }

    /**
     * @return CompiledTemplate
     * @throws CompilerException
     */
    public function compile(): CompiledTemplate
    {
        $compilerDirectory = $this->knit->directories()->_compiler;
        if (!$compilerDirectory) {
            throw new CompilerException('Knit compiler directory not set');
        } elseif (!$compilerDirectory->permissions()->write) {
            throw new CompilerException('Knit compiler directory not writable');
        }

        $timer = microtime(true); // Start timer

        // new CompiledTemplate instance
        $compiledTemplate = new CompiledTemplate();
        $compiledTemplate->templateName = $this->fileName;
        $compiledTemplate->timeStamp = time();
        $compiledTemplate->timer = microtime(true) - $timer;

        // Compile parsed template into PHP code
        $compile = '<?php' . $this->eolChar;
        $compile .= sprintf('define("COMELY_KNIT", "%s");%s', self::VERSION, $this->eolChar);
        $compile .= sprintf('define("COMELY_KNIT_PARSE_TIMER", %s);%s', $compiledTemplate->timer, $this->eolChar);
        $compile .= sprintf('define("COMELY_KNIT_TIMESTAMP", %s);%s?>', $compiledTemplate->timeStamp, $this->eolChar);
        $compile .= $this->parse(); // Parse

        // Compile file name
        $compiledTemplate->compiledFile = sprintf(
            'knit_%s_%d.php',
            md5($this->fileName),
            mt_rand(0, 1000)
        );

        // Write
        try {
            $wrote = $compilerDirectory->write($compiledTemplate->compiledFile, $compile, false, true);
            if (!$wrote) {
                throw new CompilerException('An an unexpected error occurred while writing compiled knit file');
            }
        } catch (DiskException $e) {
            throw new CompilerException('Failed to write compiled knit template file');
        }

        return $compiledTemplate;
    }
}
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

namespace Comely\Knit;

use Comely\IO\DependencyInjection\Repository;
use Comely\IO\Filesystem\Disk;
use Comely\IO\Filesystem\Exception\DiskException;
use Comely\Knit;
use Comely\Knit\Compiler\ReservedVariables;
use Comely\Knit\Traits\ActionsTrait;
use Comely\Knit\Traits\CacheTrait;
use Comely\Knit\Traits\ConfigTrait;
use Comely\Knit\Traits\DataTrait;
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

    use ActionsTrait;
    use CacheTrait;
    use ConfigTrait;
    use DataTrait;

    /**
     * Compiler constructor.
     */
    public function __construct()
    {
        $this->data =   new Data();
        $this->modifiers    =   new Repository();
        $this->reserved =   new ReservedVariables();
    }

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
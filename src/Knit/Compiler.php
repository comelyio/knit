<?php
declare(strict_types=1);

namespace Comely\Knit;

use Comely\IO\DependencyInjection\Repository;
use Comely\IO\Filesystem\Exception\DiskException;
use Comely\Knit;
use Comely\Knit\Traits\ConfigTrait;
use Comely\Knit\Traits\DataTrait;

use Comely\KnitException;

/**
 * Class Compiler
 * @package Comely\Knit
 */
abstract class Compiler
{
    private $data;
    private $modifiers;

    use ConfigTrait;
    use DataTrait;

    /**
     * Compiler constructor.
     */
    public function __construct()
    {
        $this->data =   new Data();
        $this->modifiers    =   new Repository();
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
     * @param string $tplFile
     * @return string
     */
    protected function compile(string $tplFile) : string
    {
        $parser   =   new Knit\Compiler\Template($this, $tplFile);
        $this->diskCompiler->write("foo.php", $parser->getParsed());

        return "";
    }
}
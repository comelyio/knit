<?php
declare(strict_types=1);

namespace Comely\Knit;

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

    use ConfigTrait;
    use DataTrait;

    /**
     * Compiler constructor.
     */
    public function __construct()
    {
        $this->data =   new Data();
    }

    /**
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
            return $this->diskTemplate->read($file);
        } catch(DiskException $e) {
            throw KnitException::readError($e->getMessage());
        }
    }

    /**
     * @param string $tplFile
     * @return string
     */
    private function compile(string $tplFile) : string
    {
        
    }
}
<?php
declare(strict_types=1);

namespace Comely\Knit\Compiler\Parser;
use Comely\Knit\Compiler\Template;
use Comely\KnitException;

/**
 * Class IncludeTrait
 * @package Comely\Knit\Compiler\Parser
 */
trait IncludeTrait
{
    /**
     * Handle a knit include
     * @return string
     * @throws KnitException
     */
    public function parseIncludeKnit() : string
    {
        // exp: include knit="file.knit"
        $pieces =   preg_split('/\s/', $this->token);
        $knit   =   substr($pieces[1], 6, -1);

        // Return parsed template file
        try {
            return (new Template($this->compiler, $knit))->getParsed();
        } catch(KnitException $e) {
            throw KnitException::parseError(
                sprintf(
                    '%1$s included in "%2$s" on line # %4$d',
                    $e->getMessage(),
                    basename($this->file),
                    $this->file,
                    $this->lineNum
                )
            );
        }
    }
}
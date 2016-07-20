<?php
declare(strict_types=1);

namespace Comely\Knit\Compiler\Parser;

/**
 * Class PrintTrait
 * @package Comely\Knit\Compiler\Parser
 */
trait PrintTrait
{
    /**
     * Parse a print statement
     * @return string
     */
    public function parsePrint() : string
    {
        $var    =   $this->resolveVariable($this->token);
        return sprintf('<?php print %s; ?>', $var);
    }
}
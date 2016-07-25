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
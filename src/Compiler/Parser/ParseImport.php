<?php
/**
 * This file is part of Knit package.
 * https://github.com/comelyio/knit
 *
 *  Copyright (c) 2019 Furqan A. Siddiqui <hello@furqansiddiqui.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code or visit following link:
 * https://github.com/comelyio/knit/blob/master/LICENSE
 */

declare(strict_types=1);

namespace Comely\Knit\Compiler\Parser;

use Comely\Knit\Compiler;
use Comely\Knit\Exception\CompilerException;

/**
 * Trait ParseImport
 * @package Comely\Knit\Compiler\Parser
 */
trait ParseImport
{
    /**
     * @return string
     * @throws CompilerException
     */
    private function parseImport(): string
    {
        // exp: knit "file" or knit"file"
        $fileName = trim(substr($this->token, 5), ' \'"/-.');

        // Import parsed template source, but use same Variables $reserved scope
        return (new Compiler($this->knit, $this->directory, $fileName . ".knit"))->parse($this->reserved);
    }
}
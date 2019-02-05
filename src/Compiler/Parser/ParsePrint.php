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

/**
 * Trait ParsePrint
 * @package Comely\Knit\Compiler\Parser
 */
trait ParsePrint
{
    /**
     * @return string
     */
    private function parsePrint(): string
    {
        return sprintf('<?php print %s; ?>', $this->variable($this->token));
    }
}
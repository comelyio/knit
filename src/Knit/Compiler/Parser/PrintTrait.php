<?php
/**
 * This file is part of Knit package.
 * https://github.com/comelyio/knit
 *
 *  Copyright (c) 2017 Furqan A. Siddiqui <hello@furqansiddiqui.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code or visit following link:
 * https://github.com/comelyio/knit/blob/master/LICENSE
 */

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
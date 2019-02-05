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
 * Trait ParseCount
 * @package Comely\Knit\Compiler\Parser
 */
trait ParseCount
{
    /**
     * @return string
     */
    private function parseCount(): string
    {
        // exp: count $i 1 to 100
        $pieces = preg_split('/\s/', $this->token);
        $index = $pieces[1];

        // Reserve variable
        $this->reserveVariable($index);

        // Return count statement
        $this->clauses["count"][] = ["close" => 1, "var" => $index];
        return sprintf(
            '<?php for(%1$s=%2$d;%1$s<=%3$d;%1$s++) { ?>',
            $index,
            intval($pieces[2]),
            intval($pieces[4])
        );
    }

    /**
     * @return string
     */
    private function parseCountClose(): string
    {
        if (empty($this->clauses["count"])) {
            throw $this->exception('No count loop was found');
        }

        $clause = array_pop($this->clauses["count"]);
        $this->releaseVariable($clause["var"]);

        return sprintf('<?php } unset(%1$s); ?>', $clause["var"]);
    }
}
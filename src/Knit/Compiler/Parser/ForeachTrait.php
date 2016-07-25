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

trait ForeachTrait
{
    /**
     * Parses foreach statement
     * @return string
     */
    private function parseForeach() : string
    {
        // exp: foreach $arr as $var
        $pieces =   preg_split('/\s/', $this->token);
        $array  =   $this->resolveVariable($pieces[1]);
        $index  =   $pieces[3];

        // Reserve variable
        $this->reserveVariable($index);

        // Return foreach statement
        $this->clauses["foreach"][] =   ["close" => 2, "var" => $index];
        return sprintf('<?php if(isset(%1$s) && is_array(%1$s)  &&  count(%1$s) > 0) { foreach(%1$s as %2$s) { ?>', $array, $index);
    }

    /**
     * Returns foreach else statement
     * @return string
     */
    private function parseForeachElse() : string
    {
        if(empty($this->clauses["foreach"])) {
            $this->throwException('No foreach iteration was found');
        }

        end($this->clauses["foreach"]);
        $clause =   key($this->clauses["foreach"]);

        if($this->clauses["foreach"][$clause]["close"]  !== 2) {
            $this->throwException('Multiple calls to {foreachelse} inside single foreach iteration');
        }

        $this->clauses["foreach"][$clause]["close"]  =   1;
        reset($this->clauses["foreach"]);
        
        return '<?php } } else { ?>';
    }

    /**
     * Closes foreach statement
     * @return string
     */
    private function parseForeachClose() : string
    {
        if(empty($this->clauses["foreach"])) {
            $this->throwException('No foreach iteration was found');
        }

        $clause =   array_pop($this->clauses["foreach"]);
        $this->releaseVariable($clause["var"]);

        return sprintf(
            '<?php %1$s unset(%2$s); ?>',
            str_repeat('} ', $clause["close"]),
            $clause["var"]
        );
    }
}
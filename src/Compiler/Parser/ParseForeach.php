<?php
/**
 * This file is part of Knit package.
 * https://github.com/comelyio/knit
 *
 *  Copyright (c) 2018 Furqan A. Siddiqui <hello@furqansiddiqui.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code or visit following link:
 * https://github.com/comelyio/knit/blob/master/LICENSE
 */

declare(strict_types=1);

namespace Comely\Knit\Compiler\Parser;

/**
 * Trait ParseForeach
 * @package Comely\Knit\Compiler\Parser
 */
trait ParseForeach
{
    /**
     * @return string
     * @throws \Comely\Knit\Exception\ParseException
     */
    private function parseForeach(): string
    {
        // exp: foreach $arr as $var
        $pieces = preg_split('/\s/', $this->token);
        $array = $this->variable($pieces[1]);
        $index = $pieces[3];

        // Reserve variable
        $this->reserveVariable($index);

        // Return foreach statement
        $this->clauses["foreach"][] = ["close" => 2, "var" => $index];
        return sprintf(
            '<?php if(isset(%1$s) && is_array(%1$s)) { foreach(%1$s as %2$s) { ?>',
            $array,
            $index
        );
    }

    /**
     * @return string
     * @throws \Comely\Knit\Exception\ParseException
     */
    private function parseForeachPaired(): string
    {
        // exp: foreach $arr as $key => $val
        $pieces = preg_split('/\s/', $this->token);
        $array = $this->variable($pieces[1]);
        $key = $pieces[3];
        $val = $pieces[5];

        // Reserve variables
        $this->reserveVariable($key);
        $this->reserveVariable($val);

        // Return foreach statement
        $this->clauses["foreach"][] = ["close" => 2, "var" => $key, "var2" => $val];
        return sprintf(
            '<?php if(isset(%1$s) && is_array(%1$s)) { foreach(%1$s as %2$s => %3$s) { ?>',
            $array,
            $key,
            $val
        );
    }

    /**
     * @return string
     */
    private function parseForeachElse(): string
    {
        if (empty($this->clauses["foreach"])) {
            throw $this->exception('No foreach iteration was found');
        }

        end($this->clauses["foreach"]);
        $clause = key($this->clauses["foreach"]);
        if ($this->clauses["foreach"][$clause]["close"] !== 2) {
            throw $this->exception('Multiple calls to {foreachelse} inside single foreach iteration');
        }

        $this->clauses["foreach"][$clause]["close"] = 1;
        reset($this->clauses["foreach"]);

        return '<?php } } else { ?>';
    }

    /**
     * @return string
     */
    private function parseForeachClose(): string
    {
        if (empty($this->clauses["foreach"])) {
            throw $this->exception('No foreach iteration was found');
        }

        $clause = array_pop($this->clauses["foreach"]);
        $this->releaseVariable($clause["var"]);
        if (array_key_exists("var2", $clause) && is_string($clause["var2"])) {
            $this->releaseVariable($clause["var2"]);
        }

        return sprintf(
            '<?php %1$s unset(%2$s); ?>',
            str_repeat('} ', $clause["close"]),
            $clause["var"]
        );
    }
}
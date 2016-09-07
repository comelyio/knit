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

/**
 * Class IfTrait
 * @package Comely\Knit\Compiler\Parser
 */
trait IfTrait
{
    /**
     * @param bool $isElseIf
     * @return string
     */
    private function parseIf(bool $isElseIf = false) : string
    {
        $divider    =   "";
        $statement  =   $isElseIf ? '<?php elseif(' : '<?php if(';

        // Check if IF statement has && or ||
        if(preg_match('/\s+\&{2}\s+/', $this->token)) {
            $divider    .=  "&";
        }

        if(preg_match('/\s+\|{2}\s+/', $this->token)) {
            $divider    .=  "|";
        }

        // IF statements cannot have both && and ||
        if(strlen($divider) ==  2) {
            $this->throwException('If statement cannot have both "&&" and "||"');
        }

        // Split IF statement for parsing
        $tokenOffset =  $isElseIf ? 7 : 3; // "elseif " (7) or "if " (3)
        $token  =   substr($this->token, $tokenOffset);
        $tokens =   $divider ? preg_split(sprintf('/\s+\%s{2}\s+/', $divider), $token) : [$token];

        $pieces =   [];
        foreach($tokens as $token) {
            $pieces[]   =   $this->parseIfStatement(trim($token));
        }

        $statement  .=   implode(sprintf(' %s ', str_repeat($divider, 2)), $pieces);

        if(!$isElseIf) {
            $this->clauses["ifs"]++;
        }

        return $statement . ') { ?>';
    }

    /**
     * @param string $token
     * @return string
     */
    private function parseIfStatement(string $token) : string
    {
        $pieces =   preg_split('/\s+/', $token);
        $statement[]    =   $this->resolveOperand($pieces[0], "left", true);
        if(array_key_exists(1, $pieces)) {
            // Operator
            if(!in_array($pieces[1], ["===","==","!=","!==",">=","<="])) {
                $this->throwException(sprintf('Operator %s not supported', $pieces[2]));
            }

            $statement[]   =   $pieces[1];

            if(!array_key_exists(2, $pieces)) {
                $this->throwException("Missing right operand");
            }

            $statement[]    =   $this->resolveOperand($pieces[2], "right", false);
        }

        return implode(" ", $statement);
    }

    /**
     * @param string $operand
     * @param string $which
     * @param bool $canNegate
     * @return string
     * @throws \Comely\KnitException
     */
    private function resolveOperand(string $operand, string $which, bool $canNegate = false) : string
    {
        $negate =   $operand[0] === "!" ? "!" : "";
        if($negate  &&  !$canNegate) {
            $this->throwException(sprintf('Cannot use ! in %s operand', $which));
        }

        // Resolve operand
        if(preg_match('/^\!?\$[a-z\_]+[a-z0-9\_\.\:\|\$\']*$/i', $operand)) {
            // Single variable
            $var    =   $negate ? substr($operand, 1) : $operand;
            return sprintf("%s%s", $negate, $this->resolveVariable($var));
        } elseif(preg_match('/^(\"|\').*(\"|\')$/', $operand)) {
            // Plain string
            $str    =   addslashes(substr($operand, 1, -1));
            return sprintf('"%s"', $str);
        } elseif(preg_match('/^[0-9][\.0-9]*$/', $operand)) {
            // Integer or float
            return $operand;
        } elseif(in_array(strtolower($operand), ["true","false","null"])) {
            // Bool or NULL
            return $operand;
        }

        $this->throwException(sprintf('Syntax error in %s operand of {if...} statement', $which));
    }

    /**
     * Else statement
     * @return string
     */
    private function parseIfElse() : string
    {
        return '<?php } else { ?>';
    }

    /**
     * Close if statement
     * @return string
     * @throws \Comely\KnitException
     */
    private function parseIfClose() : string
    {
        if(!$this->clauses["ifs"]) {
            $this->throwException("No conditional statement found to close");
        }

        $this->clauses["ifs"]--;
        return '<?php } ?>';
    }
}
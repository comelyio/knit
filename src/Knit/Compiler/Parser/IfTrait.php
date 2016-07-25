<?php
declare(strict_types=1);

namespace Comely\Knit\Compiler\Parser;

/**
 * Class IfTrait
 * @package Comely\Knit\Compiler\Parser
 */
trait IfTrait
{
    /**
     * Start if statement
     * @return string
     */
    private function parseIf() : string
    {
        $pieces =   preg_split('/\s/', $this->token);
        $statement[]    =   $this->resolveOperand($pieces[1], "left", true);

        if(array_key_exists(2, $pieces)) {
            // Operator
            if(!in_array($pieces[2], ["===","==","!=","!==",">=","<="])) {
                $this->throwException(sprintf('Operator %s not supported', $pieces[2]));
            }

            $statement[]   =   $pieces[2];

            if(!array_key_exists(3, $pieces)) {
                $this->throwException("Missing right operand");
            }

            $statement[]    =   $this->resolveOperand($pieces[3], "right", false);
        }

        $this->clauses["ifs"]++;
        return sprintf('<?php if(%s) { ?>', implode(" ", $statement));
    }

    /**
     * @return string
     * @throws \Comely\KnitException
     */
    private function parseElseIf() : string
    {
        $pieces =   preg_split('/\s/', $this->token);
        $statement[]    =   $this->resolveOperand($pieces[1], "left", true);

        if(array_key_exists(2, $pieces)) {
            // Operator
            if(!in_array($pieces[2], ["===","==","!=","!==",">=","<="])) {
                $this->throwException(sprintf('Operator %s not supported', $pieces[2]));
            }

            $statement[]   =   $pieces[2];

            if(!array_key_exists(3, $pieces)) {
                $this->throwException("Missing right operand");
            }

            $statement[]    =   $this->resolveOperand($pieces[3], "right", false);
        }

        $this->clauses["ifs"]++;
        return sprintf('<?php } elseif(%s) { ?>', implode(" ", $statement));
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
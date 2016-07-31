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

namespace Comely\Knit\Compiler;

use Comely\Knit;
use Comely\KnitException;

/**
 * Class AbstractParser
 * @package Comely\Knit\Compiler
 */
abstract class AbstractParser
{
    private $clauses;
    private $line;
    private $lineNum;
    private $literalMode;
    private $modifiers;
    private $reserved;
    private $token;

    use Knit\Compiler\Parser\CountTrait;
    use Knit\Compiler\Parser\ForeachTrait;
    use Knit\Compiler\Parser\IfTrait;
    use Knit\Compiler\Parser\IncludeTrait;
    use Knit\Compiler\Parser\PrintTrait;
    use Knit\Compiler\Parser\VariablesTrait;

    /**
     * AbstractParser constructor.
     */
    public function __construct()
    {
        $this->clauses  =   ["counts" => [], "foreach" => [], "ifs" => 0];
        $this->line =   null;
        $this->lineNum  =   0;
        $this->literalMode  =   false;
        $this->modifiers    =   $this->compiler->getModifiers();
        $this->reserved =   $this->compiler->getReservedVariables();
        $this->token    =   null;

        // Parse template
        $this->parse();
    }

    /**
     * @throws KnitException
     */
    public function parse()
    {
        // Timer start
        $timerStart =   microtime(true);

        // Parse line by line
        $lines  =   preg_split("/(\r\n|\n|\r)/", $this->source);
        foreach($lines as $line) {
            $this->line =   $line;
            $this->lineNum++;
            $this->parseLine();

            // Append parsed body
            $this->parsed   .=  Knit::EOL;
            $this->parsed   .=  $this->line;
        }

        // Finish
        $this->timer    =   microtime(true)-$timerStart;
        unset($this->source);
    }

    /**
     * Parse a line
     */
    private function parseLine()
    {
        // Capture Knit codes within delimiters
        $this->line =   preg_replace_callback(
            sprintf(
                '/%s([^\s].+)%s/U',
                preg_quote($this->delimiters[0], "/"),
                preg_quote($this->delimiters[1], "/")
            ),
            function ($matches) {
                $match  =   $matches[0] ?? null; // Full match
                $this->token    =   $matches[1] ?? null; // Removed delimiters
                if($this->token) {
                    // Replace all multiple spaces with single
                    $this->token    =   preg_replace("/\s+/", " ", $this->token);
                    // Check if in literal mode
                    if(!$this->literalMode) {
                        if(preg_match('/^\$[a-z\_][a-z0-9\_\.\|\:\'\$]+$/i', $this->token)) {
                            return $this->parsePrint();
                        } elseif(preg_match('/^if\s.+$/i', $this->token)) {
                            return $this->parseIf();
                        } elseif(preg_match('/^elseif\s.+$/i', $this->token)) {
                            return $this->parseElseIf();
                        } elseif(strtolower($this->token) === "else") {
                            return $this->parseIfElse();
                        } elseif(strtolower($this->token) === "/if") {
                            return $this->parseIfClose();
                        } elseif(preg_match('/^foreach\s\$[a-z\_]+[a-z0-9\_\.]*\sas\s\$[a-z]+[a-z0-9\_]*$/i', $this->token)) {
                            return $this->parseForeach();
                        } elseif(strtolower($this->token) === "foreachelse") {
                            return $this->parseForeachElse();
                        } elseif(strtolower($this->token) === "/foreach") {
                            return $this->parseForeachClose();
                        } elseif(preg_match('/^count\s\$[a-z\_]+[a-z0-9\_]*\s[1-9][0-9]*\sto\s[1-9][0-9]*$/i', $this->token)) {
                            return $this->parseCount();
                        } elseif(strtolower($this->token) === "/count") {
                            return $this->parseCountClose();
                        } elseif(preg_match('/^include\sknit=(\'|\")[a-z0-9-_.]+(\'|\")$/i', $this->token)) {
                            return $this->parseIncludeKnit();
                        } elseif(strtolower($this->token) === "literal") {
                            $this->literalMode   =   true;
                            return "";
                        } else {
                            // Syntax error, Throw exception
                            $this->throwException("Bad/incomplete syntax");
                        }
                    } else {
                        if(strtolower($this->token)   === "/literal") {
                            $this->literalMode    =   false;
                            return "";
                        }
                    }
                }

                return $match;
            },
            $this->line
        );
    }

    /**
     * @param string $message
     * @throws KnitException
     */
    private function throwException(string $message)
    {
        throw KnitException::parseError(
            sprintf(
                'Parsing error "%1$s" in template file "%2$s" on line # %4$d near "%5$s"',
                $message,
                basename($this->file),
                $this->file,
                $this->lineNum,
                substr($this->token, 0, 16) . "..."
            )
        );
    }
}
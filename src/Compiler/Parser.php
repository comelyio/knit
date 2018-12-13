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

namespace Comely\Knit\Compiler;

use Comely\IO\FileSystem\Disk\Directory;
use Comely\Knit\Compiler\Parser\ParseCount;
use Comely\Knit\Compiler\Parser\ParseForeach;
use Comely\Knit\Compiler\Parser\ParseIf;
use Comely\Knit\Compiler\Parser\ParseImport;
use Comely\Knit\Compiler\Parser\ParsePrint;
use Comely\Knit\Compiler\Parser\Variables;
use Comely\Knit\Exception\CompilerException;
use Comely\Knit\Exception\ModifierException;
use Comely\Knit\Exception\ParseException;
use Comely\Knit\Knit;

/**
 * Class Parser
 * @package Comely\Knit\Compiler
 */
class Parser
{
    /** @var Knit */
    private $knit;
    /** @var Directory */
    private $directory;
    /** @var string */
    private $source;
    /** @var array */
    private $clauses;
    /** @var bool */
    private $literal;
    /** @var Variables */
    private $reserved;
    /** @var int */
    private $line;
    /** @var null|string */
    private $token;

    use ParseCount;
    use ParseForeach;
    use ParseIf;
    use ParseImport;
    use ParsePrint;

    /**
     * Parser constructor.
     * @param Knit $knit
     * @param string $source
     * @param Variables|null $reserved
     * @param Directory|null $directory
     */
    public function __construct(Knit $knit, string $source, ?Variables $reserved = null, ?Directory $directory = null)
    {
        $this->knit = $knit;
        $this->directory = $directory ?? $this->knit->directories()->_templates;
        $this->source = $source;
        $this->clauses = ["count" => [], "foreach" => [], "if" => 0];
        $this->line = 0;
        $this->literal = false;
        $this->reserved = $reserved ?? new Variables();
    }

    /**
     * @return string
     */
    public function parse(): string
    {
        $parsed = "";
        $lines = preg_split("/(\r\n|\n|\r)/", $this->source); // Split line-breaks
        foreach ($lines as $line) {
            $parsed .= $this->line($line) . PHP_EOL;
        }

        return $parsed;
    }

    /**
     * @param string $line
     * @return null|string
     */
    private function line(string $line): ?string
    {
        $this->line++;
        return preg_replace_callback(
            '/\{([^\s].+)\}/U',
            function ($matches) {
                return $this->tokens($matches);
            },
            $line
        );
    }

    /**
     * @param array $matches
     * @return string
     * @throws CompilerException
     * @throws ParseException
     */
    private function tokens(array $matches): string
    {
        $this->token = $matches[1] ?? null; // Without delimiters
        if ($this->token) {
            $this->token = preg_replace('/\s+/', ' ', $this->token);  // Remove multiple spacers
            // Literal mode?
            if (!$this->literal) {
                if (preg_match('/^\$.*$/i', $this->token)) {
                    // Match anything starting with $ sign
                    return $this->parsePrint();
                } elseif (preg_match('/^if\s.+$/i', $this->token)) {
                    return $this->parseIf(false);
                } elseif (preg_match('/^elseif\s.+$/i', $this->token)) {
                    return $this->parseIf(true);
                } elseif (strtolower($this->token) === "else") {
                    return $this->parseIfElse();
                } elseif (strtolower($this->token) === "/if") {
                    return $this->parseIfClose();
                } elseif (preg_match('/^foreach\s\$[a-z\_]+[a-z0-9\_\.]*\sas\s\$[a-z]+[a-z0-9\_]*$/i', $this->token)) {
                    return $this->parseForeach();
                } elseif (preg_match('/^foreach\s\$[a-z\_]+[a-z0-9\_\.]*\sas\s\$[a-z]+[a-z0-9\_]*\s\=\>\s\$[a-z]+[a-z0-9\_]*$/i', $this->token)) {
                    return $this->parseForeachPaired();
                } elseif (strtolower($this->token) === "foreachelse") {
                    return $this->parseForeachElse();
                } elseif (strtolower($this->token) === "/foreach") {
                    return $this->parseForeachClose();
                } elseif (preg_match('/^count\s\$[a-z\_]+[a-z0-9\_]*\s[1-9][0-9]*\sto\s[1-9][0-9]*$/i', $this->token)) {
                    return $this->parseCount();
                } elseif (strtolower($this->token) === "/count") {
                    return $this->parseCountClose();
                } elseif (preg_match('/^knit\s?(\'|\")[a-z0-9-_.\/]+(\'|\")$/i', $this->token)) {
                    return $this->parseImport();
                } elseif (strtolower($this->token) === "literal") {
                    $this->literal = true;
                    return "";
                } else {
                    // Syntax error, Throw exception
                    throw $this->exception('Incomplete or bad syntax');
                }
            } else {
                if (strtolower($this->token) === "/literal") {
                    $this->literal = false;
                    return "";
                }
            }
        }

        return "";
    }

    /**
     * @param string $var
     * @return string
     * @throws ParseException
     */
    private function variable(string $var): string
    {
        // Split variable and modifiers
        $modifiers = explode("|", trim($var));
        $var = $modifiers[0];
        $varName = $var;
        unset($modifiers[0]);

        /*
         * ---
         * Syntax Info:
         * ---
         * // Variable may start from a-z or a underscore, and may be followed by a-z, 0-9 and/or more underscores
         * $var = '\$[a-z\_]+[a-z0-9\_]*';
         * // Box brackets may have single property name,
         * // OR variable with possibility to use "." to call sub-properties
         * $bracket = '\[([a-z0-9\_\-]+|' . $var . '(\.[a-z0-9\_\-]+(\[[a-z0-9\_\-]+\])?)*)\]';
         * // Property may have a bracket
         * $prop = '\.[a-z0-9\_\-]+' . '(' . $bracket . ')?';
         * // Main variable may have a directly succeeding bracket or number of properties
         * $pattern = '/^' . $var . '(' . $bracket . ')?(' . $prop . ')*$/i';
         */
        $pattern = '/^\$[a-z\_]+[a-z0-9\_]*(\[([a-z0-9\_\-]+|\$[a-z\_]+[a-z0-9\_]*(\.[a-z0-9\_\-]+(\[[a-z0-9\_\-]+\])?)*)\])?(\.[a-z0-9\_\-]+(\[([a-z0-9\_\-]+|\$[a-z\_]+[a-z0-9\_]*(\.[a-z0-9\_\-]+(\[[a-z0-9\_\-]+\])?)*)\])?)*$/i';
        if (!preg_match($pattern, $var)) {
            if (preg_match('/\[(\|"\')/', $var)) {
                throw $this->exception('Quotes are not allowed inside box brackets');
            }

            throw $this->exception('Bad variable syntax');
        }

        // Check if $var has brackets
        if (strpos($var, "[")) {
            // Normalize non-variable brackets to properties
            $var = preg_replace_callback(
                '/\[([^\$].+)\]/U',
                function ($matched) {
                    $property = $matched[1] ?? null;
                    if ($property) {
                        return "." . $property;
                    }
                },
                $var
            );

            // Check if $var still has brackets
            if (strpos($var, "[")) {
                // We need to resolve inner variables now
                $var = preg_replace_callback(
                    '/\[(.+)\]/U',
                    function ($matched) {
                        $sub = $matched[1] ?? null;
                        if (is_string($sub)) {
                            return "[" . $this->variable($sub) . "]";
                        }
                    },
                    $var
                );
            }
        }

        // Split into pieces
        $props = explode(".", $var);
        $var = $props[0];
        unset($props[0]);

        // Check if it is not a reserved variable
        if (!$this->reserved->has($var)) {
            // Variable has other variables inside?
            if (preg_match('/\[/', $var)) {
                // Get first token
                $varSuffix = explode("[", $var);
                $var = $varSuffix[0];
                unset($varSuffix[0]);
                $varSuffix = implode("[", $varSuffix);
            }

            $var = sprintf("\$this->data['%s']", strtolower(substr($var, 1)));
            if (isset($varSuffix)) {
                $var .= "[" . $varSuffix;
                unset($varSuffix);
            }
        }

        // Assemble properties array style
        foreach ($props as $prop) {
            $bracket = strpos($prop, "[");
            if ($bracket) {
                $var .= sprintf("['%s']%s", substr($prop, 0, $bracket), substr($prop, $bracket));
                continue;
            }

            $var .= sprintf("['%s']", $prop);
        }

        // Modifiers
        $modifiers = implode("|", $modifiers);
        if ($modifiers) {
            /*
             * ---
             * Validate Modifiers
             * ---
             * // Variables enclosed in [] may be parsed as arguments
             * $var = '\:\[\$.+\]';
             * // Integers or floats may be parsed as arguments
             * $num = '\:\-?[0-9]+(\.[0-9]+)?';
             * // Strings enclosed in quotes (' or ") having only a-z, 0-9, space and only .-_ special chars
             * // Strings NOT enclosed in quotes MUST NOT be accepted
             * $str = '\:(\"|\')[a-z0-9\s\.\_\-]+(\"|\')';
             * // Modifier name must match a-z, 0-9 and may have a underscore
             * $modifier = '[a-z0-9\_]+((' . $var . ')|(' . $num . ')|(' . $str . '))*\|?';
             * $pattern = '/^(' . $modifier . ')*$/';
             */
            $pattern = '/^([a-z0-9\_]+((\:\[\$.+\])|(\:\-?[0-9]+(\.[0-9]+)?)|(\:(\"|\')[a-z0-9\s\.\_\-]*(\"|\')))*\|?)*$/i';
            if (!preg_match($pattern, $modifiers)) {
                throw $this->exception(
                    sprintf('Incomplete or bad variable modifiers syntax for "%s"', $varName)
                );
            }

            // Check if has variables enclosed as arguments
            if (strpos($modifiers, ':[$')) {
                $modifiers = preg_replace_callback(
                    '/\[(\$.+)\]/U',
                    function ($modifierVar) {
                        $modifierVar = $modifierVar[1] ?? null;
                        if (is_string($modifierVar)) {
                            return $this->variable($modifierVar);
                        }
                    },
                    $modifiers
                );
            }

            $modifiers = explode("|", $modifiers);
            foreach ($modifiers as $modifier) {
                if ($modifier) {
                    // Split arguments
                    $args = explode(":", $modifier);
                    $modifierName = strtolower($args[0]);
                    unset($args[0]);
                    // Process arguments
                    $arguments = [];
                    foreach ($args as $arg) {
                        if ($arg === "null") {
                            $arguments[] = null;
                        } elseif (preg_match('/^\-?[0-9]+(\.[0-9]+)?$/', $arg)) {
                            $arguments[] = strpos($arg, ".") ? floatval($arg) : intval($arg);
                        } elseif ($arg === "true") {
                            $arguments[] = true;
                        } elseif ($arg === "false") {
                            $arguments[] = false;
                        } elseif (preg_match('/^(\'|\")[a-z0-9\s\.\_\-]+(\'|\")$/i', $arg)) {
                            $arguments[] = "'" . substr($arg, 1, -1) . "'";
                        } else {
                            $arguments[] = $arg; // append as-is
                        }
                    }

                    unset($args);

                    try {
                        $modifier = $this->knit->modifiers()->get($modifierName);
                        if (!$modifier instanceof \Closure) {
                            throw $this->exception(
                                sprintf('Modifier "%s" not found, no such modifier was registered', $modifierName)
                            );
                        }

                        /**
                         * Call modifier function
                         * ---
                         * @param string $var
                         * @param array $arguments
                         */
                        $var = call_user_func_array($modifier, [$var, $arguments]);
                    } catch (ModifierException $e) {
                        // Append line and near part in modifier's exception
                        throw $this->exception($e->getMessage());
                    }
                }
            }
        }


        return $var;
    }

    /**
     * @param string $var
     * @throws ParseException
     */
    private function reserveVariable(string $var): void
    {
        try {
            $this->reserved->add($var);
        } catch (\Exception $e) {
            throw $this->exception($e->getMessage());
        }
    }

    /**
     * @param string $var
     */
    private function releaseVariable(string $var): void
    {
        $this->reserved->delete($var);
    }

    /**
     * @param string $message
     * @return ParseException
     */
    private function exception(string $message): ParseException
    {
        return new ParseException($message, $this->line, $this->token);
    }
}
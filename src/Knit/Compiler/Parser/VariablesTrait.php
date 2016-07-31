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
 * Class VariablesTrait
 * @package Comely\Knit\Compiler\Parser
 */
trait VariablesTrait
{
    /**
     * Resolves a variable and applies modifiers
     * @param string $input
     * @return string
     */
    private function resolveVariable(string $input) : string
    {
        // Split
        $modifiers  =   explode("|", trim($input));
        $var    =   $modifiers[0];
        unset($modifiers[0]);

        // Resolve var
        if(!preg_match('/^\$[a-z\_][a-z0-9\_\.]+$/i', $var)) {
            $this->throwException('Bad variable syntax');
        }

        // Explode var into pieces (Array)
        $pieces =   explode(".", $var);
        $var    =   $pieces[0];
        unset($pieces[0]);

        // Check if its reserved variable (i.e. being used by foreach/count clause)
        if(!$this->reserved->has($var)) {
            // Load from assigned data
            $var    =   sprintf('$this->data["%s"]', strtolower(substr($var, 1)));
        }

        // Assemble pieces array style
        foreach($pieces as $piece) {
            $var    .=  sprintf('["%s"]', $piece);
        }

        // Apply modifiers
        foreach($modifiers as $modifier) {
            $opts   =   explode(":", $modifier);
            $modifier   =   $opts[0];
            unset($opts[0]);

            // Check modifier args
            $opts   =   array_values($opts);
            $optsCount  =   count($opts);
            for($i=0;$i<$optsCount;$i++) {
                if(preg_match('/^\$[a-z\_][a-z0-9\_\.]+$/i', $opts[$i])) {
                    // Variable
                    $opts[$i] = $this->resolveVariable($opts[$i]);
                } elseif(preg_match('/^[0-9][\.0-9]*$/', $opts[$i])) {
                    // Integer or float
                    $cast   =   strpos($opts[$i], ".") ? "floatval" : "intval";
                    $opts[$i]   =   call_user_func($cast, $opts[$i]);
                } elseif(in_array(strtolower($opts[$i]), ["true","false"])) {
                    // Boolean
                    $opts[$i]   =   boolval($opts[$i]);
                } elseif(strtolower($opts[$i])  === "null") {
                    // NULL
                    $opts[$i]   =   null;
                } else {
                    // String
                    $opts[$i]   =   str_replace(["'",'""'], "", $opts[$i]); // Remove quotes
                }
            }

            try {
                $modifierInstance   =   $this->modifiers->pull(
                    $modifier,
                    function($repo, $key) {
                        // Modifier not instantiated yet
                        $modifierClass  =   sprintf(
                            'Comely\\Knit\\Modifiers\\%s',
                            \Comely::pascalCase($key)
                        );

                        if(!class_exists($modifierClass)) {
                            $this->throwException(sprintf("Modifier '%s' not found", $key));
                        }

                        $modifier   =   new $modifierClass;
                        $repo->push($modifier, $key);
                        return $modifier;
                    }
                );
                
                $var    =   call_user_func_array([$modifierInstance,"apply"], [$var,$opts]);
            } catch(\Throwable $e) {
                $this->throwException(sprintf("Modifier '%s' %s", $modifier, $e->getMessage()));
            }
        }

        return $var;
    }

    /**
     * Reserves a variable
     * @param string $var
     */
    private function reserveVariable(string $var)
    {
        if($this->reserved->has($var)) {
            $this->throwException(
                sprintf(
                    'variable %s is reserved by a previous count/foreach clause, use a different name',
                    $var
                )
            );
        }

        if(in_array(strtolower($var), ['$this'])) {
            $this->throwException(sprintf('Variable %s cannot be used by count/foreach clauses', $var));
        }

        // Reserve variable
        $this->reserved->add($var);
    }

    /**
     * Releases a variable
     * @param string $var
     */
    private function releaseVariable(string $var)
    {
        $this->reserved->delete($var);
    }
}
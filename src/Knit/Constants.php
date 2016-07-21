<?php
declare(strict_types=1);

namespace Comely\Knit;

/**
 * Interface Constants
 * @package Comely\Knit
 */
interface Constants
{
    const VERSION   =   "1.0.1-beta";
    const DS    =   DIRECTORY_SEPARATOR;
    const EOL   =   PHP_EOL;

    const FILES =   ["knit", "tpl"];
    const DELIMITERS    =   ["{", "}"];

    const CACHE_STATIC  =   2;
    const CACHE_DYNAMIC =   4;
}
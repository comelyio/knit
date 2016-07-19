<?php
declare(strict_types=1);

namespace Comely\Knit;

/**
 * Interface Constants
 * @package Comely\Knit
 */
interface Constants
{
    const DS    =   DIRECTORY_SEPARATOR;
    const EOL   =   PHP_EOL;

    const FILES =   ["knit", "tpl"];
    const DELIMITERS    =   ["{", "}"];
}
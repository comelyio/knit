<?php
declare(strict_types=1);

namespace Comely\Knit\Modifiers;

use Comely\Knit\AbstractModifier;

/**
 * Class Trim
 * @package Comely\Knit\Modifiers
 */
class Trim extends AbstractModifier
{
    const CALL  =   "trim";
    const MIN_ARGS  =   0;
    const MAX_ARGS  =   1;
    const ARGS  =   [
        ["str", " \t\n\r\0\x0B"]
    ];
}
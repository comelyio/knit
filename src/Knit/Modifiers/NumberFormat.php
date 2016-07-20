<?php
declare(strict_types=1);

namespace Comely\Knit\Modifiers;

use Comely\Knit\AbstractModifier;

/**
 * Class NumberFormat
 * @package Comely\Knit\Modifiers
 */
class NumberFormat extends AbstractModifier
{
    const CALL  =   "number_format";
    const MIN_ARGS  =   1;
    const MAX_ARGS  =   3;
    const ARGS  =   [
        ["int", 0],
        ["str", "."],
        ["str", ","]
    ];
}
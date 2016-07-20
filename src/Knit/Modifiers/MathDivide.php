<?php
declare(strict_types=1);

namespace Comely\Knit\Modifiers;

use Comely\Knit\AbstractModifier;

/**
 * Class MathDivide
 * @package Comely\Knit\Modifiers
 */
class MathDivide extends AbstractModifier
{
    const CALL  =   "Comely\\Knit\\Library\\Arithmetic::divide";
    const MIN_ARGS  =   1;
    const MAX_ARGS  =   1;
    const ARGS  =   [
        ["~"]
    ];
}
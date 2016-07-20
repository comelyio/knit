<?php
declare(strict_types=1);

namespace Comely\Knit\Modifiers;

use Comely\Knit\AbstractModifier;

/**
 * Class MathMultiply
 * @package Comely\Knit\Modifiers
 */
class MathMultiply extends AbstractModifier
{
    const CALL  =   "Comely\\Knit\\Library\\Arithmetic::multiply";
    const MIN_ARGS  =   1;
    const MAX_ARGS  =   1;
    const ARGS  =   [
        ["~"]
    ];
}
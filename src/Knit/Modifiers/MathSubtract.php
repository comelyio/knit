<?php
declare(strict_types=1);

namespace Comely\Knit\Modifiers;

use Comely\Knit\AbstractModifier;

/**
 * Class MathSubtract
 * @package Comely\Knit\Modifiers
 */
class MathSubtract extends AbstractModifier
{
    const CALL  =   "Comely\\Knit\\Library\\Arithmetic::subtract";
    const MIN_ARGS  =   1;
    const MAX_ARGS  =   1;
    const ARGS  =   [
        ["~"]
    ];
}
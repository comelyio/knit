<?php
declare(strict_types=1);

namespace Comely\Knit\Modifiers;

use Comely\Knit\AbstractModifier;

/**
 * Class MathAdd
 * @package Comely\Knit\Modifiers
 */
class MathAdd extends AbstractModifier
{
    const CALL  =   "Comely\\Knit\\Library\\Arithmetic::add";
    const MIN_ARGS  =   1;
    const MAX_ARGS  =   1;
    const ARGS  =   [
        ["~"]
    ];
}
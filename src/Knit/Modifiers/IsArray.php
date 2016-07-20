<?php
declare(strict_types=1);

namespace Comely\Knit\Modifiers;

use Comely\Knit\AbstractModifier;

/**
 * Class IsArray
 * @package Comely\Knit\Modifiers
 */
class IsArray extends AbstractModifier
{
    const CALL  =   "is_array";
    const MIN_ARGS  =   0;
    const MAX_ARGS  =   0;
    const ARGS  =   [];
}
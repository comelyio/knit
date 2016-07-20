<?php
declare(strict_types=1);

namespace Comely\Knit\Modifiers;

use Comely\Knit\AbstractModifier;

/**
 * Class IsNull
 * @package Comely\Knit\Modifiers
 */
class IsNull extends AbstractModifier
{
    const CALL  =   "is_null";
    const MIN_ARGS  =   0;
    const MAX_ARGS  =   0;
    const ARGS  =   [];
}
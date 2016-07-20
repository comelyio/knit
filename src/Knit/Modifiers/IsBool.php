<?php
declare(strict_types=1);

namespace Comely\Knit\Modifiers;

use Comely\Knit\AbstractModifier;

/**
 * Class IsBool
 * @package Comely\Knit\Modifiers
 */
class IsBool extends AbstractModifier
{
    const CALL  =   "is_bool";
    const MIN_ARGS  =   0;
    const MAX_ARGS  =   0;
    const ARGS  =   [];
}
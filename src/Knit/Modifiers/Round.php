<?php
declare(strict_types=1);

namespace Comely\Knit\Modifiers;

use Comely\Knit\AbstractModifier;

/**
 * Class Round
 * @package Comely\Knit\Modifiers
 */
class Round extends AbstractModifier
{
    const CALL  =   "round";
    const MIN_ARGS  =   1;
    const MAX_ARGS  =   1;
    const ARGS  =   [
        ["int", 0]
    ];
}
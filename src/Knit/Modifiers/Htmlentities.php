<?php
declare(strict_types=1);

namespace Comely\Knit\Modifiers;

use Comely\Knit\AbstractModifier;

/**
 * Class Htmlentities
 * @package Comely\Knit\Modifiers
 */
class Htmlentities extends AbstractModifier
{
    const CALL  =   "htmlentities";
    const MIN_ARGS  =   0;
    const MAX_ARGS  =   3;
    const ARGS  =   [
        ["int", ENT_COMPAT|ENT_HTML5],
        ["str", "UTF-8"],
        ["bool", true]
    ];
}
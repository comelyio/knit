<?php
declare(strict_types=1);

namespace Comely\Knit\Modifiers;

use Comely\Knit\AbstractModifier;

/**
 * Class StripTags
 * @package Comely\Knit\Modifiers
 */
class StripTags extends AbstractModifier
{
    const CALL  =   "strip_tags";
    const MIN_ARGS  =   0;
    const MAX_ARGS  =   1;
    const ARGS  =   [
        ["str", ""]
    ];
}
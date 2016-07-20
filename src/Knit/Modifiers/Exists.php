<?php
declare(strict_types=1);

namespace Comely\Knit\Modifiers;

use Comely\Knit\AbstractModifier;

/**
 * Class Exists
 * @package Comely\Knit\Modifiers
 */
class Exists extends AbstractModifier
{
    const CALL  =   "isset";
    const MIN_ARGS  =   0;
    const MAX_ARGS  =   0;
    const ARGS  =   [];
}
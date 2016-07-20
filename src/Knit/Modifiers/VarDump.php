<?php
declare(strict_types=1);

namespace Comely\Knit\Modifiers;

use Comely\Knit\AbstractModifier;

/**
 * Class VarDump
 * @package Comely\Knit\Modifiers
 */
class VarDump extends AbstractModifier
{
    const CALL  =   "var_dump";
    const MIN_ARGS  =   0;
    const MAX_ARGS  =   0;
    const ARGS  =   [];
}
<?php
declare(strict_types=1);

namespace Comely\Knit\Modifiers;

use Comely\Knit\AbstractModifier;

/**
 * Class Date
 * @package Comely\Knit\Modifiers
 */
class Date extends AbstractModifier
{
    const CALL  =   "date";
    const MIN_ARGS  =   0;
    const MAX_ARGS  =   1;
    const ARGS  =   [
        ["str", "d M Y h:i A"]
    ];

    /**
     * @param string $input
     * @param array $args
     * @return string
     * @throws \Comely\KnitException
     */
    public function apply(string $input, array $args) : string
    {
        $args   =   $this->assertArgs($args);
        return sprintf("%s('%s', %s)", self::CALL, $args[0], $input);
    }
}
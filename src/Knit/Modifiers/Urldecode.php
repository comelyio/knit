<?php
declare(strict_types=1);

namespace Comely\Knit\Modifiers;

use Comely\Knit\AbstractModifier;

/**
 * Class Urldecode
 * @package Comely\Knit\Modifiers
 */
class Urldecode extends AbstractModifier
{
    const CALL  =   null;
    const MIN_ARGS  =   0;
    const MAX_ARGS  =   1;
    const ARGS  =   [
        ["bool", false] // Decode according to RFC 3986?
    ];

    /**
     * @param string $input
     * @param array $args
     * @return string
     */
    public function apply(string $input, array $args) : string
    {
        $args   =   $this->assertArgs($args);
        $call   =   $args[0]    === true ? "rawurldecode" : "urldecode";
        return sprintf('%s(%s)', $call, $input);
    }
}
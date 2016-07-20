<?php
declare(strict_types=1);

namespace Comely\Knit\Modifiers;

use Comely\Knit\AbstractModifier;

/**
 * Class Substr
 * @package Comely\Knit\Modifiers
 */
class Substr extends AbstractModifier
{
    const CALL  =   "substr";
    const MIN_ARGS  =   1;
    const MAX_ARGS  =   2;
    const ARGS  =   [
        ["int"],
        ["int", 0]
    ];

    /**
     * @param string $input
     * @param array $args
     * @return string
     */
    public function apply(string $input, array $args) : string
    {
        $asserted   =   $this->assertArgs($args);
        if(array_key_exists(1, $args)) {
            return sprintf('%s(%s, %d, %d)', self::CALL, $input, $asserted[0], $asserted[1]);
        } else {
            return sprintf('%s(%s, %d)', self::CALL, $input, $asserted[0]);
        }
    }
}
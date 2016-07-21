<?php
declare(strict_types=1);

namespace Comely;

/**
 * Class KnitException
 * @package Comely
 */
class KnitException extends \ComelyException
{
    protected static $componentId   =   "Comely\\Knit";

    /**
     * @param string $method
     * @param string $error
     * @return KnitException
     */
    public static function badPath(string $method, string $error) : self
    {
        return new self($method, $error, 1001);
    }

    /**
     * @param string $method
     * @param string $path
     * @param string $pathMethod
     * @return KnitException
     */
    public static function pathNotSet(string $method, string $path, string $pathMethod) : self
    {
        return new self(
            $method,
            sprintf('"%1$s" path must be set with "%2$s" method', $path, $pathMethod),
            1002
        );
    }

    /**
     * @param string $key
     * @return KnitException
     */
    public static function reservedDataKey(string $key) : self
    {
        return new self("Comely\\Knit::assign", sprintf('Cannot assign reserved key "%1$s"', $key), 1003);
    }

    /**
     * @param string $key
     * @param string $valueType
     * @return KnitException
     */
    public static function badAssignedValue(string $key, string $valueType) : self
    {
        return new self(
            "Comely\\Knit::assign",
            sprintf('Unsupported value type "%2$s" for key "%1$s"', $key, $valueType),
            1004
        );
    }

    /**
     * @param string $error
     * @return KnitException
     */
    public static function readError(string $error) : self
    {
        return new self(self::$componentId, $error, 1005);
    }

    /**
     * @param string $error
     * @return KnitException
     */
    public static function parseError(string $error) : self
    {
        return new self(self::$componentId, $error, 1006);
    }

    /**
     * @param string $error
     * @return KnitException
     */
    public static function sandBoxError(string $error) : self
    {
        return new self(self::$componentId . "\\Sandbox", $error, 1007);
    }

    /**
     * @param string $error
     * @return KnitException
     */
    public static function cacheError(string $error) : self
    {
        return new self(self::$componentId, $error, 1008);
    }
}
<?php
declare(strict_types=1);

namespace Comely\Knit;

use Comely\IO\Session\ComelySession\Proxy;
use Comely\KnitException;

/**
 * Class Data
 * @package Comely\Knit
 */
class Data
{
    private $data;
    private $internal;
    private $internalKeys;

    /**
     * Data constructor.
     */
    public function __construct()
    {
        $this->data =   [];
        $this->internalKeys =   ["comely", "app"];
        $this->internal =   [
            "now"   =>  time(),
            "this"  =>  "",
            "get"   =>  isset($_GET) ? $_GET : [],
            "post"  =>  isset($_POST) ? $_POST : [],
            "session"   =>  isset($_SESSION) ? $_SESSION : []
        ];
    }

    /**
     * @param Proxy $comelySession
     * @return Data
     */
    public function useComelySession(Proxy $comelySession) : self
    {
        $this->internal["session"]  =   $comelySession->getBags()->getArray();
        return $this;
    }

    /**
     * @param string $key
     * @param $value
     * @throws KnitException
     */
    public function set(string $key, $value)
    {
        // Lowercase key
        $key    =   strtolower($key);

        // Check if key is reserved
        if(in_array($key, $this->internalKeys)) {
            throw KnitException::reservedDataKey($key);
        }

        // Check value type
        if(is_object($value)) {
            // Convert Object to Array
            $value  =   json_decode(json_encode($value), true);
        }
        
        if(!is_scalar($value) &&  !is_null($value)  &&  !is_array($value)) {
            // Unsupported value type
            throw KnitException::badAssignedValue($key, gettype($value));
        }

        // Assign
        $this->data[$key]   =   $value;
    }

    /**
     * @param string $key
     * @return bool
     */
    public function get(string $key)
    {
        $key    =   strtolower($key);
        return $this->data[$key] ?? null;
    }

    /**
     * @param string $key
     * @return bool
     */
    public function __get(string $key)
    {
        return $this->get($key);
    }

    /**
     * Purge all stored key/value pairs
     */
    public function flush()
    {
        $this->data =   [];
    }
}
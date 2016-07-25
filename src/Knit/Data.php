<?php
/**
 * This file is part of Knit package.
 * https://github.com/comelyio/knit
 *
 * Copyright (c) Furqan Ahmed Siddiqui
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code or visit following link:
 * https://github.com/comelyio/knit/blob/master/LICENSE
 */

declare(strict_types=1);

namespace Comely\Knit;

use Comely\KnitException;

/**
 * Class Data
 * @package Comely\Knit
 */
class Data
{
    private $data;
    private $reservedKeys;

    /**
     * Data constructor.
     */
    public function __construct()
    {

        $this->reservedKeys =   ["comely", "app"];
        $this->data =   [
            "comely"    =>   [
                "now"   =>  time(),
                "this"  =>  "",
                "get"   =>  isset($_GET) ? $_GET : [],
                "post"  =>  isset($_POST) ? $_POST : [],
                "session"   =>  isset($_SESSION) ? $_SESSION : []
            ]
        ];
        $this->data["app"]  =   &$this->data["comely"];
    }

    /**
     * @param array $data
     * @return Data
     */
    public function setSessionData(array &$data) : self
    {
        $this->data["comely"]["session"]  =   $data;
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
        if(in_array($key, $this->reservedKeys)) {
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
     * @return array
     */
    public function getArray() : array
    {
        return $this->data;
    }

    /**
     * Purge all stored key/value pairs
     */
    public function flush()
    {
        $this->data =   [];
    }
}
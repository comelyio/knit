<?php
declare(strict_types=1);

namespace Comely\Knit\Traits;

use Comely\Knit;
use Comely\KnitException;

/**
 * Class DataTrait
 * @package Comely\Knit\Traits
 */
trait DataTrait
{
    protected $data;

    /**
     * @param string $key
     * @param $value
     * @return Knit
     * @throws KnitException
     */
    public function assign(string $key, $value) : Knit
    {
        $this->data->set($key, $value);
        return $this;
    }

    /**
     * @return Knit
     */
    public function flushData() : Knit
    {
        $this->data->flush();
    }
}
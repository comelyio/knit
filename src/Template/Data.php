<?php
/**
 * This file is part of Knit package.
 * https://github.com/comelyio/knit
 *
 *  Copyright (c) 2018 Furqan A. Siddiqui <hello@furqansiddiqui.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code or visit following link:
 * https://github.com/comelyio/knit/blob/master/LICENSE
 */

declare(strict_types=1);

namespace Comely\Knit\Template;

use Comely\Knit\Exception\TemplateException;

/**
 * Class Data
 * @package Comely\Knit\Template
 */
class Data implements \Iterator
{
    private const RESERVED = ["knit"];

    /** @var array */
    private $data;

    /**
     * Data constructor.
     */
    public function __construct()
    {
        $this->data = [];
        $this->data["knit"] = [
            "now" => time(),
            "get" => $_GET
        ];
    }

    /**
     * @return void
     */
    public function rewind(): void
    {
        reset($this->data);
    }

    /**
     * @return void
     */
    public function next(): void
    {
        next($this->data);
    }

    /**
     * @return mixed
     */
    public function current()
    {
        return current($this->data);
    }

    /**
     * @return string
     */
    public function key(): string
    {
        return key($this->data);
    }

    /**
     * @return bool
     */
    public function valid(): bool
    {
        return is_null(key($this->data)) ? false : true;
    }

    /**
     * @param string $key
     * @param $value
     * @throws TemplateException
     */
    public function push(string $key, $value): void
    {
        $key = strtolower($key); // Case-insensitivity
        if (!preg_match('/^[a-z0-9\_]+$/', $key)) {
            throw new TemplateException('Trying to assign data with an invalid key');
        }

        if (in_array($key, self::RESERVED)) {
            throw new TemplateException(sprintf('Data cannot be assigned to reserved key "%s"', $key));
        }

        $valueType = gettype($value);
        switch ($valueType) {
            case "boolean":
            case "integer":
            case "double":
            case "string":
            case "NULL":
                $this->data[$key] = $value;
                return;
            case "array":
            case "object":
                $filtered = json_decode(json_encode($valueType), true);
                if (!is_array($filtered)) {
                    throw new TemplateException(
                        sprintf('Failed to assign "%s" value to key "%s"', $valueType, $key)
                    );
                }
                $this->data[$key] = $value;
                return;
            default:
                throw new TemplateException(
                    sprintf('Cannot assign data type "%s" to key "%s"', $valueType, $key)
                );

        }
    }
}
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

namespace Comely\Knit;

use Comely\Kernel\Toolkit\Number;
use Comely\Knit\Exception\CachingException;

/**
 * Class Caching
 * @package Comely\Knit
 * @property null|string $_sessionId
 * @property int $_type
 * @property int $_ttl
 */
class Caching
{
    public const NONE = 1000;
    public const NORMAL = 1001;
    public const AGGRESSIVE = 1002;

    /** @var int */
    private $type;
    /** @var int */
    private $ttl;
    /** @var null|string */
    private $sessionId;

    /**
     * Caching constructor.
     */
    public function __construct()
    {
        $this->disable();
        $this->ttl = 0;
    }

    /**
     * @param $prop
     * @return int|null|string
     * @throws CachingException
     */
    public function __get($prop)
    {
        switch ($prop) {
            case "_sessionId":
                return $this->sessionId;
            case "_type":
                return $this->type;
            case "_ttl":
                return $this->ttl;
        }

        throw new CachingException('Cannot read inaccessible property');
    }

    /**
     * @param $prop
     * @param $value
     * @throws CachingException
     */
    public function __set($prop, $value)
    {
        throw new CachingException('Cannot write inaccessible property');
    }

    /**
     * @param int $seconds
     * @return Caching
     * @throws CachingException
     */
    public function ttl(int $seconds): self
    {
        if (!Number::Range($seconds, 0, PHP_INT_MAX)) {
            throw new CachingException('Invalid caching TTL value');
        }

        $this->ttl = $seconds;
        return $this;
    }

    /**
     * @return Caching
     */
    public function disable(): self
    {
        $this->type = self::NONE;
        $this->sessionId = null;
        return $this;
    }

    /**
     * @param string $sessionId
     * @return $this
     */
    public function aggressive(string $sessionId)
    {
        $this->disable();
        $this->type = self::AGGRESSIVE;
        $this->sessionId = $sessionId;
        return $this;
    }

    /**
     * @return Caching
     */
    public function enable(): self
    {
        $this->disable();
        $this->type = self::NONE;
        return $this;
    }
}
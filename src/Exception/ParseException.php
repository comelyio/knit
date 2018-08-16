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

namespace Comely\Knit\Exception;

/**
 * Class ParseException
 * @package Comely\Knit\Exception
 */
class ParseException extends CompilerException
{
    /** @var int */
    private $lineNum;
    /** @var null|string */
    private $token;

    /**
     * ParseException constructor.
     * @param string $message
     * @param int $line
     * @param string $token
     */
    public function __construct(string $message = "", int $line = 0, ?string $token = null)
    {
        $this->lineNum = $line;
        if ($token) {
            $this->token = substr($token, 0, 16) . "...";
        }

        parent::__construct($message);
    }

    /**
     * @return int
     */
    public function line(): int
    {
        return $this->lineNum;
    }

    /**
     * @return null|string
     */
    public function token(): ?string
    {
        return $this->token;
    }
}
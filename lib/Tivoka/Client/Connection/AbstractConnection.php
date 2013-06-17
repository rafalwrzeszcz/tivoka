<?php
/**
 * Tivoka - JSON-RPC done right!
 * Copyright (c) 2011-2013 by Marcel Klehr <mklehr@gmx.net>
 *
 * MIT LICENSE
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @package  Tivoka
 * @author Marcel Klehr <mklehr@gmx.net>
 * @author Rafał Wrzeszcz <rafal.wrzeszcz@wrzasq.pl>
 * @copyright (c) 2011-2013, Marcel Klehr
 */

namespace Tivoka\Client\Connection;

use Tivoka\Client\NativeInterface;
use Tivoka\Exception;
use Tivoka\Spec\SpecInterface;
use Tivoka\Transport\Notification;
use Tivoka\Transport\Request;

/**
 * JSON-RPC connection
 * @package Tivoka
 */
abstract class AbstractConnection implements ConnectionInterface
{
    /**
     * Initial timeout value.
     * @var int
     */
    const DEFAULT_TIMEOUT = 5;

    /**
     * Timeot.
     * @var int
     */
    protected $timeout = self::DEFAULT_TIMEOUT;

    /**
     * @var SpecInterface
     */
    public $spec;

    /**
     * Sets the spec version to use for this connection
     * @param SpecInterface $spec JSON-RPC specification handler
     * @return self Self instance
     */
    public function useSpec(SpecInterface $spec)
    {
        $this->spec = $spec;
        return $this;
    }

    /**
     * Changes timeout.
     * @param int $timeout
     * @return Self reference.
     */
    public function setTimeout($timeout)
    {
    	$this->timeout = $timeout;

    	return $this;
    }

    /**
     * Send a request directly
     * @param string $method
     * @param array|null $params
     */
    public function sendRequest($method, array $params = null)
    {
        $request = new Request($method, $params);
        return $this->send($request);
    }

    /**
     * Send a notification directly
     * @param string $method
     * @param array|null $params
     */
    public function sendNotification($method, array $params = null)
    {
        $this->send(new Notification($method, $params));
    }

    /**
     * Creates a native remote interface for the target server
     * @return Tivoka\Client\NativeInterface
     */
    public function getNativeInterface()
    {
        return new NativeInterface($this);
    }
}

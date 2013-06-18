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
     * @return NativeInterface
     */
    public function getNativeInterface()
    {
        return new NativeInterface($this);
    }

    /**
     * @return Tivoka\Transport\Response
     */
    abstract protected function createResponse();

    /**
     * @param Request $request
     * @return string JSON-RPC request
     */
    protected function buildRequest(Request $request)
    {
        if (func_num_args() > 1 ) {
            $request = func_get_args();
        }
        if (is_array($request)) {
            //TODO:BEGIN
            $request = new BatchRequest($request);
        }

        // build request data
        $request = $this->spec->prepareRequest($request);
        $request = $this->encoder->encode($request);
        return $request;
    }

    /**
     * @param string $response JSON response from server
     * @return Tivoka\Transport\Response
     */
    protected function interpretResponse($response)
    {
        //no response?
        if (trim($response) == '') {
            throw new Exception\ConnectionException('No response received');
        }
    
        //decode
        $response = $this->encoder->decode($response);
        if ($response == NULL) {
            throw new Exception\SyntaxException('Invalid response encoding');
        }

        $response = $this->spec->interpretResponse($response, $this->createResponse());

        // perform additional checks
        if ($request->id != $response->id) {
            // note - due to single-thread nature of PHP we need to act it as an exception
            // however this can really happen, since JSON-RPC is, by-desing, asynchronous!
            //TODO: use different class, it's not a syntax error
            throw new Exception\SyntaxException('Response ID did not match request ID');
        }

        return $response;
    }
}

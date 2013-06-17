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

namespace Tivoka;

use Tivoka\Client\Connection\Http;
use Tivoka\Client\Connection\Tcp;
use Tivoka\Encoder\EncoderInterface;
use Tivoka\Encoder\StandardEncoder;
use Tivoka\Spec\SpecInterface;
use Tivoka\Transport\Notification;
use Tivoka\Transport\Request;

/**
 * The public interface to all tivoka functions
 * @package Tivoka
 */
abstract class Client
{
    /**
     * Initializes a Connection to a remote server
     * @param mixed $target Remote end-point definition
     * @param EncoderInterface $encoder JSON handler serializer and unserializer
     * @param SpecInterface|string|int $spec JSON-RPC version to use (can be string, numeric version, or existing SpecInterface instance)
     * @return Tivoka\Client\Connection\ConnectionInterface
     */
    public static function connect($target, EncoderInterface $encoder = null, $spec = Tivoka::SPEC_2_0)
    {
        // use default encoder
        if (!isset($encoder)) {
            $encoder = new StandardEncoder();
        }

        // unify spec
        if (!$spec instanceof SpecInterface) {
            $spec = Tivoka::getSpec($spec);
        }

        // TCP conneciton is defined as ['host' => $host, 'port' => $port] definition
        if (is_array($target) && isset($target['host'], $target['port'])) {
            $connection = new Tcp($target['host'], $target['port'], $encoder);
        } else {
            // HTTP end-point should be defined just as string
            $connection = new Http($target, $encoder);
        }

        $connection->useSpec($spec);
        return $connection;
    }

    /**
     * Creates a request
     * @param string $method The method to invoke
     * @param array $params The parameters
     * @return Request
     */
    public static function createRequest($method, array $params = null)
    {
        return new Request($method, $params);
    }

    /**
     * Creates a notification
     * @param string $method The method to invoke
     * @param array $params The parameters
     * @return Notification
     */
    public static function createNotification($method, array $params = null)
    {
        return new Notification($method, $params);
    }

    //TODO: refactor
    /**
     * Creates a batch request
     * @param mixed $request either an array of requests or a comma-seperated list of requests
     * @throws Tivoka\Exception\Exception
     * @return Tivoka\Client\BatchRequest
     */
    public static function createBatch($request)
    {
        if(func_num_args() > 1 ) $request = func_get_args();
        if(!is_array($request)) throw new Exception\Exception('Object of invalid data type passed to Tivoka::createBatch.');
        return new Client\BatchRequest($request);
    }
}

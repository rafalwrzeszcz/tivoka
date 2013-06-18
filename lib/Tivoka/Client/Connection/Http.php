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

use Tivoka\Encoder\EncoderInterface;
use Tivoka\Exception;
use Tivoka\Transport\Http\Response;
use Tivoka\Transport\Request;

use Tivoka\Client\BatchRequest;

/**
 * HTTP connection
 * @package Tivoka
 */
class Http extends AbstractConnection
{
    public $target;
    public $headers = array();

    /**
     * Constructs connection
     * @access private
     * @param string $target URL
     * @param EncoderInterface $encoder JSON serialization handler.
     */
    public function __construct($target, EncoderInterface $encoder)
    {
        //validate url...
        if (!filter_var($target, FILTER_VALIDATE_URL, FILTER_FLAG_SCHEME_REQUIRED)) {
            throw new Exception\Exception('Valid URL (scheme://domain[/path][/file]) required.');
        }

        //validate scheme...
        $url = parse_url($target);
        $scheme = strtolower($url['scheme']);
        if ($scheme != 'http' && $scheme != 'https') {
            throw new Exception\Exception('Unknown or unsupported scheme given.');
        }

        parent::__construct($encoder);

        $this->target = $target;
    }

    /**
     * Sets the HTTP headers to use for upcoming send requests
     * @param string label of header
     * @param string value of header
     * @return self Self instance
     */
    public function setHeader($label, $value)
    {
        $this->headers[$label] = $value;
        return $this;
    }

    /**
     * Sends a JSON-RPC request
     * @param Request $request A Tivoka request
     * @return Response
     */
    public function send(Request $request)
    {
        // preparing connection...
        $context = array(
            'http' => array(
                'content' => $this->buildRequest($request),
                'header' => "Content-Type: application/json\r\n".
                            "Connection: Close\r\n",
                'method' => 'POST',
                'timeout' => $this->timeout
            )
        );
        foreach ($this->headers as $label => $value) {
            $context['http']['header'] .= $label . ': ' . $value . "\r\n";
        }
        //sending...
        $response = @file_get_contents($this->target, false, stream_context_create($context));
        if ($response === FALSE) {
            throw new Exception\ConnectionException('Connection to "'.$this->target.'" failed');
        }

        $response = $this->interpretResponse($response);
        $response->setHeaders($http_response_header);
        return $rsponse;
    }

    /**
     * @return Response
     */
    protected function createResponse()
    {
        return new Response();
    }
}

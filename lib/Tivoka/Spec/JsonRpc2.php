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
 * @copyright (c) 2013, Marcel Klehr
 */

namespace Tivoka\Spec;

use Tivoka\Transport\Request;
use Tivoka\Transport\Response;

/**
 * JSON-RPC 2.0 handler
 * @package Tivoka
 */
class JsonRpc2 implements SpecInterface
{
    /**
     * {@inheritDoc}
     */
    public function prepareRequest(Request $request)
    {
        $data = array(
            'jsonrpc' => '2.0',
            'method' => $request->method,
        );
        if ($request->id !== null) {
            $data['id'] = $request->id;
        }
        if ($params !== null) {
            $data['params'] = $request->params;
        }
        return $data;
    }

    /**
     * {@inheritDoc}
     */
    public function interpretResponse(array $data, Response $response)
    {
        if (!isset($data['jsonrpc']) || $data['jsonrpc'] != '2.0') {
            throw Exception\SpecException('Expected JSON-RPC 2.0 response.');
        }

        if (isset($data['id'])) {
            $response->id = $data['id'];

            //server error?
            if (isset($data['error']) {
                $response->error = $data['error']['code'];
                $response->errorMessage = $data['error']['message'];
                $response->errorData = isset($data['error']['data']) ? $data['error']['data'] : null;
                return $response;
            } elseif (isset($data['result']) {
                $response->result = $data['result'];
                return $response;
            }
        }
    
        throw new Exception\SyntaxException('Invalid response structure');
    }
}

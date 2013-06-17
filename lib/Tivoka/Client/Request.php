<?php
/**
 * Tivoka - JSON-RPC done right!
 * Copyright (c) 2011-2012 by Marcel Klehr <mklehr@gmx.net>
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
 * @copyright (c) 2011-2012, Marcel Klehr
 */

namespace Tivoka\Client;
use Tivoka\Exception;
use Tivoka\Tivoka;
use Tivoka\Client\Connection\AbstractConnection;

/**
 * A JSON-RPC request
 * @package Tivoka
 */
class Request
{
    /**
     * Interprets the response
     * @param string $response json data
     * @return void
     */
    public function setResponse($response) {
        $this->response = $response;

        //no response?
        if(trim($response) == '') {
            throw new Exception\ConnectionException('No response received');
        }
    
        //decode
        $resparr = json_decode($response,true);
        if($resparr == NULL) {
            throw new Exception\SyntaxException('Invalid response encoding');
        }
        
        $this->interpretResponse($resparr);
    }

    /**
     * Interprets the parsed response
     * @param array $json_struct
     */
    public function interpretResponse($json_struct) {
        //server error?
        if(($error = self::interpretError($this->spec, $json_struct, $this->id)) !== FALSE) {
            $this->error        = $error['error']['code'];
            $this->errorMessage = $error['error']['message'];
            $this->errorData    = (isset($error['error']['data'])) ? $error['error']['data'] : null;
            return;
        }
    
        //valid result?
        if(($result = self::interpretResult($this->spec, $json_struct, $this->id)) !== FALSE)
        {
            $this->result = $result['result'];
            return;
        }
    
        throw new Exception\SyntaxException('Invalid response structure');
    }

    /**
     * Checks whether the given response is a valid result
     * @param array $assoc The parsed JSON-RPC response as an associative array
     * @param mixed $id The id of the original request
     * @return array the parsed JSON object
     */
    protected static function interpretResult($spec, array $assoc, $id)
    {
        switch($spec) {
            case Tivoka::SPEC_2_0:
                if(isset($assoc['jsonrpc'], $assoc['id']) === FALSE || 
                   !array_key_exists('result', $assoc)) return FALSE;
                if($assoc['id'] !== $id || $assoc['jsonrpc'] != '2.0') return FALSE;
                return array(
                        'id' => $assoc['id'],
                        'result' => $assoc['result']
                );
            case Tivoka::SPEC_1_0:
                if(isset($assoc['result'], $assoc['id']) === FALSE) return FALSE;
                if($assoc['id'] !== $id && $assoc['result'] === null) return FALSE;
                return array(
                    'id' => $assoc['id'],
                    'result' => $assoc['result']
                );
        }
    }
    
    /**
     * Checks whether the given response is valid and an error
     * @param array $assoc The parsed JSON-RPC response as an associative array
     * @param mixed $id The id of the original request
     * @return array parsed JSON object
     */
    protected static function interpretError($spec, array $assoc, $id)
    {
        switch($spec) {
            case Tivoka::SPEC_2_0:
                if(isset($assoc['jsonrpc'], $assoc['error']) == FALSE) return FALSE;
                if($assoc['id'] != $id && $assoc['id'] != null && isset($assoc['id']) OR $assoc['jsonrpc'] != '2.0') return FALSE;
                if(isset($assoc['error']['message'], $assoc['error']['code']) === FALSE) return FALSE;
                return array(
                        'id' => $assoc['id'],
                        'error' => $assoc['error']
                );
            case Tivoka::SPEC_1_0:
                if(isset($assoc['error'], $assoc['id']) === FALSE) return FALSE;
                if($assoc['id'] != $id && $assoc['id'] !== null) return FALSE;
                if(isset($assoc['error']) === FALSE) return FALSE;
                return array(
                    'id' => $assoc['id'],
                    'error' => array('data' => $assoc['error'])
                );
        }
    }
    
    /**
     * Encodes the request properties
     * @param mixed $id The id of the request
     * @param string $method The method to be called
     * @param array $params Additional parameters
     * @return mixed the prepared assotiative array to encode
     */
    protected static function prepareRequest($spec, $id, $method, $params=null) {
        switch($spec) {
        case Tivoka::SPEC_2_0:
            $request = array(
                    'jsonrpc' => '2.0',
                    'method' => $method,
            );
            if($id !== null) $request['id'] = $id;
            if($params !== null) $request['params'] = $params;
            return $request;
        case Tivoka::SPEC_1_0:
            $request = array(
                'method' => $method,
                'id' => $id
            );
            if($params !== null) {
                if((bool)count(array_filter(array_keys($params), 'is_string'))) throw new Exception\SpecException('JSON-RPC 1.0 doesn\'t allow named parameters');
                $request['params'] = $params;
            }
            return $request;
        }
    }
}
?>
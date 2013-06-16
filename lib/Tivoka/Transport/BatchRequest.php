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

namespace Tivoka\Transport;

/**
 * A batch request
 * @package Tivoka
 */
class BatchRequest
{
    protected $id = array();
    protected $requests = array();

    /**
     * Constructs a new JSON-RPC batch request
     * All values of type other than Tivoka\Transport\Request will be ignored
     * @param Request[] $batch A list of requests to include, each a Tivoka_Request
     */
    public function __construct(array $batch)
    {
        // prepare requests...
        foreach($batch as $request)
        {
            if (!$request instanceof Request) {
                continue;
            }

            // request...
            if (!$request instanceof Notification) {
                if (in_array($request->id, $this->id, true)) {
                    continue; // strict compare
                }
                $this->id[$request->id] = $request;
            }

            $this->requests[] = $request;
        }
    }

    //TODO
}

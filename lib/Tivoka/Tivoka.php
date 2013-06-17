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

use Tivoka\Spec\JsonRpc1;
use Tivoka\Spec\JsonRpc2;

/**
 * The public interface to all tivoka functions
 * @package Tivoka
 */
abstract class Tivoka
{
    const SPEC_1_0 = 8;             // 000 001 000
    const SPEC_2_0 = 16;            // 000 010 000

    /**
     * Evaluates and returns the passed JSON-RPC spec version
     * @param string $version spec version as a string (using semver notation)
     * @return int
     */
    public static function getSpecVersion($version)
    {
        switch ($version) {
            case '1.0':
                return static::SPEC_1_0;
            case '2.0':
                return static::SPEC_2_0;
            default:
                throw new Exception\SpecException('Unsupported spec version: ' . $version);
        }
    }

    /**
     * @param int|string $version
     * @return Tivoka\Spec\SpecInterface
     */
    public static function getSpec($version)
    {
        // translate version name to version ID
        if (!is_numeric($version)) {
            $version = static::getSpecVersion($version);
        }

        switch ($version) {
            case static::SPEC_1_0:
                return new JsonRpc1();
            case static::SPEC_2_0:
                return new JsonRpc2();
            default:
                throw new Exception\SpecException('Unsupported spec version: ' . $version);
        }
    }
}

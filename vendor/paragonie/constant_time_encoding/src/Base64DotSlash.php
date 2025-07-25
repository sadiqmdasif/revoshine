<?php

declare (strict_types=1);
namespace _PhpScoper01187d35592a\ParagonIE\ConstantTime;

/**
 *  Copyright (c) 2016 - 2022 Paragon Initiative Enterprises.
 *  Copyright (c) 2014 Steve "Sc00bz" Thomas (steve at tobtu dot com)
 *
 *  Permission is hereby granted, free of charge, to any person obtaining a copy
 *  of this software and associated documentation files (the "Software"), to deal
 *  in the Software without restriction, including without limitation the rights
 *  to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 *  copies of the Software, and to permit persons to whom the Software is
 *  furnished to do so, subject to the following conditions:
 *
 *  The above copyright notice and this permission notice shall be included in all
 *  copies or substantial portions of the Software.
 *
 *  THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 *  IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 *  FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 *  AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 *  LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 *  OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 *  SOFTWARE.
 */
/**
 * Class Base64DotSlash
 * ./[A-Z][a-z][0-9]
 *
 * @package ParagonIE\ConstantTime
 */
abstract class Base64DotSlash extends Base64
{
    /**
     * Uses bitwise operators instead of table-lookups to turn 6-bit integers
     * into 8-bit integers.
     *
     * Base64 character set:
     * ./         [A-Z]      [a-z]     [0-9]
     * 0x2e-0x2f, 0x41-0x5a, 0x61-0x7a, 0x30-0x39
     *
     * @param int $src
     * @return int
     */
    protected static function decode6Bits(int $src) : int
    {
        $ret = -1;
        // if ($src > 0x2d && $src < 0x30) ret += $src - 0x2e + 1; // -45
        $ret += (0x2d - $src & $src - 0x30) >> 8 & $src - 45;
        // if ($src > 0x40 && $src < 0x5b) ret += $src - 0x41 + 2 + 1; // -62
        $ret += (0x40 - $src & $src - 0x5b) >> 8 & $src - 62;
        // if ($src > 0x60 && $src < 0x7b) ret += $src - 0x61 + 28 + 1; // -68
        $ret += (0x60 - $src & $src - 0x7b) >> 8 & $src - 68;
        // if ($src > 0x2f && $src < 0x3a) ret += $src - 0x30 + 54 + 1; // 7
        $ret += (0x2f - $src & $src - 0x3a) >> 8 & $src + 7;
        return $ret;
    }
    /**
     * Uses bitwise operators instead of table-lookups to turn 8-bit integers
     * into 6-bit integers.
     *
     * @param int $src
     * @return string
     */
    protected static function encode6Bits(int $src) : string
    {
        $src += 0x2e;
        // if ($src > 0x2f) $src += 0x41 - 0x30; // 17
        $src += 0x2f - $src >> 8 & 17;
        // if ($src > 0x5a) $src += 0x61 - 0x5b; // 6
        $src += 0x5a - $src >> 8 & 6;
        // if ($src > 0x7a) $src += 0x30 - 0x7b; // -75
        $src -= 0x7a - $src >> 8 & 75;
        return \pack('C', $src);
    }
}

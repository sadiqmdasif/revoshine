<?php

/**
 * OtherPrimeInfo
 *
 * PHP version 5
 *
 * @author    Jim Wigginton <terrafrost@php.net>
 * @copyright 2016 Jim Wigginton
 * @license   http://www.opensource.org/licenses/mit-license.html  MIT License
 * @link      http://phpseclib.sourceforge.net
 */
namespace _PhpScoper01187d35592a\phpseclib3\File\ASN1\Maps;

use _PhpScoper01187d35592a\phpseclib3\File\ASN1;
/**
 * OtherPrimeInfo
 *
 * @author  Jim Wigginton <terrafrost@php.net>
 */
abstract class OtherPrimeInfo
{
    // version must be multi if otherPrimeInfos present
    const MAP = ['type' => ASN1::TYPE_SEQUENCE, 'children' => [
        'prime' => ['type' => ASN1::TYPE_INTEGER],
        // ri
        'exponent' => ['type' => ASN1::TYPE_INTEGER],
        // di
        'coefficient' => ['type' => ASN1::TYPE_INTEGER],
    ]];
}

<?php

/**
 * Certificate
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
 * Certificate
 *
 * @author  Jim Wigginton <terrafrost@php.net>
 */
abstract class Certificate
{
    const MAP = ['type' => ASN1::TYPE_SEQUENCE, 'children' => ['tbsCertificate' => TBSCertificate::MAP, 'signatureAlgorithm' => AlgorithmIdentifier::MAP, 'signature' => ['type' => ASN1::TYPE_BIT_STRING]]];
}

<?php

/**
 * Pure-PHP implementation of Twofish.
 *
 * Uses mcrypt, if available, and an internal implementation, otherwise.
 *
 * PHP version 5
 *
 * Useful resources are as follows:
 *
 *  - {@link http://en.wikipedia.org/wiki/Twofish Wikipedia description of Twofish}
 *
 * Here's a short example of how to use this library:
 * <code>
 * <?php
 *    include 'vendor/autoload.php';
 *
 *    $twofish = new \phpseclib3\Crypt\Twofish('ctr');
 *
 *    $twofish->setKey('12345678901234567890123456789012');
 *
 *    $plaintext = str_repeat('a', 1024);
 *
 *    echo $twofish->decrypt($twofish->encrypt($plaintext));
 * ?>
 * </code>
 *
 * @author    Jim Wigginton <terrafrost@php.net>
 * @author    Hans-Juergen Petrich <petrich@tronic-media.com>
 * @copyright 2007 Jim Wigginton
 * @license   http://www.opensource.org/licenses/mit-license.html  MIT License
 * @link      http://phpseclib.sourceforge.net
 */
namespace _PhpScoper01187d35592a\phpseclib3\Crypt;

use _PhpScoper01187d35592a\phpseclib3\Crypt\Common\BlockCipher;
use _PhpScoper01187d35592a\phpseclib3\Exception\BadModeException;
/**
 * Pure-PHP implementation of Twofish.
 *
 * @author  Jim Wigginton <terrafrost@php.net>
 * @author  Hans-Juergen Petrich <petrich@tronic-media.com>
 */
class Twofish extends BlockCipher
{
    /**
     * The mcrypt specific name of the cipher
     *
     * @see \phpseclib3\Crypt\Common\SymmetricKey::cipher_name_mcrypt
     * @var string
     */
    protected $cipher_name_mcrypt = 'twofish';
    /**
     * Optimizing value while CFB-encrypting
     *
     * @see \phpseclib3\Crypt\Common\SymmetricKey::cfb_init_len
     * @var int
     */
    protected $cfb_init_len = 800;
    /**
     * Q-Table
     *
     * @var array
     */
    private static $q0 = [0xa9, 0x67, 0xb3, 0xe8, 0x4, 0xfd, 0xa3, 0x76, 0x9a, 0x92, 0x80, 0x78, 0xe4, 0xdd, 0xd1, 0x38, 0xd, 0xc6, 0x35, 0x98, 0x18, 0xf7, 0xec, 0x6c, 0x43, 0x75, 0x37, 0x26, 0xfa, 0x13, 0x94, 0x48, 0xf2, 0xd0, 0x8b, 0x30, 0x84, 0x54, 0xdf, 0x23, 0x19, 0x5b, 0x3d, 0x59, 0xf3, 0xae, 0xa2, 0x82, 0x63, 0x1, 0x83, 0x2e, 0xd9, 0x51, 0x9b, 0x7c, 0xa6, 0xeb, 0xa5, 0xbe, 0x16, 0xc, 0xe3, 0x61, 0xc0, 0x8c, 0x3a, 0xf5, 0x73, 0x2c, 0x25, 0xb, 0xbb, 0x4e, 0x89, 0x6b, 0x53, 0x6a, 0xb4, 0xf1, 0xe1, 0xe6, 0xbd, 0x45, 0xe2, 0xf4, 0xb6, 0x66, 0xcc, 0x95, 0x3, 0x56, 0xd4, 0x1c, 0x1e, 0xd7, 0xfb, 0xc3, 0x8e, 0xb5, 0xe9, 0xcf, 0xbf, 0xba, 0xea, 0x77, 0x39, 0xaf, 0x33, 0xc9, 0x62, 0x71, 0x81, 0x79, 0x9, 0xad, 0x24, 0xcd, 0xf9, 0xd8, 0xe5, 0xc5, 0xb9, 0x4d, 0x44, 0x8, 0x86, 0xe7, 0xa1, 0x1d, 0xaa, 0xed, 0x6, 0x70, 0xb2, 0xd2, 0x41, 0x7b, 0xa0, 0x11, 0x31, 0xc2, 0x27, 0x90, 0x20, 0xf6, 0x60, 0xff, 0x96, 0x5c, 0xb1, 0xab, 0x9e, 0x9c, 0x52, 0x1b, 0x5f, 0x93, 0xa, 0xef, 0x91, 0x85, 0x49, 0xee, 0x2d, 0x4f, 0x8f, 0x3b, 0x47, 0x87, 0x6d, 0x46, 0xd6, 0x3e, 0x69, 0x64, 0x2a, 0xce, 0xcb, 0x2f, 0xfc, 0x97, 0x5, 0x7a, 0xac, 0x7f, 0xd5, 0x1a, 0x4b, 0xe, 0xa7, 0x5a, 0x28, 0x14, 0x3f, 0x29, 0x88, 0x3c, 0x4c, 0x2, 0xb8, 0xda, 0xb0, 0x17, 0x55, 0x1f, 0x8a, 0x7d, 0x57, 0xc7, 0x8d, 0x74, 0xb7, 0xc4, 0x9f, 0x72, 0x7e, 0x15, 0x22, 0x12, 0x58, 0x7, 0x99, 0x34, 0x6e, 0x50, 0xde, 0x68, 0x65, 0xbc, 0xdb, 0xf8, 0xc8, 0xa8, 0x2b, 0x40, 0xdc, 0xfe, 0x32, 0xa4, 0xca, 0x10, 0x21, 0xf0, 0xd3, 0x5d, 0xf, 0x0, 0x6f, 0x9d, 0x36, 0x42, 0x4a, 0x5e, 0xc1, 0xe0];
    /**
     * Q-Table
     *
     * @var array
     */
    private static $q1 = [0x75, 0xf3, 0xc6, 0xf4, 0xdb, 0x7b, 0xfb, 0xc8, 0x4a, 0xd3, 0xe6, 0x6b, 0x45, 0x7d, 0xe8, 0x4b, 0xd6, 0x32, 0xd8, 0xfd, 0x37, 0x71, 0xf1, 0xe1, 0x30, 0xf, 0xf8, 0x1b, 0x87, 0xfa, 0x6, 0x3f, 0x5e, 0xba, 0xae, 0x5b, 0x8a, 0x0, 0xbc, 0x9d, 0x6d, 0xc1, 0xb1, 0xe, 0x80, 0x5d, 0xd2, 0xd5, 0xa0, 0x84, 0x7, 0x14, 0xb5, 0x90, 0x2c, 0xa3, 0xb2, 0x73, 0x4c, 0x54, 0x92, 0x74, 0x36, 0x51, 0x38, 0xb0, 0xbd, 0x5a, 0xfc, 0x60, 0x62, 0x96, 0x6c, 0x42, 0xf7, 0x10, 0x7c, 0x28, 0x27, 0x8c, 0x13, 0x95, 0x9c, 0xc7, 0x24, 0x46, 0x3b, 0x70, 0xca, 0xe3, 0x85, 0xcb, 0x11, 0xd0, 0x93, 0xb8, 0xa6, 0x83, 0x20, 0xff, 0x9f, 0x77, 0xc3, 0xcc, 0x3, 0x6f, 0x8, 0xbf, 0x40, 0xe7, 0x2b, 0xe2, 0x79, 0xc, 0xaa, 0x82, 0x41, 0x3a, 0xea, 0xb9, 0xe4, 0x9a, 0xa4, 0x97, 0x7e, 0xda, 0x7a, 0x17, 0x66, 0x94, 0xa1, 0x1d, 0x3d, 0xf0, 0xde, 0xb3, 0xb, 0x72, 0xa7, 0x1c, 0xef, 0xd1, 0x53, 0x3e, 0x8f, 0x33, 0x26, 0x5f, 0xec, 0x76, 0x2a, 0x49, 0x81, 0x88, 0xee, 0x21, 0xc4, 0x1a, 0xeb, 0xd9, 0xc5, 0x39, 0x99, 0xcd, 0xad, 0x31, 0x8b, 0x1, 0x18, 0x23, 0xdd, 0x1f, 0x4e, 0x2d, 0xf9, 0x48, 0x4f, 0xf2, 0x65, 0x8e, 0x78, 0x5c, 0x58, 0x19, 0x8d, 0xe5, 0x98, 0x57, 0x67, 0x7f, 0x5, 0x64, 0xaf, 0x63, 0xb6, 0xfe, 0xf5, 0xb7, 0x3c, 0xa5, 0xce, 0xe9, 0x68, 0x44, 0xe0, 0x4d, 0x43, 0x69, 0x29, 0x2e, 0xac, 0x15, 0x59, 0xa8, 0xa, 0x9e, 0x6e, 0x47, 0xdf, 0x34, 0x35, 0x6a, 0xcf, 0xdc, 0x22, 0xc9, 0xc0, 0x9b, 0x89, 0xd4, 0xed, 0xab, 0x12, 0xa2, 0xd, 0x52, 0xbb, 0x2, 0x2f, 0xa9, 0xd7, 0x61, 0x1e, 0xb4, 0x50, 0x4, 0xf6, 0xc2, 0x16, 0x25, 0x86, 0x56, 0x55, 0x9, 0xbe, 0x91];
    /**
     * M-Table
     *
     * @var array
     */
    private static $m0 = [0xbcbc3275, 0xecec21f3, 0x202043c6, 0xb3b3c9f4, 0xdada03db, 0x2028b7b, 0xe2e22bfb, 0x9e9efac8, 0xc9c9ec4a, 0xd4d409d3, 0x18186be6, 0x1e1e9f6b, 0x98980e45, 0xb2b2387d, 0xa6a6d2e8, 0x2626b74b, 0x3c3c57d6, 0x93938a32, 0x8282eed8, 0x525298fd, 0x7b7bd437, 0xbbbb3771, 0x5b5b97f1, 0x474783e1, 0x24243c30, 0x5151e20f, 0xbabac6f8, 0x4a4af31b, 0xbfbf4887, 0xd0d70fa, 0xb0b0b306, 0x7575de3f, 0xd2d2fd5e, 0x7d7d20ba, 0x666631ae, 0x3a3aa35b, 0x59591c8a, 0x0, 0xcdcd93bc, 0x1a1ae09d, 0xaeae2c6d, 0x7f7fabc1, 0x2b2bc7b1, 0xbebeb90e, 0xe0e0a080, 0x8a8a105d, 0x3b3b52d2, 0x6464bad5, 0xd8d888a0, 0xe7e7a584, 0x5f5fe807, 0x1b1b1114, 0x2c2cc2b5, 0xfcfcb490, 0x3131272c, 0x808065a3, 0x73732ab2, 0xc0c8173, 0x79795f4c, 0x6b6b4154, 0x4b4b0292, 0x53536974, 0x94948f36, 0x83831f51, 0x2a2a3638, 0xc4c49cb0, 0x2222c8bd, 0xd5d5f85a, 0xbdbdc3fc, 0x48487860, 0xffffce62, 0x4c4c0796, 0x4141776c, 0xc7c7e642, 0xebeb24f7, 0x1c1c1410, 0x5d5d637c, 0x36362228, 0x6767c027, 0xe9e9af8c, 0x4444f913, 0x1414ea95, 0xf5f5bb9c, 0xcfcf18c7, 0x3f3f2d24, 0xc0c0e346, 0x7272db3b, 0x54546c70, 0x29294cca, 0xf0f035e3, 0x808fe85, 0xc6c617cb, 0xf3f34f11, 0x8c8ce4d0, 0xa4a45993, 0xcaca96b8, 0x68683ba6, 0xb8b84d83, 0x38382820, 0xe5e52eff, 0xadad569f, 0xb0b8477, 0xc8c81dc3, 0x9999ffcc, 0x5858ed03, 0x19199a6f, 0xe0e0a08, 0x95957ebf, 0x70705040, 0xf7f730e7, 0x6e6ecf2b, 0x1f1f6ee2, 0xb5b53d79, 0x9090f0c, 0x616134aa, 0x57571682, 0x9f9f0b41, 0x9d9d803a, 0x111164ea, 0x2525cdb9, 0xafafdde4, 0x4545089a, 0xdfdf8da4, 0xa3a35c97, 0xeaead57e, 0x353558da, 0xededd07a, 0x4343fc17, 0xf8f8cb66, 0xfbfbb194, 0x3737d3a1, 0xfafa401d, 0xc2c2683d, 0xb4b4ccf0, 0x32325dde, 0x9c9c71b3, 0x5656e70b, 0xe3e3da72, 0x878760a7, 0x15151b1c, 0xf9f93aef, 0x6363bfd1, 0x3434a953, 0x9a9a853e, 0xb1b1428f, 0x7c7cd133, 0x88889b26, 0x3d3da65f, 0xa1a1d7ec, 0xe4e4df76, 0x8181942a, 0x91910149, 0xf0ffb81, 0xeeeeaa88, 0x161661ee, 0xd7d77321, 0x9797f5c4, 0xa5a5a81a, 0xfefe3feb, 0x6d6db5d9, 0x7878aec5, 0xc5c56d39, 0x1d1de599, 0x7676a4cd, 0x3e3edcad, 0xcbcb6731, 0xb6b6478b, 0xefef5b01, 0x12121e18, 0x6060c523, 0x6a6ab0dd, 0x4d4df61f, 0xcecee94e, 0xdede7c2d, 0x55559df9, 0x7e7e5a48, 0x2121b24f, 0x3037af2, 0xa0a02665, 0x5e5e198e, 0x5a5a6678, 0x65654b5c, 0x62624e58, 0xfdfd4519, 0x606f48d, 0x404086e5, 0xf2f2be98, 0x3333ac57, 0x17179067, 0x5058e7f, 0xe8e85e05, 0x4f4f7d64, 0x89896aaf, 0x10109563, 0x74742fb6, 0xa0a75fe, 0x5c5c92f5, 0x9b9b74b7, 0x2d2d333c, 0x3030d6a5, 0x2e2e49ce, 0x494989e9, 0x46467268, 0x77775544, 0xa8a8d8e0, 0x9696044d, 0x2828bd43, 0xa9a92969, 0xd9d97929, 0x8686912e, 0xd1d187ac, 0xf4f44a15, 0x8d8d1559, 0xd6d682a8, 0xb9b9bc0a, 0x42420d9e, 0xf6f6c16e, 0x2f2fb847, 0xdddd06df, 0x23233934, 0xcccc6235, 0xf1f1c46a, 0xc1c112cf, 0x8585ebdc, 0x8f8f9e22, 0x7171a1c9, 0x9090f0c0, 0xaaaa539b, 0x101f189, 0x8b8be1d4, 0x4e4e8ced, 0x8e8e6fab, 0xababa212, 0x6f6f3ea2, 0xe6e6540d, 0xdbdbf252, 0x92927bbb, 0xb7b7b602, 0x6969ca2f, 0x3939d9a9, 0xd3d30cd7, 0xa7a72361, 0xa2a2ad1e, 0xc3c399b4, 0x6c6c4450, 0x7070504, 0x4047ff6, 0x272746c2, 0xacaca716, 0xd0d07625, 0x50501386, 0xdcdcf756, 0x84841a55, 0xe1e15109, 0x7a7a25be, 0x1313ef91];
    /**
     * M-Table
     *
     * @var array
     */
    private static $m1 = [0xa9d93939, 0x67901717, 0xb3719c9c, 0xe8d2a6a6, 0x4050707, 0xfd985252, 0xa3658080, 0x76dfe4e4, 0x9a084545, 0x92024b4b, 0x80a0e0e0, 0x78665a5a, 0xe4ddafaf, 0xddb06a6a, 0xd1bf6363, 0x38362a2a, 0xd54e6e6, 0xc6432020, 0x3562cccc, 0x98bef2f2, 0x181e1212, 0xf724ebeb, 0xecd7a1a1, 0x6c774141, 0x43bd2828, 0x7532bcbc, 0x37d47b7b, 0x269b8888, 0xfa700d0d, 0x13f94444, 0x94b1fbfb, 0x485a7e7e, 0xf27a0303, 0xd0e48c8c, 0x8b47b6b6, 0x303c2424, 0x84a5e7e7, 0x54416b6b, 0xdf06dddd, 0x23c56060, 0x1945fdfd, 0x5ba33a3a, 0x3d68c2c2, 0x59158d8d, 0xf321ecec, 0xae316666, 0xa23e6f6f, 0x82165757, 0x63951010, 0x15befef, 0x834db8b8, 0x2e918686, 0xd9b56d6d, 0x511f8383, 0x9b53aaaa, 0x7c635d5d, 0xa63b6868, 0xeb3ffefe, 0xa5d63030, 0xbe257a7a, 0x16a7acac, 0xc0f0909, 0xe335f0f0, 0x6123a7a7, 0xc0f09090, 0x8cafe9e9, 0x3a809d9d, 0xf5925c5c, 0x73810c0c, 0x2c273131, 0x2576d0d0, 0xbe75656, 0xbb7b9292, 0x4ee9cece, 0x89f10101, 0x6b9f1e1e, 0x53a93434, 0x6ac4f1f1, 0xb499c3c3, 0xf1975b5b, 0xe1834747, 0xe66b1818, 0xbdc82222, 0x450e9898, 0xe26e1f1f, 0xf4c9b3b3, 0xb62f7474, 0x66cbf8f8, 0xccff9999, 0x95ea1414, 0x3ed5858, 0x56f7dcdc, 0xd4e18b8b, 0x1c1b1515, 0x1eada2a2, 0xd70cd3d3, 0xfb2be2e2, 0xc31dc8c8, 0x8e195e5e, 0xb5c22c2c, 0xe9894949, 0xcf12c1c1, 0xbf7e9595, 0xba207d7d, 0xea641111, 0x77840b0b, 0x396dc5c5, 0xaf6a8989, 0x33d17c7c, 0xc9a17171, 0x62ceffff, 0x7137bbbb, 0x81fb0f0f, 0x793db5b5, 0x951e1e1, 0xaddc3e3e, 0x242d3f3f, 0xcda47676, 0xf99d5555, 0xd8ee8282, 0xe5864040, 0xc5ae7878, 0xb9cd2525, 0x4d049696, 0x44557777, 0x80a0e0e, 0x86135050, 0xe730f7f7, 0xa1d33737, 0x1d40fafa, 0xaa346161, 0xed8c4e4e, 0x6b3b0b0, 0x706c5454, 0xb22a7373, 0xd2523b3b, 0x410b9f9f, 0x7b8b0202, 0xa088d8d8, 0x114ff3f3, 0x3167cbcb, 0xc2462727, 0x27c06767, 0x90b4fcfc, 0x20283838, 0xf67f0404, 0x60784848, 0xff2ee5e5, 0x96074c4c, 0x5c4b6565, 0xb1c72b2b, 0xab6f8e8e, 0x9e0d4242, 0x9cbbf5f5, 0x52f2dbdb, 0x1bf34a4a, 0x5fa63d3d, 0x9359a4a4, 0xabcb9b9, 0xef3af9f9, 0x91ef1313, 0x85fe0808, 0x49019191, 0xee611616, 0x2d7cdede, 0x4fb22121, 0x8f42b1b1, 0x3bdb7272, 0x47b82f2f, 0x8748bfbf, 0x6d2caeae, 0x46e3c0c0, 0xd6573c3c, 0x3e859a9a, 0x6929a9a9, 0x647d4f4f, 0x2a948181, 0xce492e2e, 0xcb17c6c6, 0x2fca6969, 0xfcc3bdbd, 0x975ca3a3, 0x55ee8e8, 0x7ad0eded, 0xac87d1d1, 0x7f8e0505, 0xd5ba6464, 0x1aa8a5a5, 0x4bb72626, 0xeb9bebe, 0xa7608787, 0x5af8d5d5, 0x28223636, 0x14111b1b, 0x3fde7575, 0x2979d9d9, 0x88aaeeee, 0x3c332d2d, 0x4c5f7979, 0x2b6b7b7, 0xb896caca, 0xda583535, 0xb09cc4c4, 0x17fc4343, 0x551a8484, 0x1ff64d4d, 0x8a1c5959, 0x7d38b2b2, 0x57ac3333, 0xc718cfcf, 0x8df40606, 0x74695353, 0xb7749b9b, 0xc4f59797, 0x9f56adad, 0x72dae3e3, 0x7ed5eaea, 0x154af4f4, 0x229e8f8f, 0x12a2abab, 0x584e6262, 0x7e85f5f, 0x99e51d1d, 0x34392323, 0x6ec1f6f6, 0x50446c6c, 0xde5d3232, 0x68724646, 0x6526a0a0, 0xbc93cdcd, 0xdb03dada, 0xf8c6baba, 0xc8fa9e9e, 0xa882d6d6, 0x2bcf6e6e, 0x40507070, 0xdceb8585, 0xfe750a0a, 0x328a9393, 0xa48ddfdf, 0xca4c2929, 0x10141c1c, 0x2173d7d7, 0xf0ccb4b4, 0xd309d4d4, 0x5d108a8a, 0xfe25151, 0x0, 0x6f9a1919, 0x9de01a1a, 0x368f9494, 0x42e6c7c7, 0x4aecc9c9, 0x5efdd2d2, 0xc1ab7f7f, 0xe0d8a8a8];
    /**
     * M-Table
     *
     * @var array
     */
    private static $m2 = [0xbc75bc32, 0xecf3ec21, 0x20c62043, 0xb3f4b3c9, 0xdadbda03, 0x27b028b, 0xe2fbe22b, 0x9ec89efa, 0xc94ac9ec, 0xd4d3d409, 0x18e6186b, 0x1e6b1e9f, 0x9845980e, 0xb27db238, 0xa6e8a6d2, 0x264b26b7, 0x3cd63c57, 0x9332938a, 0x82d882ee, 0x52fd5298, 0x7b377bd4, 0xbb71bb37, 0x5bf15b97, 0x47e14783, 0x2430243c, 0x510f51e2, 0xbaf8bac6, 0x4a1b4af3, 0xbf87bf48, 0xdfa0d70, 0xb006b0b3, 0x753f75de, 0xd25ed2fd, 0x7dba7d20, 0x66ae6631, 0x3a5b3aa3, 0x598a591c, 0x0, 0xcdbccd93, 0x1a9d1ae0, 0xae6dae2c, 0x7fc17fab, 0x2bb12bc7, 0xbe0ebeb9, 0xe080e0a0, 0x8a5d8a10, 0x3bd23b52, 0x64d564ba, 0xd8a0d888, 0xe784e7a5, 0x5f075fe8, 0x1b141b11, 0x2cb52cc2, 0xfc90fcb4, 0x312c3127, 0x80a38065, 0x73b2732a, 0xc730c81, 0x794c795f, 0x6b546b41, 0x4b924b02, 0x53745369, 0x9436948f, 0x8351831f, 0x2a382a36, 0xc4b0c49c, 0x22bd22c8, 0xd55ad5f8, 0xbdfcbdc3, 0x48604878, 0xff62ffce, 0x4c964c07, 0x416c4177, 0xc742c7e6, 0xebf7eb24, 0x1c101c14, 0x5d7c5d63, 0x36283622, 0x672767c0, 0xe98ce9af, 0x441344f9, 0x149514ea, 0xf59cf5bb, 0xcfc7cf18, 0x3f243f2d, 0xc046c0e3, 0x723b72db, 0x5470546c, 0x29ca294c, 0xf0e3f035, 0x88508fe, 0xc6cbc617, 0xf311f34f, 0x8cd08ce4, 0xa493a459, 0xcab8ca96, 0x68a6683b, 0xb883b84d, 0x38203828, 0xe5ffe52e, 0xad9fad56, 0xb770b84, 0xc8c3c81d, 0x99cc99ff, 0x580358ed, 0x196f199a, 0xe080e0a, 0x95bf957e, 0x70407050, 0xf7e7f730, 0x6e2b6ecf, 0x1fe21f6e, 0xb579b53d, 0x90c090f, 0x61aa6134, 0x57825716, 0x9f419f0b, 0x9d3a9d80, 0x11ea1164, 0x25b925cd, 0xafe4afdd, 0x459a4508, 0xdfa4df8d, 0xa397a35c, 0xea7eead5, 0x35da3558, 0xed7aedd0, 0x431743fc, 0xf866f8cb, 0xfb94fbb1, 0x37a137d3, 0xfa1dfa40, 0xc23dc268, 0xb4f0b4cc, 0x32de325d, 0x9cb39c71, 0x560b56e7, 0xe372e3da, 0x87a78760, 0x151c151b, 0xf9eff93a, 0x63d163bf, 0x345334a9, 0x9a3e9a85, 0xb18fb142, 0x7c337cd1, 0x8826889b, 0x3d5f3da6, 0xa1eca1d7, 0xe476e4df, 0x812a8194, 0x91499101, 0xf810ffb, 0xee88eeaa, 0x16ee1661, 0xd721d773, 0x97c497f5, 0xa51aa5a8, 0xfeebfe3f, 0x6dd96db5, 0x78c578ae, 0xc539c56d, 0x1d991de5, 0x76cd76a4, 0x3ead3edc, 0xcb31cb67, 0xb68bb647, 0xef01ef5b, 0x1218121e, 0x602360c5, 0x6add6ab0, 0x4d1f4df6, 0xce4ecee9, 0xde2dde7c, 0x55f9559d, 0x7e487e5a, 0x214f21b2, 0x3f2037a, 0xa065a026, 0x5e8e5e19, 0x5a785a66, 0x655c654b, 0x6258624e, 0xfd19fd45, 0x68d06f4, 0x40e54086, 0xf298f2be, 0x335733ac, 0x17671790, 0x57f058e, 0xe805e85e, 0x4f644f7d, 0x89af896a, 0x10631095, 0x74b6742f, 0xafe0a75, 0x5cf55c92, 0x9bb79b74, 0x2d3c2d33, 0x30a530d6, 0x2ece2e49, 0x49e94989, 0x46684672, 0x77447755, 0xa8e0a8d8, 0x964d9604, 0x284328bd, 0xa969a929, 0xd929d979, 0x862e8691, 0xd1acd187, 0xf415f44a, 0x8d598d15, 0xd6a8d682, 0xb90ab9bc, 0x429e420d, 0xf66ef6c1, 0x2f472fb8, 0xdddfdd06, 0x23342339, 0xcc35cc62, 0xf16af1c4, 0xc1cfc112, 0x85dc85eb, 0x8f228f9e, 0x71c971a1, 0x90c090f0, 0xaa9baa53, 0x18901f1, 0x8bd48be1, 0x4eed4e8c, 0x8eab8e6f, 0xab12aba2, 0x6fa26f3e, 0xe60de654, 0xdb52dbf2, 0x92bb927b, 0xb702b7b6, 0x692f69ca, 0x39a939d9, 0xd3d7d30c, 0xa761a723, 0xa21ea2ad, 0xc3b4c399, 0x6c506c44, 0x7040705, 0x4f6047f, 0x27c22746, 0xac16aca7, 0xd025d076, 0x50865013, 0xdc56dcf7, 0x8455841a, 0xe109e151, 0x7abe7a25, 0x139113ef];
    /**
     * M-Table
     *
     * @var array
     */
    private static $m3 = [0xd939a9d9, 0x90176790, 0x719cb371, 0xd2a6e8d2, 0x5070405, 0x9852fd98, 0x6580a365, 0xdfe476df, 0x8459a08, 0x24b9202, 0xa0e080a0, 0x665a7866, 0xddafe4dd, 0xb06addb0, 0xbf63d1bf, 0x362a3836, 0x54e60d54, 0x4320c643, 0x62cc3562, 0xbef298be, 0x1e12181e, 0x24ebf724, 0xd7a1ecd7, 0x77416c77, 0xbd2843bd, 0x32bc7532, 0xd47b37d4, 0x9b88269b, 0x700dfa70, 0xf94413f9, 0xb1fb94b1, 0x5a7e485a, 0x7a03f27a, 0xe48cd0e4, 0x47b68b47, 0x3c24303c, 0xa5e784a5, 0x416b5441, 0x6dddf06, 0xc56023c5, 0x45fd1945, 0xa33a5ba3, 0x68c23d68, 0x158d5915, 0x21ecf321, 0x3166ae31, 0x3e6fa23e, 0x16578216, 0x95106395, 0x5bef015b, 0x4db8834d, 0x91862e91, 0xb56dd9b5, 0x1f83511f, 0x53aa9b53, 0x635d7c63, 0x3b68a63b, 0x3ffeeb3f, 0xd630a5d6, 0x257abe25, 0xa7ac16a7, 0xf090c0f, 0x35f0e335, 0x23a76123, 0xf090c0f0, 0xafe98caf, 0x809d3a80, 0x925cf592, 0x810c7381, 0x27312c27, 0x76d02576, 0xe7560be7, 0x7b92bb7b, 0xe9ce4ee9, 0xf10189f1, 0x9f1e6b9f, 0xa93453a9, 0xc4f16ac4, 0x99c3b499, 0x975bf197, 0x8347e183, 0x6b18e66b, 0xc822bdc8, 0xe98450e, 0x6e1fe26e, 0xc9b3f4c9, 0x2f74b62f, 0xcbf866cb, 0xff99ccff, 0xea1495ea, 0xed5803ed, 0xf7dc56f7, 0xe18bd4e1, 0x1b151c1b, 0xada21ead, 0xcd3d70c, 0x2be2fb2b, 0x1dc8c31d, 0x195e8e19, 0xc22cb5c2, 0x8949e989, 0x12c1cf12, 0x7e95bf7e, 0x207dba20, 0x6411ea64, 0x840b7784, 0x6dc5396d, 0x6a89af6a, 0xd17c33d1, 0xa171c9a1, 0xceff62ce, 0x37bb7137, 0xfb0f81fb, 0x3db5793d, 0x51e10951, 0xdc3eaddc, 0x2d3f242d, 0xa476cda4, 0x9d55f99d, 0xee82d8ee, 0x8640e586, 0xae78c5ae, 0xcd25b9cd, 0x4964d04, 0x55774455, 0xa0e080a, 0x13508613, 0x30f7e730, 0xd337a1d3, 0x40fa1d40, 0x3461aa34, 0x8c4eed8c, 0xb3b006b3, 0x6c54706c, 0x2a73b22a, 0x523bd252, 0xb9f410b, 0x8b027b8b, 0x88d8a088, 0x4ff3114f, 0x67cb3167, 0x4627c246, 0xc06727c0, 0xb4fc90b4, 0x28382028, 0x7f04f67f, 0x78486078, 0x2ee5ff2e, 0x74c9607, 0x4b655c4b, 0xc72bb1c7, 0x6f8eab6f, 0xd429e0d, 0xbbf59cbb, 0xf2db52f2, 0xf34a1bf3, 0xa63d5fa6, 0x59a49359, 0xbcb90abc, 0x3af9ef3a, 0xef1391ef, 0xfe0885fe, 0x1914901, 0x6116ee61, 0x7cde2d7c, 0xb2214fb2, 0x42b18f42, 0xdb723bdb, 0xb82f47b8, 0x48bf8748, 0x2cae6d2c, 0xe3c046e3, 0x573cd657, 0x859a3e85, 0x29a96929, 0x7d4f647d, 0x94812a94, 0x492ece49, 0x17c6cb17, 0xca692fca, 0xc3bdfcc3, 0x5ca3975c, 0x5ee8055e, 0xd0ed7ad0, 0x87d1ac87, 0x8e057f8e, 0xba64d5ba, 0xa8a51aa8, 0xb7264bb7, 0xb9be0eb9, 0x6087a760, 0xf8d55af8, 0x22362822, 0x111b1411, 0xde753fde, 0x79d92979, 0xaaee88aa, 0x332d3c33, 0x5f794c5f, 0xb6b702b6, 0x96cab896, 0x5835da58, 0x9cc4b09c, 0xfc4317fc, 0x1a84551a, 0xf64d1ff6, 0x1c598a1c, 0x38b27d38, 0xac3357ac, 0x18cfc718, 0xf4068df4, 0x69537469, 0x749bb774, 0xf597c4f5, 0x56ad9f56, 0xdae372da, 0xd5ea7ed5, 0x4af4154a, 0x9e8f229e, 0xa2ab12a2, 0x4e62584e, 0xe85f07e8, 0xe51d99e5, 0x39233439, 0xc1f66ec1, 0x446c5044, 0x5d32de5d, 0x72466872, 0x26a06526, 0x93cdbc93, 0x3dadb03, 0xc6baf8c6, 0xfa9ec8fa, 0x82d6a882, 0xcf6e2bcf, 0x50704050, 0xeb85dceb, 0x750afe75, 0x8a93328a, 0x8ddfa48d, 0x4c29ca4c, 0x141c1014, 0x73d72173, 0xccb4f0cc, 0x9d4d309, 0x108a5d10, 0xe2510fe2, 0x0, 0x9a196f9a, 0xe01a9de0, 0x8f94368f, 0xe6c742e6, 0xecc94aec, 0xfdd25efd, 0xab7fc1ab, 0xd8a8e0d8];
    /**
     * The Key Schedule Array
     *
     * @var array
     */
    private $K = [];
    /**
     * The Key depended S-Table 0
     *
     * @var array
     */
    private $S0 = [];
    /**
     * The Key depended S-Table 1
     *
     * @var array
     */
    private $S1 = [];
    /**
     * The Key depended S-Table 2
     *
     * @var array
     */
    private $S2 = [];
    /**
     * The Key depended S-Table 3
     *
     * @var array
     */
    private $S3 = [];
    /**
     * Holds the last used key
     *
     * @var array
     */
    private $kl;
    /**
     * The Key Length (in bytes)
     *
     * @see Crypt_Twofish::setKeyLength()
     * @var int
     */
    protected $key_length = 16;
    /**
     * Default Constructor.
     *
     * @param string $mode
     * @throws BadModeException if an invalid / unsupported mode is provided
     */
    public function __construct($mode)
    {
        parent::__construct($mode);
        if ($this->mode == self::MODE_STREAM) {
            throw new BadModeException('Block ciphers cannot be ran in stream mode');
        }
    }
    /**
     * Initialize Static Variables
     */
    protected static function initialize_static_variables()
    {
        if (\is_float(self::$m3[0])) {
            self::$m0 = \array_map('intval', self::$m0);
            self::$m1 = \array_map('intval', self::$m1);
            self::$m2 = \array_map('intval', self::$m2);
            self::$m3 = \array_map('intval', self::$m3);
            self::$q0 = \array_map('intval', self::$q0);
            self::$q1 = \array_map('intval', self::$q1);
        }
        parent::initialize_static_variables();
    }
    /**
     * Sets the key length.
     *
     * Valid key lengths are 128, 192 or 256 bits
     *
     * @param int $length
     */
    public function setKeyLength($length)
    {
        switch ($length) {
            case 128:
            case 192:
            case 256:
                break;
            default:
                throw new \LengthException('Key of size ' . $length . ' not supported by this algorithm. Only keys of sizes 16, 24 or 32 supported');
        }
        parent::setKeyLength($length);
    }
    /**
     * Sets the key.
     *
     * Rijndael supports five different key lengths
     *
     * @see setKeyLength()
     * @param string $key
     * @throws \LengthException if the key length isn't supported
     */
    public function setKey($key)
    {
        switch (\strlen($key)) {
            case 16:
            case 24:
            case 32:
                break;
            default:
                throw new \LengthException('Key of size ' . \strlen($key) . ' not supported by this algorithm. Only keys of sizes 16, 24 or 32 supported');
        }
        parent::setKey($key);
    }
    /**
     * Setup the key (expansion)
     *
     * @see \phpseclib3\Crypt\Common\SymmetricKey::_setupKey()
     */
    protected function setupKey()
    {
        if (isset($this->kl['key']) && $this->key === $this->kl['key']) {
            // already expanded
            return;
        }
        $this->kl = ['key' => $this->key];
        /* Key expanding and generating the key-depended s-boxes */
        $le_longs = \unpack('V*', $this->key);
        $key = \unpack('C*', $this->key);
        $m0 = self::$m0;
        $m1 = self::$m1;
        $m2 = self::$m2;
        $m3 = self::$m3;
        $q0 = self::$q0;
        $q1 = self::$q1;
        $K = $S0 = $S1 = $S2 = $S3 = [];
        switch (\strlen($this->key)) {
            case 16:
                list($s7, $s6, $s5, $s4) = $this->mdsrem($le_longs[1], $le_longs[2]);
                list($s3, $s2, $s1, $s0) = $this->mdsrem($le_longs[3], $le_longs[4]);
                for ($i = 0, $j = 1; $i < 40; $i += 2, $j += 2) {
                    $A = $m0[$q0[$q0[$i] ^ $key[9]] ^ $key[1]] ^ $m1[$q0[$q1[$i] ^ $key[10]] ^ $key[2]] ^ $m2[$q1[$q0[$i] ^ $key[11]] ^ $key[3]] ^ $m3[$q1[$q1[$i] ^ $key[12]] ^ $key[4]];
                    $B = $m0[$q0[$q0[$j] ^ $key[13]] ^ $key[5]] ^ $m1[$q0[$q1[$j] ^ $key[14]] ^ $key[6]] ^ $m2[$q1[$q0[$j] ^ $key[15]] ^ $key[7]] ^ $m3[$q1[$q1[$j] ^ $key[16]] ^ $key[8]];
                    $B = $B << 8 | $B >> 24 & 0xff;
                    $A = self::safe_intval($A + $B);
                    $K[] = $A;
                    $A = self::safe_intval($A + $B);
                    $K[] = $A << 9 | $A >> 23 & 0x1ff;
                }
                for ($i = 0; $i < 256; ++$i) {
                    $S0[$i] = $m0[$q0[$q0[$i] ^ $s4] ^ $s0];
                    $S1[$i] = $m1[$q0[$q1[$i] ^ $s5] ^ $s1];
                    $S2[$i] = $m2[$q1[$q0[$i] ^ $s6] ^ $s2];
                    $S3[$i] = $m3[$q1[$q1[$i] ^ $s7] ^ $s3];
                }
                break;
            case 24:
                list($sb, $sa, $s9, $s8) = $this->mdsrem($le_longs[1], $le_longs[2]);
                list($s7, $s6, $s5, $s4) = $this->mdsrem($le_longs[3], $le_longs[4]);
                list($s3, $s2, $s1, $s0) = $this->mdsrem($le_longs[5], $le_longs[6]);
                for ($i = 0, $j = 1; $i < 40; $i += 2, $j += 2) {
                    $A = $m0[$q0[$q0[$q1[$i] ^ $key[17]] ^ $key[9]] ^ $key[1]] ^ $m1[$q0[$q1[$q1[$i] ^ $key[18]] ^ $key[10]] ^ $key[2]] ^ $m2[$q1[$q0[$q0[$i] ^ $key[19]] ^ $key[11]] ^ $key[3]] ^ $m3[$q1[$q1[$q0[$i] ^ $key[20]] ^ $key[12]] ^ $key[4]];
                    $B = $m0[$q0[$q0[$q1[$j] ^ $key[21]] ^ $key[13]] ^ $key[5]] ^ $m1[$q0[$q1[$q1[$j] ^ $key[22]] ^ $key[14]] ^ $key[6]] ^ $m2[$q1[$q0[$q0[$j] ^ $key[23]] ^ $key[15]] ^ $key[7]] ^ $m3[$q1[$q1[$q0[$j] ^ $key[24]] ^ $key[16]] ^ $key[8]];
                    $B = $B << 8 | $B >> 24 & 0xff;
                    $A = self::safe_intval($A + $B);
                    $K[] = $A;
                    $A = self::safe_intval($A + $B);
                    $K[] = $A << 9 | $A >> 23 & 0x1ff;
                }
                for ($i = 0; $i < 256; ++$i) {
                    $S0[$i] = $m0[$q0[$q0[$q1[$i] ^ $s8] ^ $s4] ^ $s0];
                    $S1[$i] = $m1[$q0[$q1[$q1[$i] ^ $s9] ^ $s5] ^ $s1];
                    $S2[$i] = $m2[$q1[$q0[$q0[$i] ^ $sa] ^ $s6] ^ $s2];
                    $S3[$i] = $m3[$q1[$q1[$q0[$i] ^ $sb] ^ $s7] ^ $s3];
                }
                break;
            default:
                // 32
                list($sf, $se, $sd, $sc) = $this->mdsrem($le_longs[1], $le_longs[2]);
                list($sb, $sa, $s9, $s8) = $this->mdsrem($le_longs[3], $le_longs[4]);
                list($s7, $s6, $s5, $s4) = $this->mdsrem($le_longs[5], $le_longs[6]);
                list($s3, $s2, $s1, $s0) = $this->mdsrem($le_longs[7], $le_longs[8]);
                for ($i = 0, $j = 1; $i < 40; $i += 2, $j += 2) {
                    $A = $m0[$q0[$q0[$q1[$q1[$i] ^ $key[25]] ^ $key[17]] ^ $key[9]] ^ $key[1]] ^ $m1[$q0[$q1[$q1[$q0[$i] ^ $key[26]] ^ $key[18]] ^ $key[10]] ^ $key[2]] ^ $m2[$q1[$q0[$q0[$q0[$i] ^ $key[27]] ^ $key[19]] ^ $key[11]] ^ $key[3]] ^ $m3[$q1[$q1[$q0[$q1[$i] ^ $key[28]] ^ $key[20]] ^ $key[12]] ^ $key[4]];
                    $B = $m0[$q0[$q0[$q1[$q1[$j] ^ $key[29]] ^ $key[21]] ^ $key[13]] ^ $key[5]] ^ $m1[$q0[$q1[$q1[$q0[$j] ^ $key[30]] ^ $key[22]] ^ $key[14]] ^ $key[6]] ^ $m2[$q1[$q0[$q0[$q0[$j] ^ $key[31]] ^ $key[23]] ^ $key[15]] ^ $key[7]] ^ $m3[$q1[$q1[$q0[$q1[$j] ^ $key[32]] ^ $key[24]] ^ $key[16]] ^ $key[8]];
                    $B = $B << 8 | $B >> 24 & 0xff;
                    $A = self::safe_intval($A + $B);
                    $K[] = $A;
                    $A = self::safe_intval($A + $B);
                    $K[] = $A << 9 | $A >> 23 & 0x1ff;
                }
                for ($i = 0; $i < 256; ++$i) {
                    $S0[$i] = $m0[$q0[$q0[$q1[$q1[$i] ^ $sc] ^ $s8] ^ $s4] ^ $s0];
                    $S1[$i] = $m1[$q0[$q1[$q1[$q0[$i] ^ $sd] ^ $s9] ^ $s5] ^ $s1];
                    $S2[$i] = $m2[$q1[$q0[$q0[$q0[$i] ^ $se] ^ $sa] ^ $s6] ^ $s2];
                    $S3[$i] = $m3[$q1[$q1[$q0[$q1[$i] ^ $sf] ^ $sb] ^ $s7] ^ $s3];
                }
        }
        $this->K = $K;
        $this->S0 = $S0;
        $this->S1 = $S1;
        $this->S2 = $S2;
        $this->S3 = $S3;
    }
    /**
     * _mdsrem function using by the twofish cipher algorithm
     *
     * @param string $A
     * @param string $B
     * @return array
     */
    private function mdsrem($A, $B)
    {
        // No gain by unrolling this loop.
        for ($i = 0; $i < 8; ++$i) {
            // Get most significant coefficient.
            $t = 0xff & $B >> 24;
            // Shift the others up.
            $B = $B << 8 | 0xff & $A >> 24;
            $A <<= 8;
            $u = $t << 1;
            // Subtract the modular polynomial on overflow.
            if ($t & 0x80) {
                $u ^= 0x14d;
            }
            // Remove t * (a * x^2 + 1).
            $B ^= $t ^ $u << 16;
            // Form u = a*t + t/a = t*(a + 1/a).
            $u ^= 0x7fffffff & $t >> 1;
            // Add the modular polynomial on underflow.
            if ($t & 0x1) {
                $u ^= 0xa6;
            }
            // Remove t * (a + 1/a) * (x^3 + x).
            $B ^= $u << 24 | $u << 8;
        }
        return [0xff & $B >> 24, 0xff & $B >> 16, 0xff & $B >> 8, 0xff & $B];
    }
    /**
     * Encrypts a block
     *
     * @param string $in
     * @return string
     */
    protected function encryptBlock($in)
    {
        $S0 = $this->S0;
        $S1 = $this->S1;
        $S2 = $this->S2;
        $S3 = $this->S3;
        $K = $this->K;
        $in = \unpack("V4", $in);
        $R0 = $K[0] ^ $in[1];
        $R1 = $K[1] ^ $in[2];
        $R2 = $K[2] ^ $in[3];
        $R3 = $K[3] ^ $in[4];
        $ki = 7;
        while ($ki < 39) {
            $t0 = $S0[$R0 & 0xff] ^ $S1[$R0 >> 8 & 0xff] ^ $S2[$R0 >> 16 & 0xff] ^ $S3[$R0 >> 24 & 0xff];
            $t1 = $S0[$R1 >> 24 & 0xff] ^ $S1[$R1 & 0xff] ^ $S2[$R1 >> 8 & 0xff] ^ $S3[$R1 >> 16 & 0xff];
            $R2 ^= self::safe_intval($t0 + $t1 + $K[++$ki]);
            $R2 = $R2 >> 1 & 0x7fffffff | $R2 << 31;
            $R3 = ($R3 >> 31 & 1 | $R3 << 1) ^ self::safe_intval($t0 + ($t1 << 1) + $K[++$ki]);
            $t0 = $S0[$R2 & 0xff] ^ $S1[$R2 >> 8 & 0xff] ^ $S2[$R2 >> 16 & 0xff] ^ $S3[$R2 >> 24 & 0xff];
            $t1 = $S0[$R3 >> 24 & 0xff] ^ $S1[$R3 & 0xff] ^ $S2[$R3 >> 8 & 0xff] ^ $S3[$R3 >> 16 & 0xff];
            $R0 ^= self::safe_intval($t0 + $t1 + $K[++$ki]);
            $R0 = $R0 >> 1 & 0x7fffffff | $R0 << 31;
            $R1 = ($R1 >> 31 & 1 | $R1 << 1) ^ self::safe_intval($t0 + ($t1 << 1) + $K[++$ki]);
        }
        // @codingStandardsIgnoreStart
        return \pack("V4", $K[4] ^ $R2, $K[5] ^ $R3, $K[6] ^ $R0, $K[7] ^ $R1);
        // @codingStandardsIgnoreEnd
    }
    /**
     * Decrypts a block
     *
     * @param string $in
     * @return string
     */
    protected function decryptBlock($in)
    {
        $S0 = $this->S0;
        $S1 = $this->S1;
        $S2 = $this->S2;
        $S3 = $this->S3;
        $K = $this->K;
        $in = \unpack("V4", $in);
        $R0 = $K[4] ^ $in[1];
        $R1 = $K[5] ^ $in[2];
        $R2 = $K[6] ^ $in[3];
        $R3 = $K[7] ^ $in[4];
        $ki = 40;
        while ($ki > 8) {
            $t0 = $S0[$R0 & 0xff] ^ $S1[$R0 >> 8 & 0xff] ^ $S2[$R0 >> 16 & 0xff] ^ $S3[$R0 >> 24 & 0xff];
            $t1 = $S0[$R1 >> 24 & 0xff] ^ $S1[$R1 & 0xff] ^ $S2[$R1 >> 8 & 0xff] ^ $S3[$R1 >> 16 & 0xff];
            $R3 ^= self::safe_intval($t0 + ($t1 << 1) + $K[--$ki]);
            $R3 = $R3 >> 1 & 0x7fffffff | $R3 << 31;
            $R2 = ($R2 >> 31 & 0x1 | $R2 << 1) ^ self::safe_intval($t0 + $t1 + $K[--$ki]);
            $t0 = $S0[$R2 & 0xff] ^ $S1[$R2 >> 8 & 0xff] ^ $S2[$R2 >> 16 & 0xff] ^ $S3[$R2 >> 24 & 0xff];
            $t1 = $S0[$R3 >> 24 & 0xff] ^ $S1[$R3 & 0xff] ^ $S2[$R3 >> 8 & 0xff] ^ $S3[$R3 >> 16 & 0xff];
            $R1 ^= self::safe_intval($t0 + ($t1 << 1) + $K[--$ki]);
            $R1 = $R1 >> 1 & 0x7fffffff | $R1 << 31;
            $R0 = ($R0 >> 31 & 0x1 | $R0 << 1) ^ self::safe_intval($t0 + $t1 + $K[--$ki]);
        }
        // @codingStandardsIgnoreStart
        return \pack("V4", $K[0] ^ $R2, $K[1] ^ $R3, $K[2] ^ $R0, $K[3] ^ $R1);
        // @codingStandardsIgnoreEnd
    }
    /**
     * Setup the performance-optimized function for de/encrypt()
     *
     * @see \phpseclib3\Crypt\Common\SymmetricKey::_setupInlineCrypt()
     */
    protected function setupInlineCrypt()
    {
        $K = $this->K;
        $init_crypt = '
            static $S0, $S1, $S2, $S3;
            if (!$S0) {
                for ($i = 0; $i < 256; ++$i) {
                    $S0[] = (int)$this->S0[$i];
                    $S1[] = (int)$this->S1[$i];
                    $S2[] = (int)$this->S2[$i];
                    $S3[] = (int)$this->S3[$i];
                }
            }
        ';
        $safeint = self::safe_intval_inline();
        // Generating encrypt code:
        $encrypt_block = '
            $in = unpack("V4", $in);
            $R0 = ' . $K[0] . ' ^ $in[1];
            $R1 = ' . $K[1] . ' ^ $in[2];
            $R2 = ' . $K[2] . ' ^ $in[3];
            $R3 = ' . $K[3] . ' ^ $in[4];
        ';
        for ($ki = 7, $i = 0; $i < 8; ++$i) {
            $encrypt_block .= '
                $t0 = $S0[ $R0        & 0xff] ^
                      $S1[($R0 >>  8) & 0xff] ^
                      $S2[($R0 >> 16) & 0xff] ^
                      $S3[($R0 >> 24) & 0xff];
                $t1 = $S0[($R1 >> 24) & 0xff] ^
                      $S1[ $R1        & 0xff] ^
                      $S2[($R1 >>  8) & 0xff] ^
                      $S3[($R1 >> 16) & 0xff];
                    $R2^= ' . \sprintf($safeint, '$t0 + $t1 + ' . $K[++$ki]) . ';
                $R2 = ($R2 >> 1 & 0x7fffffff) | ($R2 << 31);
                $R3 = ((($R3 >> 31) & 1) | ($R3 << 1)) ^ ' . \sprintf($safeint, '($t0 + ($t1 << 1) + ' . $K[++$ki] . ')') . ';

                $t0 = $S0[ $R2        & 0xff] ^
                      $S1[($R2 >>  8) & 0xff] ^
                      $S2[($R2 >> 16) & 0xff] ^
                      $S3[($R2 >> 24) & 0xff];
                $t1 = $S0[($R3 >> 24) & 0xff] ^
                      $S1[ $R3        & 0xff] ^
                      $S2[($R3 >>  8) & 0xff] ^
                      $S3[($R3 >> 16) & 0xff];
                $R0^= ' . \sprintf($safeint, '($t0 + $t1 + ' . $K[++$ki] . ')') . ';
                $R0 = ($R0 >> 1 & 0x7fffffff) | ($R0 << 31);
                $R1 = ((($R1 >> 31) & 1) | ($R1 << 1)) ^ ' . \sprintf($safeint, '($t0 + ($t1 << 1) + ' . $K[++$ki] . ')') . ';
            ';
        }
        $encrypt_block .= '
            $in = pack("V4", ' . $K[4] . ' ^ $R2,
                             ' . $K[5] . ' ^ $R3,
                             ' . $K[6] . ' ^ $R0,
                             ' . $K[7] . ' ^ $R1);
        ';
        // Generating decrypt code:
        $decrypt_block = '
            $in = unpack("V4", $in);
            $R0 = ' . $K[4] . ' ^ $in[1];
            $R1 = ' . $K[5] . ' ^ $in[2];
            $R2 = ' . $K[6] . ' ^ $in[3];
            $R3 = ' . $K[7] . ' ^ $in[4];
        ';
        for ($ki = 40, $i = 0; $i < 8; ++$i) {
            $decrypt_block .= '
                $t0 = $S0[$R0       & 0xff] ^
                      $S1[$R0 >>  8 & 0xff] ^
                      $S2[$R0 >> 16 & 0xff] ^
                      $S3[$R0 >> 24 & 0xff];
                $t1 = $S0[$R1 >> 24 & 0xff] ^
                      $S1[$R1       & 0xff] ^
                      $S2[$R1 >>  8 & 0xff] ^
                      $S3[$R1 >> 16 & 0xff];
                $R3^= ' . \sprintf($safeint, '$t0 + ($t1 << 1) + ' . $K[--$ki]) . ';
                $R3 = $R3 >> 1 & 0x7fffffff | $R3 << 31;
                $R2 = ($R2 >> 31 & 0x1 | $R2 << 1) ^ ' . \sprintf($safeint, '($t0 + $t1 + ' . $K[--$ki] . ')') . ';

                $t0 = $S0[$R2       & 0xff] ^
                      $S1[$R2 >>  8 & 0xff] ^
                      $S2[$R2 >> 16 & 0xff] ^
                      $S3[$R2 >> 24 & 0xff];
                $t1 = $S0[$R3 >> 24 & 0xff] ^
                      $S1[$R3       & 0xff] ^
                      $S2[$R3 >>  8 & 0xff] ^
                      $S3[$R3 >> 16 & 0xff];
                $R1^= ' . \sprintf($safeint, '$t0 + ($t1 << 1) + ' . $K[--$ki]) . ';
                $R1 = $R1 >> 1 & 0x7fffffff | $R1 << 31;
                $R0 = ($R0 >> 31 & 0x1 | $R0 << 1) ^ ' . \sprintf($safeint, '($t0 + $t1 + ' . $K[--$ki] . ')') . ';
            ';
        }
        $decrypt_block .= '
            $in = pack("V4", ' . $K[0] . ' ^ $R2,
                             ' . $K[1] . ' ^ $R3,
                             ' . $K[2] . ' ^ $R0,
                             ' . $K[3] . ' ^ $R1);
        ';
        $this->inline_crypt = $this->createInlineCryptFunction(['init_crypt' => $init_crypt, 'init_encrypt' => '', 'init_decrypt' => '', 'encrypt_block' => $encrypt_block, 'decrypt_block' => $decrypt_block]);
    }
}

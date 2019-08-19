<?php

namespace AlibabaCloud\Tea\Tests\Unit;

use PHPUnit\Framework\TestCase;
use AlibabaCloud\RpcClient\RpcClient;

/**
 * Class RpcClientTest
 *
 * @package AlibabaCloud\Tea\Tests\Unit
 */
class RpcClientTest extends TestCase
{
    public static function testConstruct()
    {
        $request = new RpcClient(
            'name',
                                 [
                                     'endpoint' => 'aliyun.com',
                                     '@http'    => [
                                         'proxy' => 'http://192.168.16.1:10',
                                     ],
                                 ]
        );

        self::assertInstanceOf(RpcClient::class, $request);
    }
}

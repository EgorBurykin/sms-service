<?php

/**
 * Copyright 2018, Egor Burykin <c5.Jett@gmail.com>.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests\Unit\Scheduler;

use PHPUnit\Framework\TestCase;
use Scheduler\Response;

/**
 * Class ResponseTest.
 */
class ResponseTest extends TestCase
{
    /**
     * @runInSeparateProcess
     */
    public function testSend()
    {
        $resp = new Response('test');
        ob_start();
        $resp->send();
        $out = ob_get_clean();
        $this->assertJsonStringEqualsJsonString('{"message":"test"}', $out);
        $resp = new Response('test', Response::SERVER_ERROR);
        ob_start();
        $resp->send();
        $out = ob_get_clean();
        $this->assertJsonStringEqualsJsonString('{"error":"test"}', $out);
    }
}

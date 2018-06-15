<?php

/**
 * Copyright 2018, Egor Burykin <c5.Jett@gmail.com>.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests\Unit\Executor;

use Executor\Request;
use Model\SMSMessage;
use PHPUnit\Framework\TestCase;

/**
 * Class RequestTest.
 */
class RequestTest extends TestCase
{
    public function testSimpleMessage()
    {
        $sms = new SMSMessage();
        $sms->body = str_repeat('x', 160);
        $sms->originator = 'test';
        $sms->recipients = ['31612345678'];
        $req = new Request($sms);
        $messages = $req->getParts();
        $this->assertCount(1, $messages);
        $msg = $messages[0];
        $this->assertEquals($sms->body, $msg->body);
        $this->assertEquals($sms->recipients, $msg->recipients);
        $this->assertEquals($sms->originator, $msg->originator);
    }

    public function testComplexMessage()
    {
        $sms = new SMSMessage();
        $sms->body = str_repeat('x', 3 * 153);
        $sms->originator = 'test';
        $sms->recipients = ['31612345678'];
        $request = new Request($sms);
        $messages = $request->getParts();
        $this->assertCount(3, $messages);
        $partNo = 0;
        foreach ($messages as $msg) {
            ++$partNo;
            $this->assertNotEmpty($msg->typeDetails['udh']);
            $udh = $msg->typeDetails['udh'];
            $udh = substr($udh, 0, 6).'00'.substr($udh, 8);
            $this->assertEquals('0500030003'.'0'.$partNo, $udh);
            $this->assertEquals($sms->recipients, $msg->recipients);
            $this->assertEquals($sms->originator, $msg->originator);
        }
    }
}

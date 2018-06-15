<?php

/**
 * Copyright 2018, Egor Burykin <c5.Jett@gmail.com>.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests\Integration\App;

use App\AppFactory;
use Model\SMSMessage;
use PHPUnit\Framework\TestCase;

/**
 * Class AppTest.
 *
 * Abstract class for testing applications
 */
abstract class AppTest extends TestCase
{
    protected $scheduledMessages = [];

    protected function getMessageMock()
    {
        $msg = new SMSMessage();
        $msg->originator = 'test';
        $msg->body = 'Test message';
        $msg->recipients = ['31612345678'];

        return $msg;
    }

    protected function getFactoryMock()
    {
        $factory = $this->getMockBuilder(AppFactory::class)->setMethods([
            'getAMQPChannel', 'getAMQPQueue', 'getAMQPExchange', 'getMessage', 'getClient',
        ])->getMock();
        $ex = $this->createMock(\AMQPExchange::class);
        $ex->expects($this->any())->method('publish')->willReturnCallback(function ($msg) {
            $this->scheduledMessages[] = $msg;
        });
        $factory->expects($this->any())->method('getAMQPExchange')->willReturn($ex);

        return $factory;
    }
}

<?php

/**
 * Copyright 2018, Egor Burykin <c5.Jett@gmail.com>.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests\Unit\Scheduler;

use App\MessageValidator;
use Model\SMSMessage;
use PHPUnit\Framework\TestCase;
use Scheduler\AMQPScheduler;

/**
 * Class AMQPSchedulerTest.
 */
class AMQPSchedulerTest extends TestCase
{
    private $message;

    public function setUp()
    {
        $this->message = new SMSMessage();
    }

    public function testSchedule()
    {
        $exchange = $this->createMock(\AMQPExchange::class);
        $scheduledMessage = null;
        $exchange->expects($this->once())->method('publish')
            ->willReturnCallback(function ($msg) use (&$scheduledMessage) {
                $scheduledMessage = $msg;
            });
        $queue = $this->createMock(\AMQPQueue::class);
        $scheduler = new AMQPScheduler($exchange, $queue, $this->getValidatorMock());
        $scheduler->schedule($this->message);
        $this->assertJsonStringEqualsJsonString(json_encode($this->message), $scheduledMessage);
    }

    public function testConsumeRightMessage()
    {
        $exchange = $this->createMock(\AMQPExchange::class);
        $queue = $this->getQueueMock();
        $queue->expects($this->once())->method('ack');
        $validator = $this->getValidatorMock();

        $scheduler = new AMQPScheduler($exchange, $queue, $validator);

        $scheduler->consume(function (SMSMessage $msg) {
            $this->assertEquals($this->message, $msg);
        });
    }

    public function testConsumeInvalidMessage()
    {
        $exchange = $this->createMock(\AMQPExchange::class);
        $queue = $this->getQueueMock();
        $queue->expects($this->once())->method('reject');
        $validator = $this->getValidatorMock([MessageValidator::ORIGINATOR_INVALID]);
        $scheduler = new AMQPScheduler($exchange, $queue, $validator);

        $scheduler->consume(function () {
            $this->fail();
        });
    }

    private function getValidatorMock($errors = [])
    {
        $validator = $this->createMock(MessageValidator::class);
        $validator->expects($this->any())
            ->method('validate')
            ->willReturn($errors);

        return $validator;
    }

    private function getQueueMock()
    {
        $queue = $this->createMock(\AMQPQueue::class);

        $queue->expects($this->any())->method('consume')->willReturnCallback(function ($call) {
            $envelope = $this->createMock(\AMQPEnvelope::class);
            $envelope->expects($this->once())->method('getBody')->willReturn(json_encode($this->message));
            $call($envelope);
        });

        return $queue;
    }
}

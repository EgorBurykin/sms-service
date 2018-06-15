<?php

/**
 * Copyright 2018, Egor Burykin <c5.Jett@gmail.com>.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests\Integration\App;

use App\ExecutorApp;
use MessageBird\Resources\Messages;
use Model\SMSMessage;

/**
 * Class ExecutorAppTest.
 *
 * Tests Executor application
 */
class ExecutorAppTest extends AppTest
{
    private $messages = [];
    private $messagesSent = [];
    private $messagesTime;
    private $delta = 1;
    private $app;

    public function setUp()
    {
        $this->messages[] = new SMSMessage();

        for ($i = 0; $i < 9; ++$i) {
            $this->messages[] = new SMSMessage('Test'.$i, 'test', ['123456789'.$i]);
        }

        $factory = $this->getFactoryMock();

        $queue = $this->getQueueMock();
        $factory->expects($this->any())->method('getAMQPQueue')->willReturn($queue);

        $client = $this->getClientMock();
        $factory->expects($this->any())->method('getClient')->willReturn($client);

        $this->app = new ExecutorApp($factory);
    }

    public function testExecute()
    {
        $this->app->execute();

        $this->assertGreaterThan(1, $this->delta);

        $this->assertCount(count($this->messages) - 1, $this->messagesSent);
    }

    private function getQueueMock()
    {
        $queue = $this->createMock(\AMQPQueue::class);
        $queue->expects($this->any())->method('consume')->willReturnCallback(function ($call) {
            foreach ($this->messages as $message) {
                $envelope = $this->createMock(\AMQPEnvelope::class);
                $envelope->expects($this->once())->method('getBody')
                    ->willReturn(json_encode($message));
                $call($envelope);
            }
        });

        return $queue;
    }

    private function getClientMock()
    {
        $client = $this->getMockBuilder(Messages::class)->disableOriginalConstructor()->getMock();
        $client->expects($this->any())->method('create')->willReturnCallback(function ($msg) {
            $this->messagesSent[] = $msg;
            if (empty($this->messagesTime)) {
                $this->messagesTime = microtime(true);
                $this->delta = $this->messagesTime;
            } else {
                $this->delta = min($this->delta, microtime(true) - $this->messagesTime);
                $this->messagesTime = microtime(true);
            }
        });

        return $client;
    }
}

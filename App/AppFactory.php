<?php

/**
 * Copyright 2018, Egor Burykin <c5.Jett@gmail.com>.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App;

use Executor\Executor;
use Executor\OneSecondExecutor;
use Model\SMSMessage;
use Scheduler\AMQPScheduler;
use Scheduler\Scheduler;

/**
 * Class AppFactory.
 *
 * This factory used to isolate creation and configuration of services
 */
class AppFactory
{
    /**
     * @var \AMQPChannel
     */
    private $amqpChannel;

    public function __destruct()
    {
        if ($this->amqpChannel) {
            $this->amqpChannel->close();
        }
    }

    /**
     * @throws \AMQPConnectionException
     *
     * @return \AMQPChannel
     */
    public function getAMQPChannel(): \AMQPChannel
    {
        if (!$this->amqpChannel) {
            $connection = new \AMQPConnection(['host' => 'localhost', 'login' => 'sms', 'password' => 'sms']);
            $connection->pconnect();
            $this->amqpChannel = new \AMQPChannel($connection);
        }

        return $this->amqpChannel;
    }

    /**
     * @param \AMQPChannel $channel
     *
     * @throws \AMQPChannelException
     * @throws \AMQPConnectionException
     * @throws \AMQPExchangeException
     *
     * @return \AMQPExchange
     */
    public function getAMQPExchange(\AMQPChannel $channel): \AMQPExchange
    {
        $exchange = new \AMQPExchange($channel);
        $exchange->setName('sms');
        $exchange->setType(AMQP_EX_TYPE_DIRECT);
        $exchange->setFlags(AMQP_DURABLE);
        $exchange->declareExchange();

        return $exchange;
    }

    /**
     * @param \AMQPChannel $channel
     *
     * @throws \AMQPChannelException
     * @throws \AMQPConnectionException
     * @throws \AMQPQueueException
     *
     * @return \AMQPQueue
     */
    public function getAMQPQueue(\AMQPChannel $channel): \AMQPQueue
    {
        $queue = new \AMQPQueue($channel);
        $queue->setName('sms');
        $queue->setFlags(AMQP_DURABLE);
        $queue->declareQueue();
        $queue->bind('sms');

        return $queue;
    }

    /**
     * @throws \AMQPChannelException
     * @throws \AMQPConnectionException
     * @throws \AMQPExchangeException
     * @throws \AMQPQueueException
     *
     * @return Scheduler
     */
    public function getScheduler(): Scheduler
    {
        $channel = $this->getAMQPChannel();
        $exchange = $this->getAMQPExchange($channel);
        $queue = $this->getAMQPQueue($channel);

        return new AMQPScheduler($exchange, $queue, $this->getValidator());
    }

    /**
     * @throws \AMQPChannelException
     * @throws \AMQPConnectionException
     * @throws \AMQPExchangeException
     * @throws \AMQPQueueException
     *
     * @return Executor
     */
    public function getExecutor(): Executor
    {
        return new OneSecondExecutor($this->getScheduler(), $this->getClient());
    }

    public function getMessage(): SMSMessage
    {
        return SMSMessage::createFromInput(file_get_contents('php://input'));
    }

    public function getValidator(): MessageValidator
    {
        return new MessageValidator();
    }

    public function getClient()
    {
        $client = new \MessageBird\Client('YOUR_ACCESS_KEY');

        return $client->messages;
    }
}

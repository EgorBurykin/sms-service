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
     * @throws \AMQPConnectionException
     *
     * @return \AMQPChannel
     */
    public function getAMQPChannel(): \AMQPChannel
    {
        $connection = new \AMQPConnection([
            'host' => Config::QUEUE_HOST,
            'login' => Config::QUEUE_USER,
            'password' => Config::QUEUE_PASSWORD
        ]);
        $connection->pconnect();
        return new \AMQPChannel($connection);
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
        $exchange->setName(Config::QUEUE_NAME);
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
        $queue->setName(Config::QUEUE_NAME);
        $queue->setFlags(AMQP_DURABLE);
        $queue->declareQueue();
        $queue->bind(Config::QUEUE_NAME);

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
        $client = new \MessageBird\Client(Config::API_KEY);

        return $client->messages;
    }
}

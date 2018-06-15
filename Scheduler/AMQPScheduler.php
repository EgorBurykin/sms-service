<?php

/**
 * Copyright 2018, Egor Burykin <c5.Jett@gmail.com>.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Scheduler;

use App\MessageValidator;
use Exception\ValidationException;
use Model\SMSMessage;

/**
 * Class AMQPScheduler.
 *
 * Responsible for scheduling messages for delivery and consuming them.
 */
class AMQPScheduler implements Scheduler
{
    private $queue;
    private $exchange;
    private $validator;

    public function __construct(\AMQPExchange $exchange, \AMQPQueue $queue, MessageValidator $validator)
    {
        $this->queue = $queue;
        $this->exchange = $exchange;
        $this->validator = $validator;
    }

    /**
     * Schedules message for delivery.
     *
     * @param SMSMessage $message
     *
     * @throws ValidationException
     * @throws \AMQPChannelException
     * @throws \AMQPConnectionException
     * @throws \AMQPExchangeException
     */
    public function schedule(SMSMessage $message)
    {
        if (count($errors = $this->validator->validate($message))) {
            throw new ValidationException($errors);
        }
        $this->exchange->publish(json_encode($message));
    }

    /**
     * Consumes messages from delivery queue.
     *
     * @param $callback
     *
     * @throws \AMQPChannelException
     * @throws \AMQPConnectionException
     * @throws \AMQPEnvelopeException
     */
    public function consume($callback)
    {
        $this->queue->consume(function (\AMQPEnvelope $envelope) use (&$callback) {
            $message = SMSMessage::createFromInput($envelope->getBody());
            if (count($this->validator->validate($message))) {
                $this->queue->reject($envelope->getDeliveryTag());

                return;
            }
            $callback($message);
            $this->queue->ack($envelope->getDeliveryTag());
        });
    }
}

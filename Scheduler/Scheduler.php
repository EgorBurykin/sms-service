<?php

/**
 * Copyright 2018, Egor Burykin <c5.Jett@gmail.com>.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Scheduler;

use Exception\ValidationException;
use Model\SMSMessage;

/**
 * Interface Scheduler.
 *
 * Responsible for scheduling messages for delivery and consuming them.
 */
interface Scheduler
{
    /**
     * Schedules message for delivery.
     *
     * @param SMSMessage $message
     *
     * @throws ValidationException - if message is invalid
     * @throws \Exception          - other failures
     *
     * @return mixed
     */
    public function schedule(SMSMessage $message);

    /**
     * Consumes messages from delivery queue.
     *
     * @param $callback
     *
     * @throws \Exception
     */
    public function consume($callback);
}

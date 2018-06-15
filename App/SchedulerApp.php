<?php

/**
 * Copyright 2018, Egor Burykin <c5.Jett@gmail.com>.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App;

use Exception\ValidationException;
use Scheduler\Response;

/**
 * Class SchedulerApp.
 *
 * Main class of Scheduler application.
 */
class SchedulerApp extends App
{
    const INVITE_MSG = 'Post message to this end-point send it.';
    const SCHEDULE_MSG = 'Message has been scheduled for delivery.';

    public function execute($method = null)
    {
        switch ($method ?: $_SERVER['REQUEST_METHOD']) {
            case 'POST':
                return $this->schedule();
            case 'GET':
                return new Response(self::INVITE_MSG);
            default:
                return new Response(null, Response::METHOD_NOT_ALLOWED);
        }
    }

    private function schedule()
    {
        try {
            $message = $this->factory->getMessage();
            $this->factory->getScheduler()->schedule($message);

            return new Response(self::SCHEDULE_MSG);
        } catch (ValidationException $ex) {
            return new Response($ex->getErrors(), Response::UNPROCESSABLE_ENTITY);
        } catch (\Exception $ex) {
            return new Response($ex->getMessage(), Response::SERVER_ERROR);
        }
    }
}

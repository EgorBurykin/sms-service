<?php

/**
 * Copyright 2018, Egor Burykin <c5.Jett@gmail.com>.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Executor;

use MessageBird\Resources\Messages;
use Model\SMSMessage;
use Scheduler\Scheduler;

/**
 * Class OneSecondExecutor.
 */
class OneSecondExecutor implements Executor
{
    const TIMEOUT = 1;

    private $scheduler;

    private $time = 0;

    private $client;

    public function __construct(Scheduler $scheduler, Messages $client)
    {
        $this->scheduler = $scheduler;
        $this->client = $client;
    }

    public function execute()
    {
        $this->scheduler->consume(function (SMSMessage $message) {
            $this->makeRequest(new Request($message));
            return true;
        });
    }

    /**
     * TODO: More then one request per second can be made after reloading.
     */
    private function takeTime()
    {
        $current = microtime(true);
        if ($this->time && ($current - $this->time < self::TIMEOUT)) {
            $secsToWait = self::TIMEOUT - ($current - $this->time);
            usleep($secsToWait * 10 ** 6);
        }

        $this->time = microtime(true);
    }

    /**
     * @param Request $request
     *
     * @throws \MessageBird\Exceptions\HttpException
     * @throws \MessageBird\Exceptions\RequestException
     * @throws \MessageBird\Exceptions\ServerException
     */
    private function makeRequest(Request $request)
    {
        foreach ($request->getParts() as $part) {
            $this->takeTime();
            $this->client->create($part);
            $this->time = microtime(true);
        }
    }
}

<?php

/**
 * Copyright 2018, Egor Burykin <c5.Jett@gmail.com>.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests\Unit\App;

use App\AppFactory;
use App\SchedulerApp;
use PHPUnit\Framework\TestCase;
use Scheduler\Response;
use Scheduler\Scheduler;

/**
 * Class SchedulerAppTest.
 */
class SchedulerAppTest extends TestCase
{
    public function testFailure()
    {
        $factory = $this->createMock(AppFactory::class);
        $executor = $this->createMock(Scheduler::class);
        $executor->expects($this->once())->method('schedule')->willReturnCallback(function () {
            throw new \Exception('Test');
        });
        $factory->expects($this->any())->method('getScheduler')->willReturn($executor);
        $app = new SchedulerApp($factory);
        $response = $app->execute('POST');
        $this->assertEquals(Response::SERVER_ERROR, $response->code);
        $this->assertEquals('Test', $response->content);
    }
}

<?php

/**
 * Copyright 2018, Egor Burykin <c5.Jett@gmail.com>.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests\Integration\App;

use App\SchedulerApp;
use Model\SMSMessage;
use Scheduler\Response;

/**
 * Class SchedulerAppTest.
 */
class SchedulerAppTest extends AppTest
{
    private $message;

    private $app;

    protected function setUp()
    {
        $this->message = new SMSMessage();

        $factory = $this->getFactoryMock();

        $factory->expects($this->any())->method('getMessage')->willReturnCallback(function () {
            return $this->message;
        });

        $this->app = new SchedulerApp($factory);
    }

    public function testExecutePostEmptyMessage()
    {
        $response = $this->app->execute('POST');
        $this->assertEquals(Response::UNPROCESSABLE_ENTITY, $response->code);
        $this->assertEmpty($this->scheduledMessages);
    }

    public function testExecutePostMessage()
    {
        $this->message = $this->getMessageMock();

        $response = $this->app->execute('POST');
        $this->assertEquals(Response::SUCCESS, $response->code);
        $this->assertCount(1, $this->scheduledMessages);
        $this->assertJsonStringEqualsJsonString(json_encode($this->message), $this->scheduledMessages[0]);
    }

    public function testExecuteGet()
    {
        $response = $this->app->execute('GET');
        $this->assertEquals(Response::SUCCESS, $response->code);
    }

    public function testExecuteNotSupportedMethod()
    {
        foreach (['PUT', 'PATCH', 'OPTIONS', 'DELETE', 'HEAD'] as $method) {
            $response = $this->app->execute($method);
            $this->assertEquals(Response::METHOD_NOT_ALLOWED, $response->code);
        }
    }
}

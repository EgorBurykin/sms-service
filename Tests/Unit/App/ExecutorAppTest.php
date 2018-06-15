<?php

/**
 * Copyright 2018, Egor Burykin <c5.Jett@gmail.com>.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests\Unit\App;

use App\AppFactory;
use App\ExecutorApp;
use Executor\Executor;
use PHPUnit\Framework\TestCase;

/**
 * Class ExecutorAppTest.
 */
class ExecutorAppTest extends TestCase
{
    public function testFailure()
    {
        $factory = $this->createMock(AppFactory::class);
        $executor = $this->createMock(Executor::class);
        $executor->expects($this->once())->method('execute')->willReturnCallback(function () {
            throw new \Exception('Test');
        });
        $factory->expects($this->any())->method('getExecutor')->willReturn($executor);
        $app = new ExecutorApp($factory);
        ob_start();
        $app->execute();
        $out = ob_get_clean();
        $this->assertEquals('Test', $out);
    }
}

<?php

/**
 * Copyright 2018, Egor Burykin <c5.Jett@gmail.com>.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
require_once 'vendor/autoload.php';

$app = new App\SchedulerApp(new App\AppFactory());

$response = $app->execute();

$response->send();

<?php

/**
 * Copyright 2018, Egor Burykin <c5.Jett@gmail.com>.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App;

/**
 * Class ExecutorApp.
 *
 * Main class of Executor application.
 */
class ExecutorApp extends App
{
    public function execute()
    {
        try {
            $executor = $this->factory->getExecutor();
            $executor->execute();
        } catch (\Exception $ex) {
            echo $ex->getMessage();
        }

        return 1;
    }
}

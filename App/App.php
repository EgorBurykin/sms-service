<?php

/**
 * Copyright 2018, Egor Burykin <c5.Jett@gmail.com>.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App;

abstract class App
{
    protected $factory;

    public function __construct(AppFactory $factory)
    {
        $this->factory = $factory;
    }

    abstract public function execute();
}

<?php

/**
 * Copyright 2018, Egor Burykin <c5.Jett@gmail.com>.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Model;

use MessageBird\Objects\Base;

/**
 * Class Message.
 *
 * Represents simple (C)SMS message.
 */
class SMSMessage extends Base
{
    public $body;
    public $recipients;
    public $originator;

    public function __construct($body = null, $originator = null, $recipients = null)
    {
        $this->body = $body;
        $this->originator = $originator;
        $this->recipients = $recipients;
    }

    public static function createFromInput($data): self
    {
        $data = json_decode($data, true);

        $msg = new self();

        $msg->loadFromArray($data);

        return $msg;
    }
}

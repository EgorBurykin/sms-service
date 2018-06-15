<?php

/**
 * Copyright 2018, Egor Burykin <c5.Jett@gmail.com>.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Executor;

use MessageBird\Objects\Message;
use Model\SMSMessage;

/**
 * Class Request.
 *
 * Represents request to MessageBird messaging API
 */
class Request
{
    private $messages = [];

    public function __construct(SMSMessage $message)
    {
        if (strlen($message->body) <= 160) {
            $msg = new Message();
            $msg->body = $message->body;
            $msg->originator = $message->originator;
            $msg->recipients = $message->recipients;
            $this->messages[] = $msg;
        } else {
            $parts = str_split($message->body, 153);
            $ref = mt_rand(1, 255);
            $udh = ['udh_length' => '05', 'identifier' => '00', 'header_length' => '03'];
            $udh['reference'] = $this->dechex($ref);
            $udh['msg_count'] = $this->dechex(count($parts));

            $partNo = 1;
            foreach ($parts as $part) {
                $udh['msg_part'] = $this->dechex($partNo);
                $msg = new Message();
                $msg->originator = $message->originator;
                $msg->recipients = $message->recipients;
                $msg->setBinarySms(implode('', $udh), $part);
                $this->messages[] = $msg;
                ++$partNo;
            }
        }
    }

    public function getParts()
    {
        return $this->messages;
    }

    private function dechex($ref)
    {
        return ($ref <= 15) ? '0'.dechex($ref) : dechex($ref);
    }
}

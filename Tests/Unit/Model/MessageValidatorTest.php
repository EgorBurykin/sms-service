<?php

/**
 * Copyright 2018, Egor Burykin <c5.Jett@gmail.com>.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests\Unit\Model;

use App\MessageValidator;
use Model\SMSMessage;
use PHPUnit\Framework\TestCase;

/**
 * Class MessageValidatorTest.
 */
class MessageValidatorTest extends TestCase
{
    private $validator;

    public function setUp()
    {
        $this->validator = new MessageValidator();
    }

    public function testValidateEmpty()
    {
        $msg = new SMSMessage();
        $msg->body = '';
        $msg->originator = '';
        $msg->recipients = '';
        $errors = $this->validator->validate($msg);
        $this->assertCount(3, $errors);
        $this->assertContains(MessageValidator::RECIPIENTS_EMPTY, $errors);
        $this->assertContains(MessageValidator::BODY_EMPTY, $errors);
        $this->assertContains(MessageValidator::ORIGINATOR_INVALID, $errors);
    }

    public function testValidateOriginator()
    {
        foreach (['testtesttest', '-asd134', ''] as $orig) {
            $this->assertFalse(MessageValidator::validateOriginator($orig));
        }
        foreach (['testtest', 'asd123', '31612345678'] as $orig) {
            $this->assertTrue(MessageValidator::validateOriginator($orig));
        }
    }

    public function testValidateRecipient()
    {
        foreach (['testtesttest', '-asd134', ''] as $recipient) {
            $this->assertFalse(MessageValidator::validateRecipient($recipient));
        }
        foreach (['31612345678'] as $recipient) {
            $this->assertTrue(MessageValidator::validateRecipient($recipient));
        }
    }

    public function testValidateLimits()
    {
        $msg = new SMSMessage();
        $msg->body = str_repeat('x', 153 * 255 + 1);
        $msg->originator = 'test';
        $msg->recipients = array_map(function ($no) {
            return substr(str_repeat($no, 10), 0, 10);
        }, range(1, 52));
        $errors = $this->validator->validate($msg);
        $this->assertCount(2, $errors);
        $this->assertContains(MessageValidator::BODY_TOO_LONG, $errors);
        $this->assertContains(MessageValidator::RECIPIENTS_MAX_COUNT_EXCEEDED, $errors);
    }
}

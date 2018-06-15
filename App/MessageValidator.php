<?php

/**
 * Copyright 2018, Egor Burykin <c5.Jett@gmail.com>.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App;

use Model\SMSMessage;

/**
 * Class MessageValidator.
 *
 * Validation service for incoming SMS message
 */
class MessageValidator
{
    /**
     * Constraints.
     */
    const BODY_MAX_LEN = 153 * 255;

    const RECIPIENTS_MAX_COUNT = 50;

    const RECIPIENT_MAX_LEN = 11;

    /**
     * Validation messages.
     */
    const BODY_EMPTY = 'Message should not be empty';

    const RECIPIENTS_EMPTY = 'Recipients list should not be empty';

    const RECIPIENTS_MAX_COUNT_EXCEEDED = 'Recipients max count exceeded';

    const BODY_TOO_LONG = 'Message can not be send as it contains more than '.self::BODY_MAX_LEN.' characters';

    const RECIPIENT_INVALID = 'Recipient %d is invalid';

    const ORIGINATOR_INVALID = 'Originator is invalid';

    public static function validateOriginator($name)
    {
        return self::validateRecipient($name) ||
            preg_match('/^[\w\d]+$/', $name) &&
            strlen($name) < self::RECIPIENT_MAX_LEN;
    }

    public static function validateRecipient($name)
    {
        return (bool) preg_match('/^\+?[1-9]\d{1,14}$/', $name);
    }

    public function validate(SMSMessage $message): array
    {
        $errors = [];

        if (empty($message->body)) {
            $errors[] = self::BODY_EMPTY;
        }

        if (strlen($message->body) > self::BODY_MAX_LEN) {
            $errors[] = self::BODY_TOO_LONG;
        }

        if (!self::validateOriginator($message->originator)) {
            $errors[] = self::ORIGINATOR_INVALID;
        }

        if (empty($message->recipients) || !is_array($message->recipients)) {
            $errors[] = self::RECIPIENTS_EMPTY;
        }

        if (is_array($message->recipients) && count($message->recipients) > self::RECIPIENTS_MAX_COUNT) {
            $errors[] = self::RECIPIENTS_MAX_COUNT_EXCEEDED;
        }

        if (is_array($message->recipients)) {
            $count = count($message->recipients);
            for ($i = 0; $i < $count; ++$i) {
                if (!self::validateRecipient($message->recipients[$i])) {
                    $errors[] = sprintf(self::RECIPIENT_INVALID, $i + 1);
                }
            }
        }

        return $errors;
    }
}

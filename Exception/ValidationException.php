<?php

/**
 * Copyright 2018, Egor Burykin <c5.Jett@gmail.com>.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Exception;

/**
 * Class ValidationException.
 */
class ValidationException extends \Exception
{
    const MESSAGE = 'Validation failed';

    private $errors;

    public function __construct(array $errors)
    {
        parent::__construct(self::MESSAGE);
        $this->errors = $errors;
    }

    /**
     * @return array
     */
    public function getErrors(): array
    {
        return $this->errors;
    }
}

<?php

/**
 * Copyright 2018, Egor Burykin <c5.Jett@gmail.com>.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Scheduler;

/**
 * Class Response.
 *
 * HTTP Response.
 */
class Response
{
    /**
     * HTTP Response codes.
     */
    const SUCCESS = 200;
    const UNPROCESSABLE_ENTITY = 422;
    const METHOD_NOT_ALLOWED = 405;
    const SERVER_ERROR = 500;

    /**
     * @var int - HTTP Response code
     */
    public $code;

    /**
     * @var mixed - content
     */
    public $content;

    public function __construct($content, $code = self::SUCCESS)
    {
        $this->content = $content;
        $this->code = $code;
    }

    /**
     * Sends response to buffer.
     */
    public function send()
    {
        header('Content-Type: application/json; charset=utf-8');
        http_response_code($this->code);
        if ($this->code > 400) {
            echo json_encode(['error' => $this->content]);
        } else {
            echo json_encode(['message' => $this->content]);
        }
    }
}

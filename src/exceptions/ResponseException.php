<?php
    namespace MashCoding\AlexaPHPFramework\exceptions;

    use MashCoding\AlexaPHPFramework\helper\SSMLHelper;

    class ResponseException extends \Exception
    {
        const CODE_ERROR   = 503;
        const CODE_FATAL   = 500;
        const CODE_REPROMT = 404;

        public function sayMessage ()
        {
            return ($this->getCode() !== self::CODE_FATAL) ? SSMLHelper::say($this->getMessage()) : $this->getMessage();
        }
    }
<?php
    namespace MashCoding\AlexaPHPFramework\exceptions;

    use MashCoding\AlexaPHPFramework\helper\LocalizationHelper;
    use MashCoding\AlexaPHPFramework\helper\SSMLHelper;
    use Prophecy\Exception\Exception;

    class ResponseException extends \Exception
    {
        const CODE_STOP    = 0;
        const CODE_ERROR   = 503;
        const CODE_FATAL   = 500;
        const CODE_REPROMT = 404;

        public function sayMessage ()
        {
            return ($this->getCode() !== self::CODE_FATAL) ? SSMLHelper::say($this->getMessage()) : $this->getMessage();
        }

        public function __construct ($message = "", $code = self::CODE_ERROR, $previous = null)
        {
            $message = LocalizationHelper::localize($message);
            if (strpos(strtolower($message), 'error:') === false && ($code == self::CODE_ERROR || $code == self::CODE_FATAL))
                $message = "Error: " . $message;

            parent::__construct($message, $code, $previous);
        }
    }
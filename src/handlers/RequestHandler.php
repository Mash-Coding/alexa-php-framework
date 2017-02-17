<?php
    namespace MashCoding\AlexaPHPFramework\handlers;

    use MashCoding\AlexaPHPFramework\exceptions\ResponseException;
    use MashCoding\AlexaPHPFramework\helper\LocalizationHelper;
    use MashCoding\AlexaPHPFramework\helper\ObjectHelper;
    use MashCoding\AlexaPHPFramework\helper\SettingsHelper;
    use MashCoding\AlexaPHPFramework\Request;
    use MashCoding\AlexaPHPFramework\Response;

    class RequestHandler
    {
        /**
         * @var Response
         */
        protected $Response;

        public static function getHandler (Response &$Response)
        {
            $type = $Response->__request->request->type;
            $handler = str_replace(ObjectHelper::getClassname(self::class), ucfirst($type) . 'Handler', self::class);

            if (!$Response->hasProperties() || !$Response->__request->hasProperties())
                throw new ResponseException("invalid request", ResponseException::CODE_FATAL);
            else if (!in_array($type, Request::$VALID_TYPES))
                throw new ResponseException("unknown request type '" . $type . "'", ResponseException::CODE_FATAL);
            else if (!class_exists($handler))
                throw new ResponseException("unknown request handler '" . ObjectHelper::getClassname($handler) . "'", ResponseException::CODE_FATAL);

            return new $handler($Response);
        }

        public function __construct (Response &$Response)
        {
            $this->Response = $Response;
        }
    }
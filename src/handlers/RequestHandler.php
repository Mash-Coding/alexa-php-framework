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

        public function __get ($name)
        {
            switch ($name) {
                case "request":
                    $val = $this->Response->__request->request;
                break;

                default:
                    $val = null;
            };
            return $val;
        }

        public static function execResponse (Response &$Response)
        {
            $type = $Response->__request->request->type;
            $handler = str_replace(ObjectHelper::getClassname(self::class), ucfirst($type) . 'Handler', self::class);

            if (!$Response->hasProperties() || !$Response->__request->hasProperties())
                throw new ResponseException(LocalizationHelper::localize("invalid", ["request"]), ResponseException::CODE_FATAL);
            else if (!in_array($type, Request::$VALID_TYPES))
                throw new ResponseException(LocalizationHelper::localize("unknown value", ["type" => "request type", "value" => $type]), ResponseException::CODE_FATAL);
            else if (!class_exists($handler))
                throw new ResponseException(LocalizationHelper::localize("unknown value", ["type" => "request handler", "value" => ObjectHelper::getClassname($handler)]), ResponseException::CODE_FATAL);

            /**
             * @var $Handler RequestHandler
             */
            $Handler = new $handler($Response);
            $Handler->run();
        }

        public function run ()
        {
            $this->validate();
        }

        protected function validate ()
        {
            return true;
        }

        public function __construct (Response &$Response)
        {
            $this->Response = $Response;
        }
    }
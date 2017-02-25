<?php
    namespace MashCoding\AlexaPHPFramework\handlers;

    use MashCoding\AlexaPHPFramework\exceptions\ResponseException;
    use MashCoding\AlexaPHPFramework\exceptions\SignatureException;
    use MashCoding\AlexaPHPFramework\helper\LocalizationHelper;
    use MashCoding\AlexaPHPFramework\helper\ObjectHelper;
    use MashCoding\AlexaPHPFramework\helper\SettingsHelper;
    use MashCoding\AlexaPHPFramework\Request;
    use MashCoding\AlexaPHPFramework\Response;
    use MashCoding\AlexaPHPFramework\Skill;

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

                case "application":
                    $val = $this->Response->__request->session->application ?: $this->Response->__request->context->System->application;
                break;

                case "skill":
                    $val = null;
                    if ($this->__skill)
                        $val = $this->__skill;
                    else {
                        $skillId = $this->application->applicationId;
                        $Settings = SettingsHelper::getConfig();
                        if ($Settings->skills->hasProperty($skillId))
                            $val = new Skill($skillId, $Settings->skills->$skillId->data());
                        else
                            throw new SignatureException(400, "skill is not registered as valid skill");

                        $this->__skill = $val;
                    }
                break;

                default:
                    $val = null;
            };
            return $val;
        }

        /**
         * executes the response (and request) with a RequestHandler
         *
         * @param Response $Response
         *
         * @throws ResponseException
         */
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
            $Handler->init();
        }

        /**
         * validates the handler and then runs it
         *
         * @throws ResponseException
         */
        public function init ()
        {
            $this->validate();
            $this->run();
        }

        /**
         * gets called after validating the handler
         */
        protected function run () {}

        /**
         * checks if skill is valid and available
         *
         * @return bool
         * @throws ResponseException
         */
        protected function validate ()
        {
            $Settings = SettingsHelper::getConfig();

            // check if skill is valid
            if (!$Settings->skills->hasProperties())
                throw new ResponseException("no skills", ResponseException::CODE_FATAL);
            else if (!$this->skill)
                throw new ResponseException(LocalizationHelper::localize("unknown", ["skill"]), ResponseException::CODE_FATAL);

            return true;
        }

        public function __construct (Response &$Response)
        {
            $this->Response = $Response;
        }
    }
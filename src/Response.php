<?php
    namespace MashCoding\AlexaPHPFramework;

    use MashCoding\AlexaPHPFramework\exceptions\ResponseException;
    use MashCoding\AlexaPHPFramework\helper\JSONObject;
    use MashCoding\AlexaPHPFramework\helper\SSMLHelper;

    class Response extends JSONObject
    {
        const TYPE_QUESTION = "Repromt";
        const TYPE_MESSAGE  = "Respond";

        private static $BASE_RESPONSE = [
            "version" => null,
            "sessionAttributes" => [],
            "response" => [],
        ];

        public static function fromRequest (Request $Request)
        {
            $ResponseObj = new Response();
            try {
                $ResponseObj->version = $Request->version;

                $ResponseObj->setType($Request->request->type);
            } catch (ResponseException $e) {
                var_dump($e); print ' in ' . __FILE__ . '::' . __LINE__ . PHP_EOL . PHP_EOL;
            }

            return $ResponseObj;
        }
        
        private function setType ($type)
        {
            if (!in_array($type, Request::$VALID_TYPES))
                throw new ResponseException("Invalid Request-Type!", ResponseException::CODE_FATAL);
        }

        public function fetch ()
        {
            if (!$this->response->hasProperties())
                $this->respond(SSMLHelper::say("haha?"));

            return $this->data();
        }

        public function fetchJSON ()
        {
            $this->fetch();
            return parent::json();
        }

        public function respond ($message)
        {
            return $this->addResponse($message, self::TYPE_MESSAGE);
        }
        public function ask ($message)
        {
            return $this->addResponse($message, self::TYPE_QUESTION);
        }
        private function addResponse ($message, $type)
        {
            $speech = new OutputSpeech($message);
            switch ($type) {
                case self::TYPE_QUESTION:
                    $this->response->reprompt->outputSpeech = $speech;
                break;

                default:
                    $this->response->outputSpeech = $speech;
            }

            $this->response->shouldEndSession = (!$this->response->hasProperty('repromt'));

            return $this;
        }

        public function __construct ()
        {
            parent::__construct(self::$BASE_RESPONSE);
        }
    }
<?php
    namespace MashCoding\AlexaPHPFramework;

    use MashCoding\AlexaPHPFramework\exceptions\ResponseException;
    use MashCoding\AlexaPHPFramework\handlers\RequestHandler;
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

        public static function fromRequest (Request &$Request)
        {
            $ResponseObj = new Response();
            try {
                $ResponseObj->__request = $Request;
                $ResponseObj->version = $Request->version;
            } catch (ResponseException $e) {
                var_dump($e); print ' in ' . __FILE__ . '::' . __LINE__ . PHP_EOL . PHP_EOL;
            }

            return $ResponseObj;
        }

        private function exec ()
        {
            $Handler = RequestHandler::getHandler($this);

            var_dump($Handler); print ' in ' . __FILE__ . '::' . __LINE__; exit;

            return $this;
        }

        public function fetch ()
        {
            $this->exec();

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
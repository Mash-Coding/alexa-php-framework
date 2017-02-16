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

        /**
         * creates Response-object from given $Request
         *
         * @param Request $Request
         *
         * @return Response
         */
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

        /**
         * executes the request as in parsing the request type and creating a message for skill,
         * intent/launch/sessionend and slots
         *
         * @return $this
         * @throws ResponseException
         */
        private function exec ()
        {
            $Handler = RequestHandler::getHandler($this);
            return $this;
        }

        /**
         * executes the request and creates at least a empty response as fallback.
         *
         * @return array whole response data without request
         * @see Response::exec()
         * @throws ResponseException
         */
        public function fetch ()
        {
            $this->exec();

            if (!$this->response->hasProperties())
                throw new ResponseException("OK", ResponseException::CODE_STOP);

            return $this->data();
        }

        /**
         * fetches response first and then returns data as JSON
         *
         * @return string JSON output of data
         * @see Response::fetch()
         * @see JSONObject::json()
         * @throws ResponseException
         */
        public function fetchJSON ()
        {
            $this->fetch();
            return parent::json();
        }

        /**
         * adds a default message to the response
         *
         * @param $message
         *
         * @return Response
         */
        public function respond ($message)
        {
            return $this->addResponse($message, self::TYPE_MESSAGE);
        }
        /**
         * adds a repromt message, means that Amazon Echo will wait for further information.
         *
         * @param $message
         * @example "Alexa, do stuff" -> Response::ask("What stuff you want me to do?")
         *
         * @return Response
         */
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

        public function appendCard ($cardType = Card::TYPE_STANDARD)
        {
            $card = new Card($cardType, "card", $this->response);
            $this->response->card = $card;
            return $card;
        }

        public function __construct ()
        {
            parent::__construct(self::$BASE_RESPONSE);
        }
    }
<?php
    namespace MashCoding\AlexaPHPFramework\handlers;

    use MashCoding\AlexaPHPFramework\exceptions\ResponseException;

    class IntentRequestHandler extends RequestHandler
    {
        protected function validate ()
        {
            if ($this->request->intent)
                throw new ResponseException("no intent", ResponseException::CODE_FATAL);
        }
    }
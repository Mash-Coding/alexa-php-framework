<?php
    namespace MashCoding\AlexaPHPFramework\handlers;

    use MashCoding\AlexaPHPFramework\exceptions\ResponseException;
    use MashCoding\AlexaPHPFramework\helper\LocalizationHelper;

    class IntentRequestHandler extends RequestHandler
    {
        protected function validate ()
        {
            parent::validate();
            if (!$this->request->intent)
                throw new ResponseException("no intent", ResponseException::CODE_FATAL);
            else if (!$this->skill->intents || !$this->skill->intents->hasProperty($this->request->intent->name))
                throw new ResponseException(LocalizationHelper::localize("unknown value", ["type" => "intent", "value" => $this->request->intent->name]), ResponseException::CODE_FATAL);

        }
    }
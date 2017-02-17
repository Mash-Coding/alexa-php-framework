<?php
    namespace MashCoding\AlexaPHPFramework\handlers;

    use MashCoding\AlexaPHPFramework\exceptions\ResponseException;
    use MashCoding\AlexaPHPFramework\helper\JSONObject;
    use MashCoding\AlexaPHPFramework\helper\LocalizationHelper;
    use MashCoding\AlexaPHPFramework\helper\SettingsHelper;
    use MashCoding\AlexaPHPFramework\Intent;

    class IntentRequestHandler extends RequestHandler
    {
        /**
         * searches for the intent class in the namespace defined in the settings and if non-existent a base Intent
         * class will be created
         *
         * @return Intent
         */
        private function getIntent ()
        {
            $Settings = SettingsHelper::getConfig();
            $class = $Settings->namespace->skills . $this->skill->alias . '\\intents\\' . ucfirst($this->skill->intent->intentClass) . 'Intent';
            if (!class_exists($class))
                $class = Intent::class;

            return new $class($this->Response, $this->skill->intent, $this->request->intent->slots->data());
        }

        /**
         * checks if request is a valid IntentRequest (with a valid skillId and an existing intent) and if so, calls
         * the specified intent
         *
         * @throws ResponseException
         */
        protected function validate ()
        {
            parent::validate();
            if (!$this->request->intent)
                throw new ResponseException("no intent", ResponseException::CODE_FATAL);
            else if (!$this->skill->intents || !$this->skill->intent($this->request->intent->name))
                throw new ResponseException(LocalizationHelper::localize("unknown value", ["type" => "intent", "value" => $this->request->intent->name]), ResponseException::CODE_FATAL);
        }

        protected function run ()
        {
            $Intent = $this->getIntent();
            $Intent->callAction();
        }
    }
<?php
    namespace MashCoding\AlexaPHPFramework;

    use MashCoding\AlexaPHPFramework\helper\FileHelper;
    use MashCoding\AlexaPHPFramework\helper\JSONObject;
    use MashCoding\AlexaPHPFramework\helper\LocalizationHelper;
    use MashCoding\AlexaPHPFramework\helper\SettingsHelper;

    class Skill extends JSONObject
    {
        private static $BASE_STRUCTURE = [
            "name" => "",
            "alias" => "",
            "skillId" => "",
            "intents" => [],
        ];

        private static $BASE_INTENT = [
            "name" => "",
            "intentClass" => "Base",
            "actions" => []
        ];

        public function __get ($name)
        {
            switch ($name) {
                case "intent":
                    $val = $this->__intent;
                break;

                default:
                    $val = parent::__get($name);
            }
            return $val;
        }

        /**
         * creates and set alias properly by the skills name
         */
        private function getAlias ()
        {
            $this->alias = (is_string($this->alias) && $this->alias) ? $this->alias : FileHelper::makeConformName($this->name ?: $this->skillId);
        }

        /**
         * returns the intent data for the current request
         *
         * @param $name
         *
         * @return JSONObject|null
         */
        public function intent ($name)
        {
            if (!$this->__intent || !$this->__intent->hasProperties())
                $this->__intent = ($this->intents->hasProperties() && $this->intents->hasProperty($name)) ? $this->intents->$name->invokeData(array_merge(self::$BASE_INTENT, $this->intents->$name->data(), ["name" => $name])) : null;

            return $this->__intent;
        }

        public function __construct ($skillId, array $data)
        {
            parent::__construct(array_merge(self::$BASE_STRUCTURE, $data, [
                "skillId" => $skillId,
            ]));
            $this->getAlias();

            // load skill-based translation
            $Settings = SettingsHelper::getConfig();
            $Locale = LocalizationHelper::getLocale();
            SettingsHelper::parseConfig($Settings->path->locale . $this->alias . '/' . $Locale->language . '.json', 'localization');
        }
    }
<?php
    namespace MashCoding\AlexaPHPFramework;

    use MashCoding\AlexaPHPFramework\helper\FileHelper;
    use MashCoding\AlexaPHPFramework\helper\JSONObject;

    class Skill extends JSONObject
    {
        private static $BASE_STRUCTURE = [
            "name" => "",
            "alias" => "",
            "skillId" => "",
            "intents" => [],
        ];

        private function getAlias ()
        {
            $this->alias = (is_string($this->alias) && $this->alias) ? $this->alias : FileHelper::makeConformName($this->name ?: $this->skillId);
        }

        public function __construct ($skillId, array $data)
        {
            parent::__construct(array_merge(self::$BASE_STRUCTURE, $data, [
                "skillId" => $skillId,
            ]));
            $this->getAlias();
        }
    }
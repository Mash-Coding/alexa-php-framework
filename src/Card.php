<?php
    namespace MashCoding\AlexaPHPFramework;

    use MashCoding\AlexaPHPFramework\exceptions\ResponseException;
    use MashCoding\AlexaPHPFramework\helper\FileHelper;
    use MashCoding\AlexaPHPFramework\helper\JSONObject;
    use MashCoding\AlexaPHPFramework\helper\URLHelper;

    class Card extends JSONObject
    {
        const TYPE_SIMPLE = 'Simple';
        const TYPE_STANDARD = 'Standard';
        const TYPE_LINK_ACCOUNT = 'LinkAccount';

        private static $TYPE_PROPERTIES = [
            self::TYPE_SIMPLE => ["title", "content"],
            self::TYPE_STANDARD => ["title", "text", "image"],
            self::TYPE_LINK_ACCOUNT => [],
        ];

        private static $IMAGE_EXTENSIONS = [
            FileHelper::EXTENSION_PNG, FileHelper::EXTENSION_JPEG,
        ];

        public static function getType (&$type)
        {
            return (array_key_exists($type, self::$TYPE_PROPERTIES)) ? $type : null;
        }

        private function isValidProperty ($property)
        {
            return ($this->type && $property && in_array($property, self::$TYPE_PROPERTIES[$this->type]));
        }

        private function setProperty ($name, $value)
        {
            if ($this->isValidProperty($name))
                $this->setData($name, (!is_array($value)) ? strip_tags($value) : $value);
        }

        public function setTitle ($title)
        {
            $this->setProperty("title", $title);
            return $this;
        }

        public function setText ($text)
        {
            $this->setProperty(($this->type == self::TYPE_SIMPLE) ? "content" : "text", $text);
            return $this;
        }

        public function setImage ($imageBig, $imageSmall)
        {
            if (URLHelper::isValidURL($imageBig, URLHelper::PROTOCOL_HTTPS) && in_array(FileHelper::getFileExtension($imageBig), self::$IMAGE_EXTENSIONS) &&
                URLHelper::isValidURL($imageSmall, URLHelper::PROTOCOL_HTTPS) && in_array(FileHelper::getFileExtension($imageSmall), self::$IMAGE_EXTENSIONS))
                $this->setProperty("image", ["smallImageUrl" => $imageSmall, "largeImageUrl" => $imageBig]);
        }

        public function __construct ($type = self::TYPE_SIMPLE, $name, &$parent)
        {
            self::getType($type);
            parent::__construct([
                "type" => $type,
            ], $name, $parent);
        }
        
    }
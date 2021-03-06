<?php
    namespace MashCoding\AlexaPHPFramework\helper;

    use MashCoding\AlexaPHPFramework\OutputSpeech;

    class JSONObject
    {
        protected $__data = [];
        protected $__name = "";

        /**
         * @var null|JSONObject
         */
        protected $__parent = null;

        public static function escape ($string)
        {
            return strtr($string, []);
        }

        public static function toCamelCase ($string, $filterWords = [])
        {
            global $filter, $i;
            $i = 0;
            $filter = $filterWords;
            return implode('', array_map(function ($a) {
                global $filter, $i;
                $ret = $a;
                if (!is_array($filter) || !in_array(strtolower($a), $filter))
                    $ret = ($i > 0) ? ucfirst($a) : lcfirst($a);
                $i++;
                return $ret;
            }, explode(' ', strtr($string, [
                ':' => '',
                '.' => '',
                ',' => '',
            ]))));
        }

        public function __get ($name)
        {
            $value = null;
            if (array_key_exists($name, $this->__data))
                $value = $this->__data[$name];
            else
                $value = [];

            if (is_array($value))
                $value = new JSONObject($value, $name, $this);

            return $value;
        }

        public function __set ($name, $value)
        {
            $this->setData($name, $value);
            return $this;
        }

        /**
         * checks if JSONObject::__data contains any data
         * @return bool
         */
        public function hasProperties ()
        {
            return (!!count($this->data()));
        }
        /**
         * checks if specified $property is set in JSONObject::__data
         *
         * @param $property
         *
         * @return bool
         */
        public function hasProperty ($property)
        {
            return ($this->hasProperties() && array_key_exists($property, $this->data()) && isset($this->data()[$property]));
        }
        /**
         * gets property with name $property
         *
         * @param $property
         *
         * @return array|JSONObject|mixed|null
         */
        public function getProperty ($property)
        {
            return $this->$property;
        }

        /**
         * sets an array of data
         *
         * @param $data
         *
         * @return $this
         * @see JSONObject::setData()
         */
        public function invokeData ($data)
        {
            foreach ($data as $prop => $value) {
                $this->setData($prop, $value);
            };
            return $this;
        }

        /**
         * sets data accordingly to its name (names starting with '__' will be set directly, though all other values
         * are set to JSONObject::__data)
         *
         * @param $name
         * @param $value
         */
        public function setData ($name, $value)
        {
            if (substr($name, 0, 2) == '__') {
                $this->$name = $value;
            } else {
                //                if (array_key_exists($name, $this->__data))
                $this->__data[$name] = (is_object($value) && $value instanceof JSONObject) ? $value->data() : $value;

                if (isset($this->__parent) && $this->__parent instanceof JSONObject)
                    $this->__parent->setData($this->__name, $this->data());
            }
        }

        public function push ($value)
        {
            $this->setData(count($this->data()), $value);
        }

        /**
         * returns the objects data
         * @return array
         */
        public function data ()
        {
            return $this->__data;
        }

        /**
         * returns the objects data as JSON
         *
         * @param int $options
         *
         * @return string
         */
        public function json ($options = 0)
        {
            return json_encode($this->data(), $options);
        }

        public function clear ()
        {
            $this->__data = [];
        }

        public function __toString ()
        {
            return '';
        }

        public function render ($view, $ext = 'tpl', $removeWhitespace = false)
        {
            $Settings = SettingsHelper::getConfig();
            $file = $Settings->path->views . $view . '.' . $ext;
            $render = "";
            if ($Settings->path->views && FileHelper::fileExists($file)) {
                ob_start();
                include $file;
                $render = ob_get_clean();
            }

            return ($removeWhitespace) ? preg_replace('/[^\\S ]+/', '', $render) : $render;
        }

        /**
         * JSONObject constructor.
         *
         * @param array $data
         * @param null  $name
         * @param null  $parent
         */
        public function __construct (array $data = [], $name = null, $parent = null)
        {
            $this->__data = $data;

            if (isset($name) && isset($parent) && $parent instanceof JSONObject) {
                $this->__name = $name;
                $this->__parent = $parent;
            }
        }
    }
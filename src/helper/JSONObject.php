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

        public function hasProperties ()
        {
            return (!!count($this->data()));
        }
        public function hasProperty ($property)
        {
            return ($this->hasProperties() && array_key_exists($property, $this->data()) && isset($this->data()[$property]));
        }

        public function invokeData ($data)
        {
            foreach ($data as $prop => $value) {
                $this->setData($prop, $value);
            };
            return $this;
        }

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

        public function data ()
        {
            return $this->__data;
        }

        public function json ()
        {
            return json_encode($this->data());
        }

        public function __toString ()
        {
            return '';
        }

        public function __construct (array $data, $name = null, $parent = null)
        {
            $this->__data = $data;

            if (isset($name) && isset($parent) && $parent instanceof JSONObject) {
                $this->__name = $name;
                $this->__parent = $parent;
            }
        }
    }
<?php
    namespace MashCoding\AlexaPHPFramework;

    use MashCoding\AlexaPHPFramework\exceptions\ResponseException;
    use MashCoding\AlexaPHPFramework\helper\ArrayHelper;
    use MashCoding\AlexaPHPFramework\helper\JSONObject;
    use MashCoding\AlexaPHPFramework\helper\LocalizationHelper;

    class Intent implements IntentInterface
    {
        protected $registeredActions = [];
        protected $slots = [];
        protected $data;
        protected $Response;

        /**
         * Intent constructor.
         *
         * @param Response   $Response
         * @param JSONObject $intent
         * @param array      $slots
         */
        public function __construct (Response &$Response, JSONObject $intent, array $slots)
        {
            $this->registeredActions = array_map(function ($a) { $a = (is_string($a)) ? [$a] : $a; sort($a, SORT_NATURAL); return implode(',', $a); }, $intent->actions->data());

            ksort($slots, SORT_NATURAL);
            $this->slots = $slots;

            $this->data = $intent;
            $this->Response = $Response;
        }

        /**
         * tries to get the required action by the set slots of the request and calls that action
         *
         * @throws ResponseException
         */
        public function callAction ()
        {
            $action = $this->getActionFromSlots();
            $rawAction = array_search(implode(',', array_keys($this->slots)), $this->registeredActions);
            if (isset($action))
                $this->$action(ArrayHelper::getFilteredArray($this->data->actions->data()[lcfirst(substr($action, 6))], $this->slots));
            else if ($rawAction)
                throw new ResponseException(LocalizationHelper::localize("unknown value", ["type" => "action", "value" => $rawAction]));
            else
                throw new ResponseException(LocalizationHelper::localize("invalid", ["action"]));
        }

        /**
         * tries to get the action method with the set slots of the request
         *
         * @return null|string
         */
        protected function getActionFromSlots ()
        {
            return $this->getAction(array_search(implode(',', array_keys($this->slots)), $this->registeredActions));
        }

        /**
         * checkfs if given action exists
         *
         * @param $action
         *
         * @return null|string
         */
        protected function getAction ($action)
        {
            if ($action && method_exists($this, 'action' . ucfirst($action)))
                return 'action' . ucfirst($action);

            return null;
        }
    }

    interface IntentInterface {
        public function __construct (Response &$Response, JSONObject $intent, array $slots);
    }
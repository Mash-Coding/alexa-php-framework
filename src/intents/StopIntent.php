<?php
    namespace MashCoding\AlexaPHPFramework\intents;

    use MashCoding\AlexaPHPFramework\Intent;

    class StopIntent extends Intent implements StopIntentInterface
    {
        public function actionStop ($slots)
        {
        }
    }

    interface StopIntentInterface {
        public function actionStop ($slots);
    }
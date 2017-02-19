<?php
    namespace MashCoding\AlexaPHPFramework\intents;

    use MashCoding\AlexaPHPFramework\Card;
    use MashCoding\AlexaPHPFramework\helper\SSMLHelper as SSML;
    use MashCoding\AlexaPHPFramework\Intent;

    class HelpIntent extends Intent implements HelpIntentInterface
    {
        public function actionGetHelp ($slots)
        {
            $this->Response
                ->respond(SSML::say($this->Skill->help->text) . SSML::pause(SSML::PAUSE_STRONG) . SSML::sayList($this->Skill->help->examples->data(), SSML::BREAK_SOFT, null, "for example you can say"))
                ->appendCard(Card::TYPE_SIMPLE)
                    ->setTitle((is_string($this->Skill->help->title)) ? $this->Skill->help->title : "About the skill")
                    ->setText($this->Skill->help->text . (($this->Skill->help->examples->hasProperties()) ? PHP_EOL . PHP_EOL . "Usage examples:" . PHP_EOL . implode(PHP_EOL, $this->Skill->help->examples->data()) : ''));
        }
    }

    interface HelpIntentInterface {
        public function actionGetHelp ($slots);
    }
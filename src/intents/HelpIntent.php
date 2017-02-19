<?php
    namespace MashCoding\AlexaPHPFramework\intents;

    use MashCoding\AlexaPHPFramework\Card;
    use MashCoding\AlexaPHPFramework\helper\LocalizationHelper;
    use MashCoding\AlexaPHPFramework\helper\SSMLHelper as SSML;
    use MashCoding\AlexaPHPFramework\Intent;

    class HelpIntent extends Intent implements HelpIntentInterface
    {
        public function actionGetHelp ($slots)
        {
            $localize = [
                "invocationName" => $this->Skill->invocationName,
                "name" => $this->Skill->name,
            ];

            $this->Response
                ->respond(SSML::say($this->Skill->help->text, $localize) . SSML::pause(SSML::PAUSE_STRONG) . LocalizationHelper::localize(SSML::sayList($this->Skill->help->examples->data(), SSML::BREAK_SOFT, null, "for example you can say"), $localize))
                ->appendCard(Card::TYPE_SIMPLE)
                    ->setTitle((is_string($this->Skill->help->title)) ? $this->Skill->help->title : "About the skill")
                    ->setText(LocalizationHelper::localize($this->Skill->help->text . (($this->Skill->help->examples->hasProperties()) ? PHP_EOL . PHP_EOL . "Usage examples:" . PHP_EOL . implode(PHP_EOL, $this->Skill->help->examples->data()) : ''), $localize));
        }
    }

    interface HelpIntentInterface {
        public function actionGetHelp ($slots);
    }
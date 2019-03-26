<?php
declare(strict_types=1);

namespace NamelessCoder\MasterRecord\Hooks;

use NamelessCoder\MasterRecord\RecordInsight;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectPostInitHookInterface;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

class ContentObjectPostInit implements ContentObjectPostInitHookInterface
{
    public function postProcessContentObjectInitialization(ContentObjectRenderer &$parentObject)
    {
        if ($parentObject->data['tx_masterrecord_instanceof']) {
            foreach ((new RecordInsight('tt_content', $parentObject->data))->getDifferences() as $columnName => $difference) {
                $parentObject->data[$columnName] = $difference->getMergedValue();
            }
        }
    }
}
<?php
declare(strict_types=1);

namespace NamelessCoder\MasterRecord\FormElement;

use NamelessCoder\MasterRecord\TcaFieldRenderer;
use TYPO3\CMS\Backend\Form\Element\AbstractFormElement;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class MasterRecordInstances extends AbstractFormElement
{
    public function render()
    {
        return array_replace(
            $this->initializeResultArray(),
            [
                'html' => GeneralUtility::makeInstance(TcaFieldRenderer::class)->renderListOfInstances($this->data)
            ]
        );
    }
}
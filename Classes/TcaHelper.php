<?php
declare(strict_types=1);

namespace NamelessCoder\MasterRecord;

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

class TcaHelper
{
    public static function addFieldsToTable(string $table)
    {
        ExtensionManagementUtility::addTCAcolumns(
            $table,
            [
                't3_origuid' => [
                    'label' => 'LLL:EXT:master_record/Resources/Private/Language/locallang.xlf:tt_content.tx_masterrecord_sync',
                    'config' => [
                        'type' => 'user',
                        'userFunc' => TcaFieldRenderer::class . '->renderField',
                    ]
                ],
            ]
        );

        ExtensionManagementUtility::addToAllTCAtypes(
            $table,
            ',--div--;LLL:EXT:master_record/Resources/Private/Language/locallang.xlf:tt_content.tx_masterrecord_tab,t3_origuid'
        );
    }
}
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
                'tx_masterrecord_instanceof' => [
                    'label' => 'LLL:EXT:master_record/Resources/Private/Language/locallang.xlf:tt_content.tx_masterrecord_instanceof',
                    'displayCond' => 'FIELD:tx_masterrecord_master:<:1',
                    'config' => [
                        'type' => 'user',
                        'userFunc' => TcaFieldRenderer::class . '->renderMasterInstanceSelector',
                    ]
                ],
                'tx_masterrecord_master' => [
                    'label' => 'LLL:EXT:master_record/Resources/Private/Language/locallang.xlf:tt_content.tx_masterrecord_master',
                    'config' => [
                        'type' => 'check',
                    ],
                    'displayCond' => 'FIELD:tx_masterrecord_instanceof:<:1',
                ],
                'tx_masterrecord_instances' => [
                    'label' => 'LLL:EXT:master_record/Resources/Private/Language/locallang.xlf:tt_content.tx_masterrecord_instances',
                    'config' => [
                        'type' => 'user',
                        'userFunc' => TcaFieldRenderer::class . '->renderListOfInstances',
                    ],
                    'displayCond' => 'FIELD:tx_masterrecord_master:>:0',
                ],
            ]
        );

        ExtensionManagementUtility::addToAllTCAtypes(
            $table,
            ',--div--;LLL:EXT:master_record/Resources/Private/Language/locallang.xlf:tt_content.tx_masterrecord_tab,tx_masterrecord_master,tx_masterrecord_instances,tx_masterrecord_instanceof'
        );
    }
}
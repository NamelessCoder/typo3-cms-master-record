<?php
declare(strict_types=1);

namespace NamelessCoder\MasterRecord;

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

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

        if (!empty($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['master_record']['setup']['newContentWizardGroups'])) {
            ExtensionManagementUtility::addTCAcolumns(
                $table,
                [
                    'tx_masterrecord_group' => [
                        'label' => 'LLL:EXT:master_record/Resources/Private/Language/locallang.xlf:tt_content.tx_masterrecord_group',
                        'config' => [
                            'type' => 'select',
                            'items' => static::getGroupItems(),
                        ],
                        'displayCond' => 'FIELD:tx_masterrecord_master:>:0',
                    ],
                ]
            );
        }

        ExtensionManagementUtility::addToAllTCAtypes(
            $table,
            ',--div--;LLL:EXT:master_record/Resources/Private/Language/locallang.xlf:tt_content.tx_masterrecord_tab,tx_masterrecord_master,tx_masterrecord_instances,tx_masterrecord_instanceof,tx_masterrecord_group'
        );
    }

    public static function sanitizeGroupName(string $groupName): string
    {
        if (strncmp($groupName, 'LLL:EXT:', 8) === 0) {
            $groupName = array_pop(explode(':', $groupName));
        }
        return (string) preg_replace('/[^a-z0-9]/', '_', strtolower($groupName));
    }

    protected static function getGroupItems(): array
    {
        $groupNames = GeneralUtility::trimExplode(',', $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['master_record']['setup']['newContentWizardGroups'] ?? '', true);
        $groups = [['LLL:EXT:master_record/Resources/Private/Language/locallang.xlf:tt_content.tx_masterrecord_group.notshown', '']];
        foreach ($groupNames as $groupName) {
            $groups[] = [$groupName, $groupName];
        }
        return $groups;
    }
}

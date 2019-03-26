<?php
defined('TYPO3_MODE') or die('Access denied.');

(function() {
    if (!(TYPO3_REQUESTTYPE & TYPO3_REQUESTTYPE_INSTALL)) {
        $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tslib/class.tslib_content.php']['postInit'][] = \NamelessCoder\MasterRecord\Hooks\ContentObjectPostInit::class;
        if (TYPO3_REQUESTTYPE & TYPO3_REQUESTTYPE_BE) {
            $queryBuilder = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Database\ConnectionPool::class)->getQueryBuilderForTable('tt_content');
            $masterInstances = $queryBuilder->select('*')
                ->from('tt_content')
                ->where($queryBuilder->expr()->eq('tx_masterrecord_master', $queryBuilder->createNamedParameter(1, \PDO::PARAM_INT)))
                ->andWhere($queryBuilder->expr()->in('sys_language_uid', $queryBuilder->createNamedParameter([-1, 0], \Doctrine\DBAL\Connection::PARAM_INT_ARRAY)))
                ->execute()
                ->fetchAll();

            $groupName = 'masters';

            \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig(
                sprintf(
                    '
                    mod.wizards.newContentElement.wizardItems.%s.header = LLL:EXT:master_record/Resources/Private/Language/locallang.xlf:tt_content.tx_masterrecord_tab
                    mod.wizards.newContentElement.wizardItems.%s.show = *
                    ',
                    $groupName,
                    $groupName
                )
            );


            foreach ($masterInstances as $masterRecord) {
                $contentId = 'instanceof_' . $masterRecord['uid'];
                \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig(
                    sprintf(
                        '
                        mod.wizards.newContentElement.wizardItems.%s.elements.%s {
                            iconIdentifier = %s
                            title = %s
                            description = %s
                            tt_content_defValues {
                                CType = %s
                                tx_masterrecord_instanceof = %d
                            }
                        }
                        mod.wizards.newContentElement.wizardItems.%s.show := addToList(%s)
                        ',
                        $groupName,
                        $contentId,
                        'content-' . $masterRecord['CType'],
                        $masterRecord['rowDescription'] ?: 'An instance of tt_content:' . $masterRecord['uid'],
                        'Inserts an instance of tt_content:' . $masterRecord['uid'] . ' of type ' . $masterRecord['CType'],
                        $masterRecord['CType'],
                        $masterRecord['uid'],
                        $groupName,
                        $contentId
                    )
                );
            }
        }
    }
})();
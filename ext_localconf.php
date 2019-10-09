<?php

defined('TYPO3_MODE') or die('Access denied.');

(function($conf) {

    if ($conf === null && class_exists(\TYPO3\CMS\Core\Configuration\ExtensionConfiguration::class)) {
        $conf = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Configuration\ExtensionConfiguration::class)->get('master_record');
    } else {
        $conf = unserialize($conf);
    }

    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['master_record']['setup'] = $conf;

    $GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['nodeRegistry'][] = [
        'nodeName' => 'masterRecordInstanceOf',
        'priority' => 40,
        'class' => \NamelessCoder\MasterRecord\FormElement\MasterRecordInstanceOf::class,
    ];
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['nodeRegistry'][] = [
        'nodeName' => 'masterRecordInstances',
        'priority' => 40,
        'class' => \NamelessCoder\MasterRecord\FormElement\MasterRecordInstances::class,
    ];

    if (!(TYPO3_REQUESTTYPE & TYPO3_REQUESTTYPE_INSTALL) && !empty($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['master_record']['setup']['newContentWizardGroups'])) {

        $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tslib/class.tslib_content.php']['postInit'][] = \NamelessCoder\MasterRecord\Hooks\ContentObjectPostInit::class;

        if (TYPO3_REQUESTTYPE & TYPO3_REQUESTTYPE_BE) {
            $queryBuilder = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Database\ConnectionPool::class)->getQueryBuilderForTable('tt_content');
            try {
                $masterInstances = $queryBuilder->select('*')
                    ->from('tt_content')
                    ->where($queryBuilder->expr()->eq('tx_masterrecord_master', $queryBuilder->createNamedParameter(1, \PDO::PARAM_INT)))
                    ->andWhere($queryBuilder->expr()->in('sys_language_uid', $queryBuilder->createNamedParameter([-1, 0], \Doctrine\DBAL\Connection::PARAM_INT_ARRAY)))
                    ->andWhere($queryBuilder->expr()->neq('tx_masterrecord_group', $queryBuilder->createNamedParameter('', \PDO::PARAM_STR)))
                    ->execute()
                    ->fetchAll();

            } catch (\Doctrine\DBAL\Exception\InvalidFieldNameException $exception) {
                $masterInstances = [];
            }

            $groupNames = array_column($masterInstances, 'tx_masterrecord_group');
            foreach ($groupNames as $groupName) {
                $sanitizedGroupName = \NamelessCoder\MasterRecord\TcaHelper::sanitizeGroupName($groupName);
                \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig(
                    sprintf(
                        '
                        mod.wizards.newContentElement.wizardItems.%s.header = %s
                        mod.wizards.newContentElement.wizardItems.%s.show = *
                        ',
                        $sanitizedGroupName,
                        $groupName,
                        $sanitizedGroupName
                    )
                );

            }

            foreach ($masterInstances as $masterRecord) {
                $contentId = 'instanceof_' . $masterRecord['uid'];
                $sanitizedGroupName = \NamelessCoder\MasterRecord\TcaHelper::sanitizeGroupName($masterRecord['tx_masterrecord_group']);
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
                        $sanitizedGroupName,
                        $contentId,
                        'content-' . $masterRecord['CType'],
                        $masterRecord['rowDescription'] ?: 'An instance of tt_content:' . $masterRecord['uid'],
                        'Inserts an instance of tt_content:' . $masterRecord['uid'] . ' of type ' . $masterRecord['CType'],
                        $masterRecord['CType'],
                        $masterRecord['uid'],
                        $sanitizedGroupName,
                        $contentId
                    )
                );
            }
        }
    }
})($_EXTCONF);
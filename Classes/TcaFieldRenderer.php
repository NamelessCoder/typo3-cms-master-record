<?php
declare(strict_types=1);

namespace NamelessCoder\MasterRecord;

use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\Restriction\DeletedRestriction;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Backend\Routing\UriBuilder;

class TcaFieldRenderer
{
    public function renderListOfInstances(array $parameters)
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable($parameters['table']);
        $queryBuilder->getRestrictions()->removeAll()->add(GeneralUtility::makeInstance(DeletedRestriction::class));
        $instanceRecords = $queryBuilder->select('*')
            ->from($parameters['table'])
            ->where($queryBuilder->expr()->eq('tx_masterrecord_instanceof', $queryBuilder->createNamedParameter($parameters['row']['uid'], \PDO::PARAM_INT)))
            ->execute()
            ->fetchAll();
        $content = '<h5>Instances</h5>';
        if (empty($instanceRecords)) {
            $content .= '<i>No instances exist of this master</i>';
            return $content;
        }
        $content .= '<ul>';
        foreach ($instanceRecords as $instanceRecord) {
            $parameters = [
                'edit' => [$parameters['table'] => [$instanceRecord['uid'] => 'edit']],
                'returnUrl' => $_SERVER['REQUEST_URI'],
            ];
            $uriBuilder = GeneralUtility::makeInstance(UriBuilder::class);
            $uri = (string)$uriBuilder->buildUriFromRoute('record_edit', $parameters);
            $content .= sprintf(
                '<li><a href="%s">%s</a></li>',
                $uri,
                BackendUtility::getRecordTitle('tt_content', $instanceRecord)
                    . ' (uid ' . $instanceRecord['uid'] . ') on page '
                    . BackendUtility::getRecordPath($instanceRecord['pid'], '1=1', 0, 0)
                    . ' (pid ' . $instanceRecord['pid'] . ')'

            );
        }
        $content .= '</ul>';
        return $content;
    }

    public function renderMasterInstanceSelector(array $parameters)
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable($parameters['table']);
        $queryBuilder->getRestrictions()->removeAll()->add(GeneralUtility::makeInstance(DeletedRestriction::class));
        $masterRecords = $queryBuilder->select('*')
            ->from($parameters['table'])
            ->where($queryBuilder->expr()->eq('tx_masterrecord_master', $queryBuilder->createNamedParameter(1, \PDO::PARAM_INT)))
            ->andWhere($queryBuilder->expr()->eq('CType', $queryBuilder->createNamedParameter($parameters['row']['CType'][0] ?? $parameters['row']['CType'], \PDO::PARAM_STR)))
            ->orderBy('sorting')
            ->groupBy('pid')
            ->execute()
            ->fetchAll();

        $optionGroups = [];
        foreach ($masterRecords as $masterRecord) {
            if (!isset($optionGroups[$masterRecord['pid']])) {
                $optionGroups[$masterRecord['pid']] = [
                    'title' => BackendUtility::getRecordPath($masterRecord['pid'], '1=1', 0, 0),
                    'items' => [],
                ];
            }
            $optionGroups[$masterRecord['pid']]['items'][] = $masterRecord;
        }

        $field = '<select class="form-control form-control-adapt" name="' . $parameters['itemFormElName'] . '" id="' . $parameters['itemFormElID'] . '">';
        foreach ($optionGroups as $optionGroup) {
            $field .= '<optgroup label="' . $optionGroup['title'] . '">';
            foreach ($optionGroup['items'] as $option) {
                $field .= sprintf(
                    '<option value="' . $option['uid'] . '"%s>%s</option>',
                    (int)$option['uid'] === (int)$parameters['itemFormElValue'] ? ' selected="selected"' : '',
                    BackendUtility::getRecordTitle('tt_content', $masterRecord)
                );
            }
            $field .= '</optgroup>';
        }
        $field .= '</select>';

        return $field;
    }
}

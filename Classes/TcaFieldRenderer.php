<?php
declare(strict_types=1);

namespace NamelessCoder\MasterRecord;

use Doctrine\DBAL\Exception\InvalidFieldNameException;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\Restriction\DeletedRestriction;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Backend\Routing\UriBuilder;

class TcaFieldRenderer
{
    public function renderListOfInstances(array $parameters)
    {
        $content = '<h5>Instances</h5>';
        $generator = GeneralUtility::makeInstance(RecordInsight::class, $parameters['table'], $parameters['row'])->getInstances();
        if (!$generator->valid()) {
            $content .= '<i>No instances exist of this master</i>';
            return $content;
        }

        $content .= '<ul>';
        foreach ($generator as $instanceRecordInsight) {
            $instanceUid = $instanceRecordInsight->readInstanceValue('uid');
            $instancePid = $instanceRecordInsight->readInstanceValue('pid');
            $parameters = [
                'edit' => [$parameters['table'] => [$instanceUid => 'edit']],
                'returnUrl' => $_SERVER['REQUEST_URI'],
            ];
            $uriBuilder = GeneralUtility::makeInstance(UriBuilder::class);
            $uri = (string)$uriBuilder->buildUriFromRoute('record_edit', $parameters);
            $content .= sprintf(
                '<li><a href="%s">%s</a></li>',
                $uri,
                ($instanceRecordInsight->readInstanceValue('header') ?: '[No title]')
                    . ' (uid ' . $instanceUid . ') on page '
                    . BackendUtility::getRecordPath($instancePid, '1=1', 0, 0)
                    . ' (pid ' . $instancePid . ')'

            );
        }
        $content .= '</ul>';

        return $content;
    }

    public function renderMasterInstanceSelector(array $parameters)
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable($parameters['table']);
        $queryBuilder->getRestrictions()->removeAll()->add(GeneralUtility::makeInstance(DeletedRestriction::class));
        try {
            $masterRecords = $queryBuilder->select('*')
                ->from($parameters['table'])
                ->where($queryBuilder->expr()->eq('tx_masterrecord_master', $queryBuilder->createNamedParameter(1, \PDO::PARAM_INT)))
                ->andWhere($queryBuilder->expr()->eq('CType', $queryBuilder->createNamedParameter($parameters['row']['CType'][0] ?? $parameters['row']['CType'], \PDO::PARAM_STR)))
                ->orderBy('sorting')
                ->groupBy('pid')
                ->execute()
                ->fetchAll();
        } catch (InvalidFieldNameException $exception) {
            return $exception->getMessage() . ' (' . $exception->getCode() . ')';
        }

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
        $field .= '<option value="0"></option>';
        foreach ($optionGroups as $optionGroup) {
            $field .= '<optgroup label="' . $optionGroup['title'] . '">';
            foreach ($optionGroup['items'] as $option) {
                $field .= sprintf(
                    '<option value="' . $option['uid'] . '"%s>%s</option>',
                    (int)$option['uid'] === (int)$parameters['itemFormElValue'] ? ' selected="selected"' : '',
                    BackendUtility::getRecordTitle('tt_content', $option)
                );
            }
            $field .= '</optgroup>';
        }
        $field .= '</select>';

        return $field;
    }
}

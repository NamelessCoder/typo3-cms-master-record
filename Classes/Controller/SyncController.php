<?php
declare(strict_types=1);

namespace NamelessCoder\MasterRecord\Controller;

use NamelessCoder\MasterRecord\RecordInsight;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class SyncController
{
    public function sync(ServerRequest $request)
    {
        $parameters = $request->getParsedBody();
        $table = $parameters['table'];
        $uid = (int)$parameters['uid'];

        $connection = GeneralUtility::makeInstance(ConnectionPool::class);

        $queryBuilder = $connection->getQueryBuilderForTable($table);
        $queryBuilder->getRestrictions()->removeAll();
        $record = $queryBuilder->select('*')
            ->from($table)
            ->where($queryBuilder->expr()->eq('uid', $queryBuilder->createNamedParameter($uid, \PDO::PARAM_INT)))
            ->setMaxResults(1)
            ->execute()
            ->fetch();

        $insight = GeneralUtility::makeInstance(RecordInsight::class, $table, $record);

        $queryBuilder = $connection->getQueryBuilderForTable($table);
        $query = $queryBuilder->update($table)
            ->where($queryBuilder->expr()->eq('uid', $queryBuilder->createNamedParameter($uid, \PDO::PARAM_INT)))
            ->setMaxResults(1);

        foreach ((array) $parameters['fields'] as $fieldName) {
            $value = $insight->readDifferenceForField($fieldName)->getMergedValue();
            $query->set($fieldName, $queryBuilder->expr()->literal($value), false);
        }

        $result = $query->execute();
        header('Content-Type: text/plain');
        echo (int)$result;
        exit();
    }
}

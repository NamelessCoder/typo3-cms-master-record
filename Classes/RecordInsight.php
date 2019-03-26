<?php
declare(strict_types=1);

namespace NamelessCoder\MasterRecord;

use Doctrine\DBAL\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\Restriction\DeletedRestriction;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Page\PageRepository;

class RecordInsight
{
    protected $table;

    protected $record = [];

    protected $master = [];

    protected $ignoredColumns = [];

    public function __construct(string $table, array $record, array $master = [])
    {
        $this->table = $table;
        $this->record = $record;
        $this->master = $master;
    }

    public function isItselfMaster(): bool
    {
        foreach ($this->getInstances() as $_) {
            return true;
        }
        return false;
    }

    public function hasMaster(): bool
    {
        try {
            $this->fetchMasterRecordWithoutRestriction();
            return true;
        } catch (MasterRecordException $exception) {
            return false;
        }
    }

    /**
     * @return \Generator|FieldDifference[]
     */
    public function getDifferences(): \Generator
    {
        $ignoredColumns = $this->fetchColumnsToIgnore();
        try {
            foreach ($this->fetchMasterRecordWithoutRestriction() as $fieldName => $_) {
                if (in_array($fieldName, $ignoredColumns)) {
                    continue;
                }
                $diff = $this->readDifferenceForField($fieldName);
                if ($diff->isInstanceChanged()) {
                    yield $fieldName => $this->readDifferenceForField($fieldName);
                }
            }
        } catch (MasterRecordException $exception) {
        }
    }

    /**
     * @return \Generator|static[]
     */
    public function getInstances(): \Generator
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable($this->table);
        $queryBuilder->getRestrictions()->removeAll()->add(GeneralUtility::makeInstance(DeletedRestriction::class));
        $result = $queryBuilder->select('*')
            ->from($this->table)
            ->where($queryBuilder->expr()->eq('tx_masterrecord_instanceof', $queryBuilder->createNamedParameter($this->record['uid'], \PDO::PARAM_INT)))
            ->andWhere($queryBuilder->expr()->in('sys_language_uid', $queryBuilder->createNamedParameter([-1, 0], Connection::PARAM_INT_ARRAY)))
            ->execute();
        while (($instanceRecord = $result->fetch())) {
            yield new static($this->table, $instanceRecord, $this->record);
        }
    }

    public function getMasterRecordInsight(): RecordInsight
    {
        if (!$this->hasMaster()) {
            throw new MasterRecordException('Attempt to read differences from a record without master', 1551139600);
        }
        $masterRecord = $this->fetchMasterRecordWithoutRestriction();
        return new RecordInsight($this->table, $masterRecord);
    }

    public function readMasterValue(string $fieldName)
    {
        $this->fetchMasterRecordWithoutRestriction();
        return $this->master[$fieldName] ?? null;
    }

    public function readInstanceValue(string $fieldName)
    {
        return $this->record[$fieldName] ?? null;
    }

    public function readDifferenceForField(string $fieldName): FieldDifference
    {
        return new FieldDifference($this, $fieldName, $GLOBALS['TCA'][$this->table]['columns'][$fieldName]['config']['type'] === 'flex');
    }

    protected function fetchColumnsToIgnore(): array
    {
        if (empty($this->ignoredColumns)) {
            $this->ignoredColumns = array_filter(
                array_merge(
                    [$GLOBALS['TCA'][$this->table]['ctrl']['delete'], $GLOBALS['TCA'][$this->table]['ctrl']['type']],
                    [$GLOBALS['TCA'][$this->table]['ctrl']['descriptionColumn'], $GLOBALS['TCA'][$this->table]['ctrl']['editlock']],
                    (array)$GLOBALS['TCA'][$this->table]['ctrl']['enableColumns'],
                    ['pid', 'l18n_diffsource', 'tx_masterrecord_instanceof', 'tx_masterrecord_instances', 'tx_masterrecord_master', '_LOCALIZED_UID'],
                    [$GLOBALS['TCA'][$this->table]['ctrl']['languageField'], $GLOBALS['TCA'][$this->table]['ctrl']['tstamp'], $GLOBALS['TCA'][$this->table]['ctrl']['crdate']],
                    [$GLOBALS['TCA'][$this->table]['ctrl']['cruser_id'], $GLOBALS['TCA'][$this->table]['ctrl']['sortby'], 'uid', 't3_origuid', 'tx_masterrecord_sync'],
                    [$GLOBALS['TCA'][$this->table]['ctrl']['translationSource'], $GLOBALS['TCA'][$this->table]['ctrl']['transOrigDiffSourceField'], $GLOBALS['TCA'][$this->table]['ctrl']['transOrigPointerField']]
                )
            );
        }
        return $this->ignoredColumns;
    }

    protected function fetchMasterRecordWithoutRestriction(): array
    {
        if (empty($this->master)) {
            $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable($this->table);
            $queryBuilder->getRestrictions()->removeAll();
            $this->master = $queryBuilder->select('*')
                ->from($this->table)
                ->where($queryBuilder->expr()->eq('uid', $queryBuilder->createNamedParameter($this->record['tx_masterrecord_instanceof'], \PDO::PARAM_INT)))
                ->setMaxResults(1)
                ->execute()
                ->fetch();
            if (empty($this->master)) {
                throw new MasterRecordException(
                    sprintf(
                        'Master for record %s:%d not found',
                        $this->table,
                        $this->record['uid']
                    ),
                    1551139601
                );
            }
            $this->master = GeneralUtility::makeInstance(PageRepository::class)->getLanguageOverlay($this->table, $this->master) ?? $this->master;
        }
        return $this->master;
    }
}

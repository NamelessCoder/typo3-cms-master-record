<?php
declare(strict_types=1);

namespace NamelessCoder\MasterRecord;

use TYPO3\CMS\Core\Utility\CommandUtility;

class FieldDifference
{
    protected $insight;

    protected $fieldName = '';

    protected $masterValue = null;

    protected $instanceValue = null;

    protected $schema;

    public function __construct(RecordInsight $insight, string $fieldName)
    {
        $this->insight = $insight;
        $this->fieldName = $fieldName;
        $this->masterValue = $insight->readMasterValue($fieldName);
        $this->instanceValue = $insight->readInstanceValue($fieldName);
        $this->schema = new SchemaInsight($insight, $fieldName);
    }

    public function isInstanceChanged(): bool
    {
        return $this->instanceValue !== $this->masterValue;
    }

    public function isMasterChanged(): bool
    {
        return true;
    }

    public function getDifferenceValue()
    {
        return $this->diffValues($this->masterValue, $this->instanceValue);
    }

    public function getMergedValue()
    {
        return $this->masterValue;
    }

    public function getResetValue()
    {
        return $this->masterValue;
    }

    protected function diffValues($value1, $value2)
    {
        static $diffCommand;
        if (!$diffCommand) {
            $diffCommand = CommandUtility::getCommand('diff');
        }
        $command = $diffCommand . ' <(echo "' . addslashes((string)$value1) . '") <(echo "' . addslashes((string)$value2) . '") 2&>1';
        $output = [];
        $code = 0;
        exec($command, $output, $code);
        var_dump($command);
        var_dump($output);
        var_dump($code);
        #var_dump($command);
        #var_dump(shell_exec($command));
        #var_dump(shell_exec('which diff'));
        return implode(PHP_EOL, $output);
    }

    protected function mergeValues($value1, $value2)
    {
        static $diffCommand;
        if (!$diffCommand) {
            $diffCommand = CommandUtility::getCommand('diff');
        }
        $command = 'ed ' . $diffCommand . ' <(echo ' . escapeshellarg((string)$value1) . ' ) <(echo ' . escapeshellarg((string)$value2) . ')';
        #var_dump(shel)
    }
}

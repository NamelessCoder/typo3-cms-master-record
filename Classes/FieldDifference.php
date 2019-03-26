<?php
declare(strict_types=1);

namespace NamelessCoder\MasterRecord;

use TYPO3\CMS\Core\Configuration\FlexForm\FlexFormTools;
use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class FieldDifference
{
    protected $insight;

    protected $fieldName = '';

    protected $masterValue = null;

    protected $instanceValue = null;

    protected $isFlexFormField = false;

    public function __construct(RecordInsight $insight, string $fieldName, bool $isFlexFormField)
    {
        $this->insight = $insight;
        $this->fieldName = $fieldName;
        $this->isFlexFormField = $isFlexFormField;
        $this->masterValue = $insight->readMasterValue($fieldName);
        $this->instanceValue = $insight->readInstanceValue($fieldName);
    }

    public function isInstanceChanged(): bool
    {
        return $this->instanceValue !== $this->masterValue;
    }

    public function getMergedValue()
    {
        if ($this->isFlexFormField) {
            if (empty($this->masterValue)) {
                return $this->instanceValue;
            }
            $data = GeneralUtility::xml2array($this->masterValue);
            if (!is_array($data)) {
                return $this->instanceValue;
            }
            $instanceData = GeneralUtility::xml2array($this->instanceValue);
            if (!is_array($instanceData)) {
                return $this->masterValue;
            }
            ArrayUtility::mergeRecursiveWithOverrule($data, $instanceData, true, false);
            return (new FlexFormTools())->flexArray2Xml($data);
        }
        return empty($this->instanceValue) ? $this->masterValue : $this->instanceValue;
    }
}

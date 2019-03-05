<?php
declare(strict_types=1);

namespace NamelessCoder\MasterRecord;

class SchemaInsight
{
    protected $insight;

    protected $fieldName = '';

    public function __construct(RecordInsight $insight, string $fieldName)
    {
        $this->insight = $insight;
        $this->fieldName = $fieldName;
    }

    public function isPlainValue(): bool
    {
        return false;
    }

    public function isVisible(): bool
    {
        return false;
    }

    public function isModified(): bool
    {
        return false;
    }
}

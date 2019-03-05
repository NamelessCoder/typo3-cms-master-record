<?php
declare(strict_types=1);

namespace NamelessCoder\MasterRecord;

class RecordStatus
{
    protected $insight;

    public function __construct(RecordInsight $insight)
    {
        $this->insight = $insight;
    }
}

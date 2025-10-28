<?php

namespace App\Services\Reports;

use App\DTO\Reports\ReportOptionsDTO;
use App\Services\Reports\Contracts\ReportInterface;
use App\Services\Reports\Contracts\ReportDataInterface;

abstract class AbstractReport implements ReportInterface
{
    protected ReportOptionsDTO $options;

    public function __construct(ReportOptionsDTO $options)
    {
        $this->options = $options;
    }

    /**
     * Generate the raw report data.
     *
     * @return ReportDataInterface
     */
    abstract protected function generateData(): ReportDataInterface;
}

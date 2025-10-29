<?php

namespace App\DTO\Reports;

class ChartDataDTO
{
    public function __construct(
        public readonly string $type,        // 'line', 'bar', 'horizontal-bar'
        public readonly array $labels,       // X-axis: dates or categories
        public readonly array $datasets,     // Y-axis: data series
        public readonly array $options = [], // Chart.js options
    ) {}

    public function toArray(): array
    {
        return [
            'type' => $this->type,
            'labels' => $this->labels,
            'datasets' => $this->datasets,
            'options' => $this->options,
        ];
    }
}

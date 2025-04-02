<?php

namespace App\Charts;

use ArielMejiaDev\LarapexCharts\LarapexChart;

class LoanChart
{
    protected $chart;

    public function __construct()
    {
        $this->chart = new LarapexChart();
    }

    public function labels(array $labels)
    {
        $this->chart->setLabels($labels);
        return $this;
    }

    public function dataset($name, $type, array $data)
    {
        $this->chart->addDataSet($name)->data($data)->setType($type);
        return $this;
    }

    public function backgroundColor(array $colors)
    {
        $this->chart->setColors($colors);
        return $this;
    }

    public function render()
    {
        return $this->chart;
    }
}

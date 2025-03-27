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

    public function build()
    {
        return $this->chart->barChart()
            ->setTitle('Loans Statistics')
            ->setXAxis(['Pending', 'Approved', 'Rejected', 'Paid']);
    }
}

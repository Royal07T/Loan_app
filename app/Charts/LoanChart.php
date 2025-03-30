<?php

namespace App\Charts;

use ArielMejiaDev\LarapexCharts\LarapexChart;
use App\Models\Loan;

class LoanChart
{
    protected $chart;

    public function __construct()
    {
        $this->chart = new LarapexChart();
    }

    public function build()
    {
        // Get the count of loans based on their statuses
        $pending = Loan::where('status', 'pending')->count();
        $approved = Loan::where('status', 'approved')->count();
        $rejected = Loan::where('status', 'rejected')->count();
        $paid = Loan::where('status', 'paid')->count();

        return $this->chart->barChart()
            ->setTitle('Loan Statistics')
            ->setXAxis(['Pending', 'Approved', 'Rejected', 'Paid'])
            ->setDataset([
                [
                    'name' => 'Loan Applications',
                    'data' => [$pending, $approved, $rejected, $paid]
                ]
            ]);
    }
}

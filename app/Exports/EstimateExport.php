<?php

namespace App\Exports;

use App\Models\Estimate;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class EstimateExport implements FromView
{
    /** @var Estimate */
    protected $estimate;

    public function __construct(Estimate $estimate)
    {
        $this->estimate = $estimate;
    }

    public function view(): View
    {
        // Получаем все данные точно так же, как в PDF
        $calculated = $this->estimate->getEstimate();
        $project    = $this->estimate->project;
        $estimate   = $this->estimate;

        // Возвращаем Blade-шаблон, который рендерит HTML-таблицу
        return view('projects.estimate_excel', compact('project', 'estimate', 'calculated'));
    }
}

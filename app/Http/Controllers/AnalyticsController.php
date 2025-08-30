<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AnalyticsController extends Controller
{
    /**
     * Страница отчета по проектам
     */
    public function projects()
    {
        return view('analytics.projects');
    }

    /**
     * Страница отчета по заработной плате
     */
    public function salary()
    {
        return view('analytics.salary');
    }

    /**
     * Страница контроля оборудования
     */
    public function equipment()
    {
        return view('analytics.equipment');
    }

    /**
     * Страница отчета по продажам
     */
    public function sales()
    {
        return view('analytics.sales');
    }

    /**
     * Страница финансового отчета
     */
    public function financial()
    {
        return view('analytics.financial');
    }
}

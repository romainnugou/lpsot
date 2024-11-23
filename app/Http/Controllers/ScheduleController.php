<?php

namespace App\Http\Controllers;

use App\Services\ScheduleService;

class ScheduleController extends Controller
{
    protected $scheduleService;

    public function __construct(ScheduleService $scheduleService)
    {
        $this->scheduleService = $scheduleService;
    }

    public function index()
    {
        $timeline = $this->scheduleService->generateTimeline();

        $success = $timeline->isSuccess();
        $hasEvents = $timeline->hasEvents();
        $timelineArr = $timeline->getTimelineArray();
        $delayedOrders = $timeline->getDelayedOrders();

        return view('schedule.index')
            ->with(compact('success', 'hasEvents', 'timelineArr', 'delayedOrders'));
    }
}

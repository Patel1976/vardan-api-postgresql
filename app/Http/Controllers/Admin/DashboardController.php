<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Models\StaffUser;
use App\Models\StaffTimelog;
use App\Models\Staff_emergency_logs;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController
{
    public function stats()
    {
        $totalStaff = StaffUser::count();
        $activeStaff = StaffUser::where('status', true)->count();
        $todayLogs = StaffTimelog::whereDate('created_at', Carbon::today())->count();
        $emergencyAlerts = Staff_emergency_logs::whereDate('created_at', Carbon::today())->count();
        $stats = [
            [
                'title' => 'Total Staff',
                'value' => $totalStaff,
                'icon' => 'faUsers',
                'color' => '#bd9a68',
                'background' => 'rgba(189,154,104,0.2)',
                'border' => '1px solid rgba(189,154,104,0.3)',
            ],
            [
                'title' => 'Active Staff',
                'value' => $activeStaff,
                'icon' => 'faUserTie',
                'color' => '#4caf50',
                'background' => 'rgba(76,175,80,0.2)',
                'border' => '1px solid rgba(76,175,80,0.3)',
            ],
            [
                'title' => 'Time Logs Today',
                'value' => $todayLogs,
                'icon' => 'faClipboardList',
                'color' => '#2196f3',
                'background' => 'rgba(33,150,243,0.2)',
                'border' => '1px solid rgba(33,150,243,0.3)',
            ],
            [
                'title' => 'Emergency Alerts',
                'value' => $emergencyAlerts,
                'icon' => 'faExclamationTriangle',
                'color' => '#f44336',
                'background' => 'rgba(244,67,54,0.2)',
                'border' => '1px solid rgba(244,67,54,0.3)',
            ],
        ];
        return response()->json($stats);
    }

    public function recentActivities()
    {
        $activities = DB::table('staff_timelogs')
            ->join('staff_users', 'staff_timelogs.user_id', '=', 'staff_users.id')
            ->select(
                'staff_users.name as staffName',
                'staff_timelogs.type as activity',
                'staff_timelogs.logs as time'
            )
            ->orderBy('staff_timelogs.logs', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($log) {
                return [
                    'staffName' => $log->staffName,
                    'activity' => ucfirst($log->activity),
                    'time' => Carbon::parse($log->time)->format('F j, Y, h:i A'),
                ];
            });
        return response()->json($activities);
    }

    public function emergencyNotifications()
    {
        $logs = DB::table('staff_emergency_logs')
            ->join('staff_users', 'staff_emergency_logs.user_id', '=', 'staff_users.id')
            ->select('staff_users.name', 'staff_emergency_logs.description', 'staff_emergency_logs.created_at')
            ->orderBy('staff_emergency_logs.created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($log) {
                return [
                    'staffName' => $log->name ?? 'Unknown',
                    'description' => $log->description,
                    'time' => Carbon::parse($log->created_at)->diffForHumans(),
                ];
            });
        return response()->json($logs);
    }
}

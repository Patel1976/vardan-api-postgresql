<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;

class DashboardController
{
    public function stats()
    {
        $stats = [
            [
                'title' => 'Total Staff',
                'value' => 125,
                'icon' => 'faUsers',
                'color' => '#bd9a68',
                'background' => 'rgba(189,154,104,0.2)',
                'border' => '1px solid rgba(189,154,104,0.3)',
            ],
            [
                'title' => 'Active Staff',
                'value' => 87,
                'icon' => 'faUserTie',
                'color' => '#4caf50',
                'background' => 'rgba(76,175,80,0.2)',
                'border' => '1px solid rgba(76,175,80,0.3)',
            ],
            [
                'title' => 'Time Logs Today',
                'value' => 254,
                'icon' => 'faClipboardList',
                'color' => '#2196f3',
                'background' => 'rgba(33,150,243,0.2)',
                'border' => '1px solid rgba(33,150,243,0.3)',
            ],
            [
                'title' => 'Emergency Alerts',
                'value' => 3,
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
        $activities = [
            ['staffName' => 'John Doe', 'activity' => 'Checked in', 'time' => 'Today, 09:15 AM'],
            ['staffName' => 'Jane Smith', 'activity' => 'Checked out', 'time' => 'Today, 05:30 PM'],
            ['staffName' => 'Michael Johnson', 'activity' => 'Reported emergency', 'time' => 'Today, 02:45 PM'],
            ['staffName' => 'Linda Williams', 'activity' => 'Checked in', 'time' => 'Today, 08:50 AM'],
        ];

        return response()->json($activities);
    }

    public function emergencyNotifications()
    {
        $notifications = [
            [
                'staffName' => 'Michael Johnson',
                'message' => 'reported an emergency at',
                'location' => 'Main St. Location',
                'time' => '02:45 PM',
                'type' => 'danger',
            ],
            [
                'staffName' => 'Susan Miller',
                'message' => 'requested assistance at',
                'location' => 'Downtown Office',
                'time' => '11:20 AM',
                'type' => 'warning',
            ],
            [
                'staffName' => 'Robert Brown',
                'message' => 'reported an emergency at',
                'location' => 'Warehouse B',
                'time' => 'Yesterday, 4:30 PM',
                'type' => 'danger',
            ],
        ];

        return response()->json($notifications);
    }
}

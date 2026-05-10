<?php

namespace App\Http\Controllers;

use App\Models\Room;
use App\Models\Guest;
use App\Models\Reservation;
use App\Models\Payment;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $from = $request->from;
        $to = $request->to;

        $reservationQuery = Reservation::with(['guest', 'room', 'payment']);
        $paymentQuery = Payment::with(['reservation.guest', 'reservation.room']);

        if ($from && $to) {
            $reservationQuery->whereBetween('created_at', [
                $from . ' 00:00:00',
                $to . ' 23:59:59'
            ]);

            $paymentQuery->whereBetween('created_at', [
                $from . ' 00:00:00',
                $to . ' 23:59:59'
            ]);
        }

        $totalRooms = Room::count();
        $availableRooms = Room::where('status', 'available')->count();
        $occupiedRooms = Room::where('status', 'occupied')->count();
        $reservedRooms = Room::where('status', 'reserved')->count();

        $totalGuests = Guest::count();
        $totalReservations = Reservation::count();

        $totalIncome = (clone $paymentQuery)->sum('amount_paid');
        $totalBalance = (clone $paymentQuery)->sum('balance');

        $recentReservations = $reservationQuery->latest()->get();
        $recentPayments = $paymentQuery->latest()->get();

        return view('reports.index', compact(
            'from',
            'to',
            'totalRooms',
            'availableRooms',
            'occupiedRooms',
            'reservedRooms',
            'totalGuests',
            'totalReservations',
            'totalIncome',
            'totalBalance',
            'recentReservations',
            'recentPayments'
        ));
    }
}
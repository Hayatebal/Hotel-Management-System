<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Models\Guest;
use App\Models\Room;
use Illuminate\Http\Request;

class ReservationController extends Controller
{
    public function index()
    {
        $reservations = Reservation::with(['guest', 'room'])->latest()->get();

        return view('reservations.index', compact('reservations'));
    }

    public function create()
    {
        $guests = Guest::orderBy('first_name')->get();

        $rooms = Room::where('status', 'available')
            ->orderBy('room_number')
            ->get();

        return view('reservations.create', compact('guests', 'rooms'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'guest_id' => 'required|exists:guests,id',
            'room_id' => 'required|exists:rooms,id',
            'check_in' => 'required|date',
            'duration_type' => 'required|string',
            'extended_hours' => 'nullable|integer|min:0',
            'status' => 'required|string',
        ]);

        $room = Room::findOrFail($request->room_id);

        $totalAmount = match ($request->duration_type) {
            '3hrs' => $room->price_3hrs,
            '6hrs' => $room->price_6hrs,
            '8hrs' => $room->price_8hrs,
            '12hrs' => $room->price_12hrs,
            '24hrs' => $room->price_24hrs,
            default => 0,
        };

        $hours = match ($request->duration_type) {
            '3hrs' => 3,
            '6hrs' => 6,
            '8hrs' => 8,
            '12hrs' => 12,
            '24hrs' => 24,
            default => 0,
        };

        $extendedHours = $request->extended_hours ?? 0;
        $extendedFee = $extendedHours * $room->overtime_fee_per_hour;
        $finalAmount = $totalAmount + $extendedFee;

        $checkOut = date(
            'Y-m-d H:i:s',
            strtotime($request->check_in . ' +' . ($hours + $extendedHours) . ' hours')
        );

        Reservation::create([
            'guest_id' => $request->guest_id,
            'room_id' => $request->room_id,
            'check_in' => $request->check_in,
            'check_out' => $checkOut,
            'duration_type' => $request->duration_type,
            'total_amount' => $totalAmount,
            'extended_hours' => $extendedHours,
            'extended_fee' => $extendedFee,
            'final_amount' => $finalAmount,
            'status' => $request->status,
        ]);

        if ($request->status === 'checked_in') {
            $room->update(['status' => 'occupied']);
        } else {
            $room->update(['status' => 'reserved']);
        }

        return redirect()->route('reservations.index')
            ->with('success', 'Reservation Created Successfully');
    }

    public function edit(Reservation $reservation)
    {
        $guests = Guest::orderBy('first_name')->get();

        $rooms = Room::where('status', 'available')
            ->orWhere('id', $reservation->room_id)
            ->orderBy('room_number')
            ->get();

        return view('reservations.edit', compact('reservation', 'guests', 'rooms'));
    }

    public function update(Request $request, Reservation $reservation)
    {
        $request->validate([
            'guest_id' => 'required|exists:guests,id',
            'room_id' => 'required|exists:rooms,id',
            'check_in' => 'required|date',
            'duration_type' => 'required|string',
            'extended_hours' => 'nullable|integer|min:0',
            'status' => 'required|string',
        ]);

        $oldRoomId = $reservation->room_id;
        $room = Room::findOrFail($request->room_id);

        $totalAmount = match ($request->duration_type) {
            '3hrs' => $room->price_3hrs,
            '6hrs' => $room->price_6hrs,
            '8hrs' => $room->price_8hrs,
            '12hrs' => $room->price_12hrs,
            '24hrs' => $room->price_24hrs,
            default => 0,
        };

        $hours = match ($request->duration_type) {
            '3hrs' => 3,
            '6hrs' => 6,
            '8hrs' => 8,
            '12hrs' => 12,
            '24hrs' => 24,
            default => 0,
        };

        $extendedHours = $request->extended_hours ?? 0;
        $extendedFee = $extendedHours * $room->overtime_fee_per_hour;
        $finalAmount = $totalAmount + $extendedFee;

        $checkOut = date(
            'Y-m-d H:i:s',
            strtotime($request->check_in . ' +' . ($hours + $extendedHours) . ' hours')
        );

        $reservation->update([
            'guest_id' => $request->guest_id,
            'room_id' => $request->room_id,
            'check_in' => $request->check_in,
            'check_out' => $checkOut,
            'duration_type' => $request->duration_type,
            'total_amount' => $totalAmount,
            'extended_hours' => $extendedHours,
            'extended_fee' => $extendedFee,
            'final_amount' => $finalAmount,
            'status' => $request->status,
        ]);

        if ($oldRoomId != $request->room_id) {
            Room::find($oldRoomId)?->update(['status' => 'available']);
        }

        if ($request->status === 'checked_in') {
            $room->update(['status' => 'occupied']);
        } elseif ($request->status === 'checked_out' || $request->status === 'cancelled') {
            $room->update(['status' => 'available']);
        } else {
            $room->update(['status' => 'reserved']);
        }

        return redirect()->route('reservations.index')
            ->with('success', 'Reservation Updated Successfully');
    }

    public function destroy(Reservation $reservation)
    {
        $reservation->room?->update(['status' => 'available']);

        $reservation->delete();

        return redirect()->route('reservations.index')
            ->with('success', 'Reservation Deleted Successfully');
    }
}
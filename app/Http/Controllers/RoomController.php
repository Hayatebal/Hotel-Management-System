<?php

namespace App\Http\Controllers;

use App\Models\Room;
use Illuminate\Http\Request;

class RoomController extends Controller
{
    public function index()
    {
        $rooms = Room::latest()->get();

        return view('rooms.index', compact('rooms'));
    }

    public function create()
    {
        return view('rooms.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'room_number' => 'required|unique:rooms,room_number',
            'room_type' => 'required',
            'price_3hrs' => 'required|numeric',
            'price_6hrs' => 'required|numeric',
            'price_8hrs' => 'required|numeric',
            'price_12hrs' => 'required|numeric',
            'price_24hrs' => 'required|numeric',
            'status' => 'required',
        ]);

        Room::create($request->only([
            'room_number',
            'room_type',
            'price_3hrs',
            'price_6hrs',
            'price_8hrs',
            'price_12hrs',
            'price_24hrs',
            'status',
        ]));

        return redirect()->route('rooms.index')
            ->with('success', 'Room Added Successfully');
    }

    public function edit(Room $room)
    {
        return view('rooms.edit', compact('room'));
    }

    public function update(Request $request, Room $room)
    {
        $request->validate([
            'room_number' => 'required|unique:rooms,room_number,' . $room->id,
            'room_type' => 'required',
            'price_3hrs' => 'required|numeric',
            'price_6hrs' => 'required|numeric',
            'price_8hrs' => 'required|numeric',
            'price_12hrs' => 'required|numeric',
            'price_24hrs' => 'required|numeric',
            'status' => 'required',
        ]);

        $room->update($request->only([
            'room_number',
            'room_type',
            'price_3hrs',
            'price_6hrs',
            'price_8hrs',
            'price_12hrs',
            'price_24hrs',
            'status',
        ]));

        return redirect()->route('rooms.index')
            ->with('success', 'Room Updated Successfully');
    }

    public function destroy(Room $room)
    {
        $room->delete();

        return redirect()->route('rooms.index')
            ->with('success', 'Room Deleted Successfully');
    }
}
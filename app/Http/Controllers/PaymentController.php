<?php

namespace App\Http\Controllers;

use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Payment;
use App\Models\Reservation;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function index()
    {
        $payments = Payment::with(['reservation.guest', 'reservation.room'])
            ->latest()
            ->get();

        return view('payments.index', compact('payments'));
    }

    public function create()
    {
        $reservations = Reservation::with(['guest', 'room'])
            ->whereIn('status', ['checked_in', 'checked_out'])
            ->get();

        return view('payments.create', compact('reservations'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'reservation_id' => 'required|exists:reservations,id',
            'amount_paid' => 'required|numeric|min:0',
            'payment_method' => 'required|string',
            'reference_number' => 'nullable|string|max:255',
        ]);

        $reservation = Reservation::findOrFail($request->reservation_id);

        $balance = $reservation->final_amount - $request->amount_paid;

        if ($balance <= 0) {
            $paymentStatus = 'paid';
            $balance = 0;
        } elseif ($request->amount_paid > 0) {
            $paymentStatus = 'partial';
        } else {
            $paymentStatus = 'unpaid';
        }

        Payment::create([
            'reservation_id' => $request->reservation_id,
            'amount_paid' => $request->amount_paid,
            'balance' => $balance,
            'payment_method' => $request->payment_method,
            'payment_status' => $paymentStatus,
            'reference_number' => $request->reference_number,
        ]);

        return redirect()->route('payments.index')
            ->with('success', 'Payment Added Successfully');
    }

    public function edit(Payment $payment)
    {
        $payment->load(['reservation.guest', 'reservation.room']);

        $reservations = Reservation::with(['guest', 'room'])->get();

        return view('payments.edit', compact('payment', 'reservations'));
    }

    public function update(Request $request, Payment $payment)
    {
        $request->validate([
            'reservation_id' => 'required|exists:reservations,id',
            'amount_paid' => 'required|numeric|min:0',
            'payment_method' => 'required|string',
            'reference_number' => 'nullable|string|max:255',
        ]);

        $reservation = Reservation::findOrFail($request->reservation_id);

        $balance = $reservation->final_amount - $request->amount_paid;

        if ($balance <= 0) {
            $paymentStatus = 'paid';
            $balance = 0;
        } elseif ($request->amount_paid > 0) {
            $paymentStatus = 'partial';
        } else {
            $paymentStatus = 'unpaid';
        }

        $payment->update([
            'reservation_id' => $request->reservation_id,
            'amount_paid' => $request->amount_paid,
            'balance' => $balance,
            'payment_method' => $request->payment_method,
            'payment_status' => $paymentStatus,
            'reference_number' => $request->reference_number,
        ]);

        return redirect()->route('payments.index')
            ->with('success', 'Payment Updated Successfully');
    }

    public function destroy(Payment $payment)
    {
        $payment->delete();

        return redirect()->route('payments.index')
            ->with('success', 'Payment Deleted Successfully');
    }

    public function receipt(Payment $payment)
    {
        $payment->load(['reservation.guest', 'reservation.room']);

        return view('payments.receipt', compact('payment'));
    }

    public function receiptPdf(Payment $payment)
    {
        $payment->load(['reservation.guest', 'reservation.room']);

        $pdf = Pdf::loadView('payments.receipt-pdf', compact('payment'));

        return $pdf->download('receipt-' . $payment->id . '.pdf');
    }
}
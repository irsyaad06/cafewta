<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Inertia\Inertia;
use Illuminate\Http\Request;

class QrisPaymentController extends Controller
{
    /**
     * Tampilkan halaman mock bank/QRIS payment.
     * Diakses setelah user scan QR code.
     */
    public function show(string $token)
    {
        $transaction = Transaction::where('qris_token', $token)
            ->with(['paymentMethod', 'transactionDetails'])
            ->first();

        if (!$transaction) {
            return abort(404, 'Transaksi tidak ditemukan.');
        }

        // Jika sudah dibayar, langsung ke halaman sukses
        if ($transaction->status === 'completed') {
            return Inertia::render('QrisPayment/AlreadyPaid', [
                'transaction' => $transaction,
            ]);
        }

        // Jika bukan pending_qris, transaksi tidak valid untuk QRIS
        if ($transaction->status !== 'pending_qris') {
            return abort(400, 'Transaksi tidak valid.');
        }

        return Inertia::render('QrisPayment/Pay', [
            'transaction' => $transaction,
            'token' => $token,
        ]);
    }

    /**
     * Konfirmasi pembayaran (setelah user input PIN).
     * PIN tidak divalidasi — kombinasi apapun diterima.
     */
    public function confirm(Request $request, string $token)
    {
        $request->validate([
            'pin' => 'required|digits:6',
        ]);

        $transaction = Transaction::where('qris_token', $token)
            ->where('status', 'pending_qris')
            ->first();

        if (!$transaction) {
            return response()->json([
                'success' => false,
                'message' => 'Transaksi tidak ditemukan atau sudah diproses.',
            ], 404);
        }

        // Update status menjadi completed
        $transaction->update([
            'status' => 'completed',
            'amount_paid' => $transaction->total_amount,
            'change_amount' => 0,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Pembayaran berhasil!',
            'redirect' => route('qris.success', ['token' => $token]),
        ]);
    }

    /**
     * Halaman konfirmasi setelah pembayaran sukses.
     */
    public function success(string $token)
    {
        $transaction = Transaction::where('qris_token', $token)
            ->with(['paymentMethod', 'transactionDetails', 'cafeTable'])
            ->first();

        if (!$transaction) {
            return abort(404, 'Transaksi tidak ditemukan.');
        }

        return Inertia::render('QrisPayment/Success', [
            'transaction' => $transaction,
        ]);
    }
}

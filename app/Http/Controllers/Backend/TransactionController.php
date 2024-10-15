<?php

namespace App\Http\Controllers\Backend;

use App\Exports\TransactionExport;
use App\Http\Controllers\Controller;
use App\Http\Services\FileService;
use App\Mail\BookingMailConfirm;
use App\Models\Review;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Maatwebsite\Excel\Facades\Excel;

class TransactionController extends Controller
{
    public function __construct(private FileService $fileService)
    {}

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $transactions = Transaction::latest()->paginate(10);

        return view('backend.transaction.index', [
            'transactions' => $transactions,
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $uuid)
    {
        $transaction = Transaction::where('uuid', $uuid)->firstOrFail();
        $review = Review::where('transaction_id', $transaction->id)->first();

        return view('backend.transaction.show', [
            'transaction' => $transaction,
            'review' => $review,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        if (!$request->user()->isOperator()) {
            abort(403, 'Unauthorized action.');
        }

        $data = $request->validate([
            'status' => 'required|in:pending,success,failed',
        ]);

        try {
            $transaction = Transaction::where('uuid', $id)->firstOrFail();
            $transaction->status = $data['status'];
            $transaction->save();

            // send email
            Mail::to($transaction->email)
                ->cc('feyfeifry@gmail.com')
                ->send(new BookingMailConfirm($transaction));

            return redirect()->back()->with('success', 'Transaction status updated successfully');
        } catch (\Exception $error) {
            return redirect()->back()->with('error', $error->getMessage());
        }

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $uuid)
    {
        if (!$request->user()->isOperator()) {
            abort(403, 'Unauthorized action.');
        }

        $transaction = Transaction::where('uuid', $uuid)->firstOrFail();

        $this->fileService->delete($transaction->file);

        $transaction->delete();

        return response()->json([
            'message' => 'Transaction has been deleted',
        ]);
    }

    public function download(Request $request)
    {
        $data = $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        try {
            return Excel::download(new TransactionExport($data['start_date'], $data['end_date']), 'transactions.xlsx');
        } catch (\Exception $error) {
            return redirect()->back()->with('error', $error->getMessage());
        }
    }
}

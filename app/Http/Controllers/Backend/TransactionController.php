<?php

namespace App\Http\Controllers\Backend;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Review;
use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Mail\BookingMailConfirm;
use App\Exports\TransactionExport;
use App\Http\Services\FileService;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
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

    public function dashboard()
    {
        $totalTransactions = Transaction::count();
        $pendingTransactions = Transaction::where('status', 'pending')->count();
        $failedTransactions = Transaction::where('status', 'failed')->count();
        $successfulTransactions = Transaction::where('status', 'success')->count();

        $totalRevenue = Transaction::where('status', 'success')->sum('amount');
        $averageTransactionValue = $successfulTransactions > 0 ? $totalRevenue / $successfulTransactions : 0;
        $successRate = $totalTransactions > 0 ? ($successfulTransactions / $totalTransactions) * 100 : 0;

        $lastWeekTransactions = Transaction::where('created_at', '>=', now()->subWeek())->count();
        $transactionTrend = $lastWeekTransactions > 0 ? (($totalTransactions - $lastWeekTransactions) / $lastWeekTransactions) * 100 : 0;

        $lastWeekRevenue = Transaction::where('status', 'success')
            ->where('created_at', '>=', now()->subWeek())
            ->sum('amount');
        $revenueTrend = $lastWeekRevenue > 0 ? (($totalRevenue - $lastWeekRevenue) / $lastWeekRevenue) * 100 : 0;

        $latestTransactions = Transaction::orderBy('created_at', 'desc')->take(5)->get();

        // Data for daily transaction chart - Fixed query
        $dailyTransactions = Transaction::selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->where('created_at', '>=', now()->subDays(7))
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('date')
            ->get();

        $dailyTransactionLabels = $dailyTransactions->pluck('date')->map(function ($date) {
            return Carbon::parse($date)->format('M d');
        });
        $dailyTransactionData = $dailyTransactions->pluck('count');

        return view('backend.dashboard.index', compact(
            'totalTransactions',
            'pendingTransactions',
            'failedTransactions',
            'successfulTransactions',
            'totalRevenue',
            'averageTransactionValue',
            'successRate',
            'transactionTrend',
            'revenueTrend',
            'latestTransactions',
            'dailyTransactionLabels',
            'dailyTransactionData'
        ));
    }
}

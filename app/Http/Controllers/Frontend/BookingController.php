<?php

namespace App\Http\Controllers\Frontend;

use App\Models\Transaction;
use App\Http\Services\FileService;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Mail;
use App\Http\Requests\BookingRequest;
use App\Mail\BookingMailPending;

class BookingController extends Controller
{
    public function store(BookingRequest $request)
    {
        $data = $request->validated();

        $fileService = new FileService();

        try {
            $data['file'] = 'transaction/' . $fileService->upload($data['file'], 'transaction');

            if ($request->type == 'event') {
                $data['amount'] = 100000;
            } else {
                $data['amount'] = 50000;
            }

            // send email
            Mail::to($data['email'])
                ->cc('feyfeifry@gmail.com')
                ->send(new BookingMailPending($data));
                // Jika ingin menggunakan queue juga bisa, pilih salah satu antara send() atau queue()
                // ->queue(new BookingMailPending($data));

            Transaction::create($data);

            return redirect()->back()->with('success', 'Booking has been sent');
        } catch (\Exception $err) {
            $fileService->delete($data['file']);

            return redirect()->back()->with('error', $err->getMessage());
        }
    }
}

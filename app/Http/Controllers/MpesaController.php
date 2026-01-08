<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;

use App\Models\PendingPayment;
use App\Models\Movie;
use App\Models\User;
use App\Models\Purchase;
use App\Models\Watchlist;
use App\Models\MovieAccessCode;

class MpesaController extends Controller
{
    public function initiate(Request $request)
    {
        $request->validate([
            'phone' => 'required',
            'amount' => 'required|numeric',
            'email' => 'required|email',
            'movie_id' => 'required|exists:movies,id',
        ]);

        $phone = $request->phone[0] === '0'
            ? '254' . substr($request->phone, 1)
            : $request->phone;

        $accessToken = $this->generateAccessToken(
            env('MPESA_CONSUMER_KEY'),
            env('MPESA_CONSUMER_SECRET')
        );

        $timestamp = now()->format('YmdHis');
        $password = base64_encode(
            env('MPESA_SHORTCODE') .
            env('MPESA_PASSKEY') .
            $timestamp
        );

        $response = Http::withToken($accessToken)->post(
            'https://api.safaricom.co.ke/mpesa/stkpush/v1/processrequest',
            [
                'BusinessShortCode' => env('MPESA_SHORTCODE'),
                'Password' => $password,
                'Timestamp' => $timestamp,
                'TransactionType' => 'CustomerBuyGoodsOnline',
                'Amount' => $request->amount,
                'PartyA' => $phone,
                'PartyB' => env('MPESA_TILL'),
                'PhoneNumber' => $phone,
                'CallBackURL' => route('mpesa.callback'),
                'AccountReference' => 'Movie Payment',
                'TransactionDesc' => 'Movie purchase',
            ]
        );

        if (isset($response['CheckoutRequestID'])) {
            PendingPayment::create([
                'email' => $request->email,
                'phone' => $phone,
                'amount' => $request->amount,
                'movie_id' => $request->movie_id,
                'checkout_request_id' => $response['CheckoutRequestID'],
            ]);
        }

        return response()->json($response->json());
    }

    public function callback(Request $request)
    {
        Log::info('MPESA CALLBACK', $request->all());

        $callback = $request->Body['stkCallback'];
        $payment = PendingPayment::where(
            'checkout_request_id',
            $callback['CheckoutRequestID']
        )->first();

        if (!$payment || $callback['ResultCode'] !== 0) {
            return response()->json(['ResultCode' => 0, 'ResultDesc' => 'Accepted']);
        }

        $meta = collect($callback['CallbackMetadata']['Item']);
        $receipt = $meta->firstWhere('Name', 'MpesaReceiptNumber')['Value'];

        $user = User::where('email', $payment->email)->first();
        if (!$user) return;

        // PURCHASE
        Purchase::create([
            'user_id' => $user->id,
            'movie_id' => $payment->movie_id,
            'amount' => $payment->amount,
            'status' => 'Success',
            'mpesa_receipt_number' => $receipt,
        ]);

        // WATCHLIST
        Watchlist::firstOrCreate([
            'user_id' => $user->id,
            'movie_id' => $payment->movie_id,
        ]);

        // ACCESS CODE
        $code = strtoupper(Str::random(8));

        MovieAccessCode::create([
            'user_id' => $user->id,
            'movie_id' => $payment->movie_id,
            'code' => $code,
            'expires_at' => now()->addDays(7),
        ]);

        Mail::raw(
            "Your movie access code: {$code}\n\nLogin and enter this code to watch.",
            fn ($m) => $m->to($user->email)->subject('Movie Access Code')
        );

        return response()->json(['ResultCode' => 0, 'ResultDesc' => 'Accepted']);
    }

    private function generateAccessToken($key, $secret)
    {
        $credentials = base64_encode("$key:$secret");
        return Http::withHeaders([
            'Authorization' => "Basic {$credentials}"
        ])->get(
            'https://api.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials'
        )['access_token'];
    }
}

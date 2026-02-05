<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use App\Models\PendingPayment;
use App\Models\Movie;
use App\Models\MerchandisePayment;
use App\Models\Payment;
use App\Models\User;
use App\Models\Purchase;
use App\Models\Watchlist;
use App\Models\WatchProgress;
use Illuminate\Support\HtmlString;
use Symfony\Component\Mime\Part\TextPart;


// use App\Http\Controllers\SecureStreamController;


class MpesaController extends Controller
{
    
public function sendEmail($email, $streamingCode, $movieTitle)
{
    $subject = "Your Movie Streaming Code for '{$movieTitle}'";

    // HTML content as string
    $htmlContent = "
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; background-color: #f5f5f5; color: #333; line-height: 1.6; }
            .container { max-width: 600px; margin: 30px auto; padding: 20px; background-color: #ffffff; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.1); text-align: center; }
            h1 { color: #1a73e8; }
            .code-box { display: inline-block; margin-top: 20px; padding: 15px 25px; font-size: 24px; font-weight: bold; background-color: #ffeb3b; color: #000; border-radius: 8px; letter-spacing: 2px; }
            p { font-size: 16px; }
            .footer { margin-top: 30px; font-size: 12px; color: #777; }
            a.button { display: inline-block; margin-top: 20px; padding: 10px 20px; background-color: #1a73e8; color: #fff; text-decoration: none; border-radius: 5px; font-weight: bold; }
        </style>
    </head>
    <body>
        <div class='container'>
            <h1>Your Streaming Code is Here!</h1>
            <p>Hello, thank you for purchasing <strong>{$movieTitle}</strong>.</p>
            <p>Log in to your account and use the code below to watch the movie:</p>
            <div class='code-box'>{$streamingCode}</div>
            <p class='footer'>This code is valid for one-time use. Keep it safe!</p>
        </div>
    </body>
    </html>
    ";

    try {
        Mail::send([], [], function ($message) use ($email, $subject, $htmlContent) {
            $message->to($email)
                    ->subject($subject)
                    ->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'))
                    ->html($htmlContent); // Use ->html() instead of setBody
        });

        Log::info("Styled email sent successfully to {$email}");
        return response()->json(['message' => 'Email sent successfully']);
    } catch (\Exception $e) {
        Log::error("Failed to send email to {$email}: " . $e->getMessage(), [
            'trace' => $e->getTraceAsString()
        ]);
        return response()->json([
            'message' => 'Failed to send email',
            'error' => $e->getMessage()
        ], 500);
    }
}



    public function testEmails()
    {
        $email = "enocmonyancha@gmail.com";
        $subject = "Test Mail";
        $messageBody = "Test Email";
        $host = env('MAIL_HOST', 'smtp.gmail.com');
        $port = env('MAIL_PORT', 587);
    
        try {
            // Check SMTP connection before sending
            $connection = @fsockopen($host, $port, $errno, $errstr, 10);
            if (!$connection) {
                $errorMsg = "Cannot connect to SMTP server {$host} on port {$port}: {$errstr} ({$errno})";
                Log::error($errorMsg);
                return response()->json([
                    'message' => 'SMTP connection failed',
                    'error' => $errorMsg
                ], 500);
            }
            fclose($connection);
    
            // Send email if connection works
            Mail::raw($messageBody, function ($message) use ($email, $subject) {
                $message->to($email)
                        ->subject($subject)
                        ->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));
            });
    
            Log::info("Test email sent successfully to {$email} with subject '{$subject}'");
    
            return response()->json(['message' => 'Email sent successfully']);
        } catch (\Exception $e) {
            Log::error("Email sending failed", [
                'to' => $email,
                'subject' => $subject,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
    
            return response()->json([
                'message' => 'Failed to send email',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function initiate(Request $request)
{
    $consumerKey = 'lFR5oJ6WlODRdctRZRGh46piAYbLvJnE5flitYTPk1DzYGU9';
    $consumerSecret = 'oSklA8P0ZvAnRhR7t80w0Ie46jJQbjXNCLiVTmrcM5ENsSriegI0aSB9Ivg1Gnk1';

    $phone_no = $request->input('phone');
    $amount = $request->input('amount');
    $email = $request->input('email');
    $movie_id = $request->input('movie_id');

    // Validate all required inputs
    if (empty($phone_no) || empty($email) || empty($movie_id)) {
        return response()->json([
            'status' => 'error',
            'message' => 'Phone number, email, and movie_id are required.'
        ], 400);
    }

    // Format phone number
    $phone = $phone_no[0] === '0'
        ? '254' . ltrim(str_replace(' ', '', $phone_no), '0')
        : $phone_no;

    // Generate access token
    $access_token = $this->generateAccessToken($consumerKey, $consumerSecret);

    if (!$access_token) {
        return response()->json([
            'status' => 'error',
            'message' => 'Failed to generate access token'
        ], 500);
    }

    $BusinessShortCode = '5729392';
    $passkey = 'd4baaa28ee7b2aee1864a9727884b7130d49ece7c9ac8a3361156cbbaeae6f7e';
    $Timestamp = '20' . date("ymdhis");
    $Password = base64_encode($BusinessShortCode . $passkey . $Timestamp);

    $payload = [
        'BusinessShortCode' => $BusinessShortCode,
        'Password' => $Password,
        'Timestamp' => $Timestamp,
        'TransactionType' => 'CustomerBuyGoodsOnline',
        'Amount' => $amount,
        'PartyA' => $phone,
        'PartyB' => '3563088',
        'PhoneNumber' => $phone,
        'CallBackURL' => 'https://api.falconeyephilmz.com/api/stk/callback',
        'AccountReference' => 'Movie Payment',
        'TransactionDesc' => 'Stk push for Movie Payment'
    ];

    $response = Http::withHeaders([
        'Authorization' => 'Bearer ' . $access_token,
        'Content-Type' => 'application/json',
    ])->post('https://api.safaricom.co.ke/mpesa/stkpush/v1/processrequest', $payload);

    $resData = $response->json();

    if (isset($resData['CheckoutRequestID'])) {
        PendingPayment::create([
            'phone' => $phone,
            'amount' => $amount,
            'email' => $email,
            'movie_id' => $movie_id,
            'checkout_request_id' => $resData['CheckoutRequestID'],
            'status' => 'pending',
        ]);
    }
    
  

    return response()->json($resData);
}


    private function generateAccessToken($consumerKey, $consumerSecret)
    {
        try {
            $credentials = base64_encode($consumerKey . ':' . $consumerSecret);

            $response = Http::withHeaders([
                'Authorization' => 'Basic ' . $credentials,
                'Content-Type' => 'application/json; charset=utf8'
            ])->get('https://api.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials');

            if ($response->successful() && isset($response->json()['access_token'])) {
                return $response->json()['access_token'];
            }

            Log::error('Failed to retrieve M-Pesa access token.', [
                'response' => $response->json()
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('Exception while retrieving M-Pesa token: ' . $e->getMessage());
            return null;
        }
    }

public function callback(Request $request)
{
    Log::info('MPESA STK Callback:', $request->all());

    $callback = $request->input('Body.stkCallback');
    $checkoutRequestId = $callback['CheckoutRequestID'];
    $resultCode = $callback['ResultCode'];
    $resultDesc = $callback['ResultDesc'];

    // Eager load movie relationship
    $payment = PendingPayment::with('movie')
        ->where('checkout_request_id', $checkoutRequestId)
        ->first();

    if (!$payment) {
        Log::warning("No pending payment found for CheckoutRequestID: $checkoutRequestId");
        return response()->json(['ResultCode' => 0, 'ResultDesc' => 'Success']);
    }

    if ($resultCode == 0) {
        $callbackMetadata = collect($callback['CallbackMetadata']['Item']);
        $mpesaReceiptNumber = $callbackMetadata->firstWhere('Name', 'MpesaReceiptNumber')['Value'] ?? null;
        $transactionDate = $callbackMetadata->firstWhere('Name', 'TransactionDate')['Value'] ?? null;

        $payment->update([
            'status' => 'Success',
            'mpesa_receipt_number' => $mpesaReceiptNumber,
            'transaction_date' => \Carbon\Carbon::createFromFormat('YmdHis', $transactionDate),
        ]);

        // Generate a unique streaming code
        $streamingCode = strtoupper(bin2hex(random_bytes(4))); // 8-character code

        if ($payment->movie) {
            $movieTitle = $payment->movie->title;

            Log::info("Sending streaming code to: {$payment->email} for movie ID: {$payment->movie->id}");

            // Send styled HTML email
            $this->sendEmail($payment->email, $streamingCode, $movieTitle);

            Log::info("Streaming code {$streamingCode} sent to {$payment->email}");

            // --- Save to purchases table ---
            $user = \DB::table('users')->where('email', $payment->email)->first();
            $userId = $user->id ?? null;

            if ($userId) {
                \DB::table('purchases')->insert([
                    'user_id' => $userId,
                    'movie_id' => $payment->movie_id,
                    'amount' => $payment->amount,
                    'email' => $payment->email,
                    'status' => 'Success',
                    'mpesa_receipt_number' => $mpesaReceiptNumber,
                    'access_code' => $streamingCode,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // --- Save to watchlists table ---
                \DB::table('watchlists')->updateOrInsert(
                    ['user_id' => $userId, 'movie_id' => $payment->movie_id],
                    ['created_at' => now(), 'updated_at' => now()]
                );

                // --- Save to watchlists table ---
                \DB::table('watchlists')->updateOrInsert(
                    [
                        'user_id' => $userId,
                        'movie_id' => $payment->movie_id
                    ],
                    [
                        'created_at' => now(),
                        'updated_at' => now()
                    ]
                );
                
                // --- Get duration from movies table ---
                $duration = Movie::where('id', $payment->movie_id)->value('duration');
                
                // --- Save to watch_progresses table ---
                if ($duration !== null) {
                    WatchProgress::updateOrCreate(
                        [
                            'user_id' => $userId,
                            'movie_id' => $payment->movie_id,
                        ],
                        [
                            'progress_seconds' => 0,
                            'duration_seconds' => (int) $duration,
                            'last_watched_at' => now(),
                        ]
                    );
                
                    Log::info('Watch progress created successfully', [
                        'user_id' => $userId,
                        'movie_id' => $payment->movie_id,
                        'duration' => $duration,
                    ]);
                }



            } else {
                Log::warning("No user found for email: {$payment->email}");
            }
        } else {
            Log::error("Movie not found for ID: {$payment->movie_id}");
        }
    } else {
        $payment->update([
            'status' => 'Failed',
        ]);
        Log::warning("Payment failed: $resultDesc for CheckoutRequestID: $checkoutRequestId");
    }

    return response()->json(['ResultCode' => 0, 'ResultDesc' => 'Success']);
}



    public function registerC2bUrls()
    {
        $access_token = $this->generateAccessToken(
            'lFR5oJ6WlODRdctRZRGh46piAYbLvJnE5flitYTPk1DzYGU9',
            'oSklA8P0ZvAnRhR7t80w0Ie46jJQbjXNCLiVTmrcM5ENsSriegI0aSB9Ivg1Gnk1'
        );

        if (!$access_token) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to generate access token'
            ], 500);
        }

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $access_token,
            'Content-Type' => 'application/json'
        ])->post('https://api.safaricom.co.ke/mpesa/c2b/v2/registerurl', [
            'ShortCode' => '5729392',
            'ResponseType' => 'Completed',
            'ConfirmationURL' => 'https://api.falconeyephilmz.com/api/c2b/confirmation',
            'ValidationURL' => 'https://api.falconeyephilmz.com/api/c2b/validation'
        ]);

        return response()->json($response->json(), 201);
    }

    public function c2bValidation(Request $request)
    {
        Log::info("C2B Validation Log:", $request->all());
        return response()->json(['ResultCode' => 0, 'ResultDesc' => 'Accepted']);
    }

    public function c2bConfirmation(Request $request)
    {
        Log::info("C2B Confirmation Log:", $request->all());
        return response()->json(['ResultCode' => 0, 'ResultDesc' => 'Accepted']);
    }
    
  public function getMpesaPayments()
{
    $moviePayments = Payment::whereNotNull('checkout_request_id')
        ->orderByDesc('created_at')
        ->get()
        ->map(function ($payment) {
            $payment->payment_for = 'movie';
            return $payment;
        });

    $merchandisePayments = MerchandisePayment::whereNotNull('checkout_request_id')
        ->orderByDesc('created_at')
        ->get()
        ->map(function ($payment) {
            $payment->payment_for = 'merchandise';
            return $payment;
        });

    $allPayments = $moviePayments->merge($merchandisePayments)
        ->sortByDesc('created_at')
        ->values();

    return response()->json($allPayments);
}

public function movieSalesAnalytics()
{
    $payments = \DB::table('payments')
        ->join('movies', 'payments.movie_id', '=', 'movies.id')
        ->selectRaw('movies.title as movie, COUNT(*) as sales_count, SUM(payments.amount) as total_revenue')
        ->where('payments.status', 'Success')
        ->groupBy('movies.title')
        ->get();

    return response()->json($payments);
}


}

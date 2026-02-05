<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Crypt;
use App\Models\EventPayment;
use App\Models\Event;
use Carbon\Carbon;
use Illuminate\Support\Str;

class EventPaymentController extends Controller
{
 
    /**
 * Send event ticket via email
 */
public function sendTicket($email, $event, $ticketCode, $attendeeName = 'Attendee')
{
    $subject = "Your Event Ticket: {$event->title}";

    // Replace with your logo URL
    $logoUrl = asset('https://www.falconeyephilmz.com/_next/image?url=%2Flogos%2FFALCON%20EYE%20PHILMZ%20REVERMPED%20LOGO%20(3).jpg&w=48&q=75'); 
    // Or store in public/images folder and use asset()

    $html = '
    <html>
    <head>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <style>
            body {
                font-family: Arial, sans-serif;
                background-color: #e9ecef;
                margin: 0;
                padding: 20px;
            }
            .ticket-wrapper {
                max-width: 650px;
                margin: 0 auto;
                background: linear-gradient(135deg, #ffffff 0%, #f9f9f9 100%);
                border-radius: 15px;
                overflow: hidden;
                box-shadow: 0 6px 18px rgba(0,0,0,0.1);
                border: 1px solid #ddd;
            }
            .ticket-header {
                background: #007bff;
                color: #fff;
                padding: 20px;
                text-align: center;
                position: relative;
            }
            .ticket-header img {
                max-height: 60px;
                margin-bottom: 10px;
            }
            .ticket-header h1 {
                font-size: 24px;
                margin: 0;
            }
            .ticket-body {
                padding: 30px;
                color: #333;
                background: #ffffff;
            }
            .ticket-body h2 {
                margin-top: 0;
                font-size: 20px;
                color: #007bff;
            }
            .ticket-details {
                margin-top: 20px;
                padding: 15px;
                border-radius: 10px;
                background: #f1f5ff;
                font-size: 15px;
                line-height: 1.6;
            }
            .ticket-detail {
                margin-bottom: 8px;
            }
            .ticket-code {
                display: inline-block;
                margin-top: 20px;
                padding: 12px 20px;
                background: #28a745;
                color: #fff;
                border-radius: 8px;
                font-weight: bold;
                font-size: 18px;
                letter-spacing: 1px;
            }
            .qr-container {
                text-align: right;
                margin-top: 20px;
            }
            .qr-container img {
                max-width: 100px;
                border-radius: 8px;
            }
            .ticket-footer {
                background: #f8f9fa;
                padding: 15px;
                text-align: center;
                font-size: 13px;
                color: #777;
            }
            @media(max-width: 600px){
                .ticket-body, .ticket-header, .ticket-footer {
                    padding: 15px;
                }
                .ticket-header h1 {
                    font-size: 20px;
                }
                .ticket-body h2 {
                    font-size: 18px;
                }
            }
        </style>
    </head>
    <body>
        <div class="ticket-wrapper">
            <div class="ticket-header">
                <img src="' . $logoUrl . '" alt="Falcone Eye Phlmz Logo">
                <h1>' . htmlspecialchars($event->title) . '</h1>
            </div>
            <div class="ticket-body">
                <h2>Hello ' . htmlspecialchars($attendeeName) . ',</h2>
                <p>Thank you for booking! Here are your event details:</p>

                <div class="ticket-details">
                    <div class="ticket-detail"><strong>Date:</strong> ' . htmlspecialchars($event->date) . '</div>
                    <div class="ticket-detail"><strong>Location:</strong> ' . htmlspecialchars($event->location) . '</div>
                    <div class="ticket-detail"><strong>Attendee:</strong> ' . htmlspecialchars($attendeeName) . '</div>
                </div>

                <div class="ticket-code">
                    TICKET CODE: ' . htmlspecialchars($ticketCode) . '
                </div>

                <div class="qr-container">
                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=100x100&data=' . urlencode($ticketCode) . '" alt="QR Code">
                </div>
            </div>
            <div class="ticket-footer">
                Please present this ticket at the entrance. | Falcon Eye Philmz © ' . date("Y") . '
            </div>
        </div>
    </body>
    </html>';

    try {
        \Mail::html($html, function ($message) use ($email, $subject) {
            $message->to($email)
                ->subject($subject)
                ->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));
        });

        \Log::info("Ticket email sent successfully to {$email} for event {$event->title}");
        return true;
    } catch (\Exception $e) {
        \Log::error("Failed to send ticket email to {$email}", [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        return false;
    }
}



    /**
     * Initiate Event Payment
     */
    public function initiate(Request $request)
    {
        $consumerKey = 'lFR5oJ6WlODRdctRZRGh46piAYbLvJnE5flitYTPk1DzYGU9';
    $consumerSecret = 'oSklA8P0ZvAnRhR7t80w0Ie46jJQbjXNCLiVTmrcM5ENsSriegI0aSB9Ivg1Gnk1';

        $phone_no = $request->input('phone');
        $amount = $request->input('amount');
        $email = $request->input('email');
        $event_id = $request->input('event_id');

        if (empty($phone_no) || empty($email) || empty($event_id)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Phone number, email, and event_id are required.'
            ], 400);
        }

        // Format phone
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
        'CallBackURL' => 'https://api.falconeyephilmz.com/api/events/stk/callback',
        'AccountReference' => 'Event Payment',
        'TransactionDesc' => 'Stk push for Event Payment'
    ];


        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $access_token,
            'Content-Type' => 'application/json',
        ])->post('https://api.safaricom.co.ke/mpesa/stkpush/v1/processrequest', $payload);

        $resData = $response->json();

        if (isset($resData['CheckoutRequestID'])) {
            EventPayment::create([
                'phone' => $phone,
                'amount' => $amount,
                'email' => $email,
                'event_id' => $event_id,
                'checkout_request_id' => $resData['CheckoutRequestID'],
                'attendee_name' => $request->input('attendee_name'),
            ]);
        }

        return response()->json($resData);
    }

    /**
     * M-Pesa Callback
     */
  public function callback(Request $request)
    {
        Log::info('Event Payment Callback RAW:', $request->all());
    
        $callback = $request->all()['Body']['stkCallback'] ?? null;
    
        if (!$callback) {
            Log::error('Invalid callback payload');
            return response()->json(['ResultCode' => 0, 'ResultDesc' => 'Success']);
        }
    
        $checkoutRequestId = $callback['CheckoutRequestID'];
        $resultCode = $callback['ResultCode'];
        $resultDesc = $callback['ResultDesc'];
    
        $payment = EventPayment::where('checkout_request_id', $checkoutRequestId)->first();
    
        if (!$payment) {
            Log::warning("No event payment found for CheckoutRequestID: {$checkoutRequestId}");
            return response()->json(['ResultCode' => 0, 'ResultDesc' => 'Success']);
        }
    
        if ($resultCode == 0) {
            $metadata = collect($callback['CallbackMetadata']['Item']);
    
            $mpesaReceiptNumber = $metadata
                ->firstWhere('Name', 'MpesaReceiptNumber')['Value'] ?? null;
    
            $transactionDate = $metadata
                ->firstWhere('Name', 'TransactionDate')['Value'] ?? null;
    
            $ticketCode = strtoupper(Str::random(10));
    
            // ✅ SAFE FIELD-BY-FIELD UPDATE
            $payment->status = 'Success';
            $payment->ticket_code = $ticketCode;
            $payment->mpesa_receipt_number = $mpesaReceiptNumber;
            $payment->transaction_date = $transactionDate
                ? Carbon::createFromFormat('YmdHis', $transactionDate)
                : now();
    
            $payment->save();
    
            $event = Event::find($payment->event_id);
    
            if ($event) {
                $attendeeName = $payment->attendee_name
                    ?: explode('@', $payment->email)[0];
    
                $this->sendTicket(
                    $payment->email,
                    $event,
                    $ticketCode,
                    ucfirst($attendeeName)
                );
            }
    
            Log::info("Event payment SUCCESS for {$checkoutRequestId}");
        } else {
            $payment->status = 'Failed';
            $payment->save();
    
            Log::warning("Event payment FAILED: {$resultDesc}");
        }
    
        return response()->json(['ResultCode' => 0, 'ResultDesc' => 'Success']);
    }



    /**
     * Generate M-Pesa Access Token
     */
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
}

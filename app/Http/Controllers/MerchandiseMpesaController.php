<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use App\Models\MerchandisePayment;
use App\Models\Merchandise;
use App\Models\MerchandiseOrder;
use Carbon\Carbon;

class MerchandiseMpesaController extends Controller
{
    // -----------------------------
    // Send detailed confirmation email
    // -----------------------------
    public function sendConfirmationEmail($email, $itemName, $referralId, $amount)
    {
        $subject = "Your Merchandise Purchase Confirmation";

        $messageBody = "
Hello,

Thank you for your purchase!

Item Purchased: {$itemName}
Amount Paid: KES {$amount}
Referral ID: {$referralId}
Purchase Date: " . now()->format('d M Y H:i') . "

Next Steps:
Please provide your order details (color, size, location, and preferred contact) to complete the delivery. 

We will contact you shortly once we receive your order details.

Thank you for shopping with us!
    
Best Regards,
Falcon Eye Philmz
";

        Mail::raw($messageBody, function ($message) use ($email, $subject) {
            $message->to($email)
                    ->subject($subject)
                    ->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));
        });

        return response()->json(['message' => 'Email sent successfully']);
    }

    // -----------------------------
    // Initiate M-Pesa STK Push
    // -----------------------------
    public function initiate(Request $request)
    {
        $consumerKey = 'lFR5oJ6WlODRdctRZRGh46piAYbLvJnE5flitYTPk1DzYGU9';
        $consumerSecret = 'oSklA8P0ZvAnRhR7t80w0Ie46jJQbjXNCLiVTmrcM5ENsSriegI0aSB9Ivg1Gnk1';

        $phone_no = $request->input('phone');
        $amount = $request->input('amount');
        $email = $request->input('email');
        $merchandise_id = $request->input('merchandise_id');

        if (empty($phone_no) || empty($email) || empty($merchandise_id)) {
            return response()->json(['status' => 'error', 'message' => 'Missing required fields'], 400);
        }

        $phone = $phone_no[0] === '0' ? '254' . ltrim(str_replace(' ', '', $phone_no), '0') : $phone_no;

        $access_token = $this->generateAccessToken($consumerKey, $consumerSecret);
        if (!$access_token) {
            return response()->json(['status' => 'error', 'message' => 'Failed to generate access token'], 500);
        }

        $BusinessShortCode = '5729392';
        $passkey = 'd4baaa28ee7b2aee1864a9727884b7130d49ece7c9ac8a3361156cbbaeae6f7e';
        $Timestamp = '20' . date("ymdhis");
        $Password = base64_encode($BusinessShortCode . $passkey . $Timestamp);

        $referral_id = uniqid('ref_');

        $payload = [
            'BusinessShortCode' => $BusinessShortCode,
            'Password' => $Password,
            'Timestamp' => $Timestamp,
            'TransactionType' => 'CustomerBuyGoodsOnline',
            'Amount' => $amount,
            'PartyA' => $phone,
            'PartyB' => '3563088',
            'PhoneNumber' => $phone,
            'CallBackURL' => url('/api/stk/merchandise-callback'),
            'AccountReference' => 'Merchandise Purchase',
            'TransactionDesc' => 'Payment for merchandise'
        ];

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $access_token,
            'Content-Type' => 'application/json',
        ])->post('https://api.safaricom.co.ke/mpesa/stkpush/v1/processrequest', $payload);

        $resData = $response->json();

        if (isset($resData['CheckoutRequestID'])) {
            $payment = MerchandisePayment::create([
                'phone' => $phone,
                'amount' => $amount,
                'email' => $email,
                'merchandise_id' => $merchandise_id,
                'checkout_request_id' => $resData['CheckoutRequestID'],
                'referral_id' => $referral_id,
                'status' => 'Pending',
            ]);
        } else {
            Log::error('M-Pesa initiation failed', $resData);
        }

        return response()->json($resData);
    }

    // -----------------------------
    // Generate M-Pesa Access Token
    // -----------------------------
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

            Log::error('Failed to retrieve M-Pesa access token', [
                'response' => $response->json()
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('Exception while retrieving M-Pesa token: ' . $e->getMessage());
            return null;
        }
    }

    // -----------------------------
    // Handle M-Pesa Callback
    // -----------------------------
    public function merchandiseCallback(Request $request)
    {
        Log::info('Merchandise Callback:', $request->all());

        $callback = $request->input('Body.stkCallback');
        $checkoutRequestId = $callback['CheckoutRequestID'];
        $resultCode = $callback['ResultCode'];
        $resultDesc = $callback['ResultDesc'];

        $payment = MerchandisePayment::where('checkout_request_id', $checkoutRequestId)->first();

        if (!$payment) {
            Log::warning("No pending payment found for ID: $checkoutRequestId");
            return response()->json(['ResultCode' => 0, 'ResultDesc' => 'Success']);
        }

        if ($resultCode == 0) {
            $metadata = collect($callback['CallbackMetadata']['Item']);
            $receipt = $metadata->firstWhere('Name', 'MpesaReceiptNumber')['Value'] ?? null;
            $date = $metadata->firstWhere('Name', 'TransactionDate')['Value'] ?? null;

            $payment->update([
                'status' => 'Success',
                'mpesa_receipt_number' => $receipt,
                'transaction_date' => Carbon::createFromFormat('YmdHis', $date),
            ]);

            $item = Merchandise::find($payment->merchandise_id);
            if ($item) {
                // Send improved confirmation email
                $this->sendConfirmationEmail($payment->email, $item->name, $payment->referral_id, $payment->amount);
            }

            // Return the payment id and item name so frontend can trigger order details modal
            return response()->json([
                'ResultCode' => 0,
                'ResultDesc' => 'Success',
                'payment_id' => $payment->id,
                'item_name' => $item?->name,
            ]);
        } else {
            $payment->update(['status' => 'Failed']);
        }

        return response()->json(['ResultCode' => 0, 'ResultDesc' => 'Success']);
    }

    // -----------------------------
    // Save Order Details after payment
    // -----------------------------
    public function saveOrderDetails(Request $request)
    {
        $request->validate([
            'payment_id' => 'required|exists:merchandise_payments,id',
            'color' => 'required|string',
            'size' => 'required|string',
            'preferred_phone' => 'required|string',
            'location' => 'required|string',
            'additional_info' => 'nullable|string',
        ]);

        $order = MerchandiseOrder::create([
            'payment_id' => $request->payment_id,
            'color' => $request->color,
            'size' => $request->size,
            'preferred_phone' => $request->preferred_phone,
            'location' => $request->location,
            'additional_info' => $request->additional_info,
        ]);

        return response()->json([
            'message' => 'Order details saved successfully',
            'order' => $order
        ]);
    }
    
    public function getPaymentStatus($checkoutRequestId)
        {
            $payment = MerchandisePayment::where('checkout_request_id', $checkoutRequestId)->first();
        
            if (!$payment) {
                return response()->json(['status' => 'NotFound']);
            }
        
            $item = Merchandise::find($payment->merchandise_id);
        
            return response()->json([
                'status' => $payment->status,
                'id' => $payment->id,
                'item_name' => $item?->name
            ]);
        }

}

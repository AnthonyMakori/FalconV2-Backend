<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use App\Models\Movie;

class MpesaController extends Controller
{
    public function sendEmail($email, $link)
    {
        $subject = "Movie Link";
        $messageBody = "Hello, here is the link to the movie: {$link}";

        Mail::raw($messageBody, function ($message) use ($email, $subject) {
            $message->to($email)
                    ->subject($subject)
                    ->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));
        });

        return response()->json(['message' => 'Email sent successfully']);
    }

    public function getAccessToken()
    {
        $url = 'https://sandbox.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials';

        $credentials = base64_encode(env('MPESA_CONSUMER_KEY') . ':' . env('MPESA_CONSUMER_SECRET'));
        
        $headers = [
            "Authorization: Basic {$credentials}",
            'Content-Type: application/json',
        ];

        $response = $this->makeCurlRequest($url, $headers, null, 'GET');

        if ($response['http_code'] === 200) {
            $data = json_decode($response['response'], true);
            return $data['access_token'] ?? null;
        } else {
            throw new \Exception("Failed to authenticate: HTTP {$response['http_code']} - {$response['response']}");
        }
    }

    public function stkPush(Request $request)
    {
        // pass the movie id to the request
        $request->validate([
            'phone' => 'required|string|min:10',
            'amount' => 'required|numeric|min:1',
            'email' => 'required|email',
            'movie_id' => 'required|exists:movies,id'
        ]);

        $phone = $this->formatPhoneNumber($request->phone);
        $amount = (int) $request->amount;
        $paybill = env('MPESA_SHORTCODE');
        $passkey = env('MPESA_PASSKEY');
        $timestamp = now()->format('YmdHis');
        $password = base64_encode($paybill . $passkey . $timestamp);
        $callbackUrl = "https://your-callback-url.com";

        $token = $this->getAccessToken();

        if (!$token) {
            return response()->json(['error' => 'Failed to authenticate M-Pesa'], 500);
        }

        $payload = [
            "BusinessShortCode" => $paybill,
            "Password" => $password,
            "Timestamp" => $timestamp,
            "TransactionType" => "CustomerPayBillOnline",
            "Amount" => $amount,
            "PartyA" => $phone,
            "PartyB" => $paybill,
            "PhoneNumber" => $phone,
            "CallBackURL" => $callbackUrl,
            "AccountReference" => "CompanyXLTD",
            "TransactionDesc" => "Payment of X"
        ];

        $headers = [
            "Authorization: Bearer {$token}",
            'Content-Type: application/json'
        ];

        $response = $this->makeCurlRequest('https://sandbox.safaricom.co.ke/mpesa/stkpush/v1/processrequest', $headers, json_encode($payload), 'POST');

        // get the movie
        $movie = Movie::find($request->movie_id);
        $movie_link = 'http://127.0.0.1:8000/storage/' . $movie->movie;
        // you can get the link from db and send it to the user
        // to be changed to callback function
        $this->sendEmail($request->email, $movie_link);

        return response()->json([
            'token_response' => $token,
            'request' => $request->all(),
            'mpesa_response' => json_decode($response['response'], true)
        ]);
    }

    // thats about it, you can enhance the email template to your liking

    private function makeCurlRequest($url, $headers, $payload = null, $method = 'POST')
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        if (!empty($payload) && ($method === 'POST' || $method === 'PUT')) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if (curl_errno($ch)) {
            throw new \Exception('Curl error: ' . curl_error($ch));
        }

        curl_close($ch);

        return [
            'http_code' => $httpCode,
            'response' => $response,
        ];
    }

    private function formatPhoneNumber($phone)
    {
        return preg_replace('/^0/', '254', $phone);
    }

    public function stkCallback(Request $request)
    {
        return response()->json(['message' => 'Callback received.']);
    }
}


// mpesa is okay, you'll need to change to Live credentials to test with real money
// also remember to change the callback URL to your live server URL
// to process email link you can await for callback to be successful then send email to user with the link to the movie
// you can also store the transaction in the database to keep track of the transactions
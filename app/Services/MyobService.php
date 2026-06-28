<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use App\Models\MyobToken;
use Carbon\Carbon;
use Exception;

class MyobService
{
    protected $baseUrl = 'https://arl2.api.myob.com/accountright';
    protected $companyFileGuid = '6e074fba-5fe1-4499-b02c-9b450e483141'; // Replace with actual GUID

    public function getAccessToken()
    {
        $token = MyobToken::latest()->first();

        if (!$token) {
            throw new Exception('MYOB token not found');
        }

        // Return token if still valid
        if (now()->lt($token->access_token_expires_at)) {
            return $token->access_token;
        }

        // Refresh token logic
        if (now()->gt($token->refresh_token_expires_at)) {
            throw new Exception('Refresh token expired. Please re-authenticate.');
        }

        $response = Http::asForm()->post('https://secure.myob.com/oauth2/v1/authorize', [
            'client_id' => trim(env('MYOB_CLIENT_ID')),
            'client_secret' => trim(env('MYOB_CLIENT_SECRET')),
            'grant_type' => 'refresh_token',
            'refresh_token' => trim($token->refresh_token),
        ]);

        if ($response->failed()) {
            dd('STATUS', $response->status(), 'BODY', $response->body());
        }


        if ($response->failed()) {
            throw new Exception('Failed to refresh token: ' . $response->body());
        }

        $data = $response->json();

        $token->update([
            'access_token' => $data['access_token'],
            'refresh_token' => $data['refresh_token'],
            'access_token_expires_at' => now()->addMinutes(20),
            'refresh_token_expires_at' => now()->addDays(7),
        ]);

        return $data['access_token'];
    }

    protected function getHeaders()
    {
        return [
            'Authorization' => 'Bearer ' . $this->getAccessToken(),
            'x-myobapi-key' => env('MYOB_CLIENT_ID'),
            'x-myobapi-version' => 'v2',
            'Accept-Encoding' => 'gzip,deflate',
            'Content-Type' => 'application/json'
        ];
    }

    public function createCustomer(array $customerData)
    {

        $url = "{$this->baseUrl}/{$this->companyFileGuid}/Contact/Customer";
        $response = Http::withHeaders($this->getHeaders())->post($url, $customerData);
        if ($response->failed()) {
            throw new Exception('Failed to create customer: ' . $response->body());
        }

        return $response->json();
    }

    public function findCustomerByDisplayId($displayId)
    {
        $url = "{$this->baseUrl}/{$this->companyFileGuid}/Contact/Customer";

        $response = Http::withHeaders($this->getHeaders())
            ->get($url, [
                '$filter' => "DisplayID eq '{$displayId}'"
            ]);

        if ($response->failed()) {
            throw new \Exception('Failed to find customer: ' . $response->body());
        }

        $data = $response->json();

        // MYOB returns results in an array under 'Items'
        if (!empty($data['Items'][0])) {
            return $data['Items'][0];
        }

        throw new \Exception("Customer with DisplayID {$displayId} not found.");
    }


    public function createItem(array $itemData)
    {
        $url = "{$this->baseUrl}/{$this->companyFileGuid}/Inventory/Item";

        $response = Http::withHeaders($this->getHeaders())->post($url, $itemData);

        if ($response->failed()) {
            throw new Exception('Failed to create item: ' . $response->body());
        }

        return $response->json();
    }

    public function findItemByNumber(string $number)
    {
        $filter = urlencode("Number eq '$number'");
        $url = "{$this->baseUrl}/{$this->companyFileGuid}/Inventory/Item?\$filter={$filter}";

        $response = Http::withHeaders($this->getHeaders())->get($url);

        if ($response->failed()) {
            throw new \Exception('Failed to find item by number: ' . $response->body());
        }

        $items = $response->json()['Items'] ?? [];

        return $items[0] ?? null; // Return full item so you can access UID or other fields
    }


    public function createInvoice(array $invoiceData)
    {
        $url = "{$this->baseUrl}/{$this->companyFileGuid}/Sale/Invoice/Service";
        /* echo $url;
         echo "<pre>";
         print_r($invoiceData);die;*/

        $response = Http::withHeaders($this->getHeaders())->post($url, $invoiceData);

        if ($response->failed()) {
            throw new \Exception('Failed to create invoice: ' . $response->body());
        }

        return $response->json();
    }

    public function findInvoiceByNumber(string $invoiceNumber)
    {
        $filter = urlencode("InvoiceNumber eq '$invoiceNumber'");
        $url = "{$this->baseUrl}/{$this->companyFileGuid}/Sale/Invoice/Service?$filter=$filter";

        $response = Http::withHeaders($this->getHeaders())->get($url);

        if ($response->failed()) {
            throw new \Exception('Failed to fetch invoice by number: ' . $response->body());
        }

        $invoices = $response->json()['Items'] ?? [];
        return $invoices[0] ?? null;
    }

}

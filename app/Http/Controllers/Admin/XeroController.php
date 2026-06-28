<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;
use XeroAPI\XeroPHP\Api\AccountingApi;
use XeroAPI\XeroPHP\Configuration;
use XeroAPI\XeroPHP\Models\Accounting\Contact;
use XeroAPI\XeroPHP\Models\Accounting\Invoice;
use XeroAPI\XeroPHP\Models\Accounting\LineItem;
use XeroAPI\XeroPHP\Models\Accounting\Invoices;
use GuzzleHttp\Client;

class XeroController extends Controller
{
    public function redirectToXero()
    {
        $authUrl = "https://login.xero.com/identity/connect/authorize?" . http_build_query([
                'response_type' => 'code',
                'client_id' => env('XERO_CLIENT_ID'),
                'redirect_uri' => env('XERO_REDIRECT_URI'),
                // Added accounting.settings for Accounts API access
                'scope' => 'openid profile email accounting.transactions accounting.contacts accounting.settings offline_access',
                'state' => csrf_token(),
            ]);

        return redirect($authUrl);
    }

    public function callback(Request $request)
    {
        echo $request->get('code');die;
        if (!$request->has('code')) {
            return redirect()->route('admin.xero.login');
        }

        $response = Http::asForm()->post('https://identity.xero.com/connect/token', [
            'grant_type' => 'authorization_code',
            'code' => $request->get('code'),
            'redirect_uri' => env('XERO_REDIRECT_URI'),
            'client_id' => env('XERO_CLIENT_ID'),
            'client_secret' => env('XERO_CLIENT_SECRET'),
        ]);

        if (!$response->successful()) {
            return response()->json(['error' => 'Token exchange failed']);
        }

        $token = $response->json();

        // Fetch tenant ID
        $tenantResponse = Http::withToken($token['access_token'])->get('https://api.xero.com/connections');
        $tenantId = $tenantResponse->json()[0]['tenantId'] ?? null;

        Session::put('xero_token', $token);
        Session::put('tenant_id', $tenantId);

        return redirect()->route('admin.xero.create-invoice');
    }

    public function createInvoice()
    {
        $token = Session::get('xero_token');

        $tenantId = Session::get('tenant_id');


        if (!$token || !$tenantId) {
            return redirect()->route('admin.xero.login');
        }

        $config = Configuration::getDefaultConfiguration()->setAccessToken($token['access_token']);
        $apiInstance = new AccountingApi(new Client(), $config);

        /*  try {
              $response = $apiInstance->getContacts($tenantId);
              $contacts = $response->getContacts();

              // Example: dump the first contact
              if (count($contacts) > 0) {
                  $contactId = $contacts[0]->getContactId();
                  dd("Contact ID: " . $contactId);
              } else {
                  dd("No contacts found in Xero account.");
              }
          } catch (\Exception $e) {
              return response()->json(['error' => $e->getMessage()]);
          }*/

        $contact = new Contact(['contact_id' => '7dd1dd56-9366-4aa3-a197-72ba50731675']); // Replace with valid Xero contact ID
        $lineItem = new LineItem([
            'description' => 'Laravel Invoice',
            'quantity' => 1,
            'unit_amount' => 99.99,
            'account_code' => '200' // Revenue account
        ]);

        $invoice = new Invoice([
            'type' => 'ACCREC',
            'contact' => $contact,
            'line_items' => [$lineItem],
            'date' => date('Y-m-d'),
            'due_date' => date('Y-m-d', strtotime('+7 days')),
            'status' => 'AUTHORISED'
        ]);

        try {
            $result = $apiInstance->createInvoices($tenantId, new Invoices(['invoices' => [$invoice]]));
            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()]);
        }
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DealImage;
use App\Models\DealShipStationOrder;
use App\Models\WatchBrand;
use App\Services\ShipStationService;
use File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Input;
use Redirect;
use Session;
use Artisan;
use Config;
use Carbon\Carbon;
use Illuminate\Support\Str;
use App\Models\Deal;
use App\Models\City;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use App\Jobs\GenerateInvoiceFromDealJob;
use App\Pdf\CustomPdf;
use App\Services\MyobService;
use App\Services\XeroService;
use App\Services\InvoicePdfService;
use App\Jobs\SendDealCreatedNotificationJob;
use App\Jobs\SendDealReviewedNotificationJob;
use App\Jobs\SendDealUpdatedNotificationJob;
use App\Jobs\SendInvoiceEmailToCustomerJob;
use App\Models\CompanyDetail;
// add this use at the top
class DealController extends Controller
{
    protected $myob;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(MyobService $myob)
    {
        $this->myob = $myob;
        $this->middleware('admin');

        $this->middleware('role:super admin')->only(['create', 'store', 'edit', 'update', 'destroy']);
    }

    public function index(Request $request)
    {

        $input = $request->all();

        $search = (object)null;
        $arr['search'] = $search;
        $showrecord = trans('admin.ADMIN_PAGE_LIMIT_NO');
        $deals = new Deal();

        if (isset($input['search_text']) && $input['search_text'] != '') {
            $search_text = $input['search_text'];
            $deals = $deals->where('model_number', 'LIKE', "%$search_text%");
            $search->search_text = $search_text;
        }

        if (isset($input['deal_status']) && $input['deal_status'] != '') {
            $deal_status = $input['deal_status'];
            $deals = $deals->where("deal_status", $deal_status);
            $search->deal_status = $deal_status;
        }

        if (isset($input['brand_id']) && $input['brand_id'] != '') {
            $brand_id = $input['brand_id'];
            $deals = $deals->where("brand_id", $brand_id);
            $search->brand_id = $brand_id;
        }

        if (isset($input['showrecord']) && $input['showrecord'] != '') {
            $showrecord = $input['showrecord'];
            $search->showrecord = $showrecord;
            Session::put('showrecord', $showrecord);
        }

        // Filter by date range (Start Date and End Date)
        if (isset($input['start_date']) && $input['start_date'] != '') {
            $start_date = Carbon::createFromFormat('Y-m-d', $input['start_date'])->startOfDay();
            $deals = $deals->where('created_at', '>=', $start_date);
            $search->start_date = $input['start_date']; // For passing the filter to the view
        }

        if (isset($input['end_date']) && $input['end_date'] != '') {
            $end_date = Carbon::createFromFormat('Y-m-d', $input['end_date'])->endOfDay();
            $deals = $deals->where('created_at', '<=', $end_date);
            $search->end_date = $input['end_date']; // For passing the filter to the view
        }
        // filters
        $filters = $request->all();
        unset($filters['_token']);
        $arr['filters'] = $filters;

        $deals = $deals->orderBy('id', 'desc')->paginate($showrecord);
        $brands = WatchBrand::orderBy('name', 'asc')->pluck('name', 'id')->toArray();
        return view('admin.deals.index', compact('deals', 'brands'))->with($arr)->with('i', ($request->input('page', 1) - 1) * $showrecord);
    }

    public function create()
    {
        $countries = $this->getCountires();
        $states = $this->getStates();
        $cities = $this->getCities('1');

        $watchBrands = WatchBrand::orderBy('name', 'ASC')->pluck('name', 'id')->prepend("Select Brand", "");

        return View::make("admin.deals.add", compact('countries', 'states', 'cities', 'watchBrands'));
    }


    public function dealPurchaseInvoiceGenerate($dealId)
    {
        try {
            $deal = Deal::findOrFail($dealId);
            $xero = app(XeroService::class);
            $contactName = $deal->customer_type === 'individual'
                ? "{$deal->first_name} {$deal->last_name}"
                : $deal->dealCustomerTypeDetail->company_name;

            $company_firstName = '';
            $company_lastName = '';
            if ($deal->customer_type != 'individual') {
                $companyName = $deal->dealCustomerTypeDetail->company_name;
                $nameParts = explode(' ', trim($companyName));
                $company_firstName = $nameParts[0] ?? '';
                $company_lastName = count($nameParts) > 1 ? implode(' ', array_slice($nameParts, 1)) : '-';
            }

            $contactData = [
                'Name' => $contactName,
                'FirstName' => $deal->customer_type === 'individual' ? $deal->first_name : $company_firstName,
                'LastName' => $deal->customer_type === 'individual' ? $deal->last_name : $company_lastName,
                'EmailAddress' => $deal->customer_type === 'individual' ? $deal->email : $deal->dealCustomerTypeDetail->company_email,
                'Phones' => [[
                    'PhoneType' => 'MOBILE',
                    'PhoneNumber' => $deal->customer_type === 'individual'
                        ? $deal->mobile
                        : $deal->dealCustomerTypeDetail->company_mobile,
                ]],
                'Addresses' => [[
                    'AddressLine1' => $deal->customer_type === 'individual'
                        ? $deal->address
                        : $deal->dealCustomerTypeDetail->company_address,
                    'City' => $deal->customer_type === 'individual'
                        ? $deal->city
                        : $deal->dealCustomerTypeDetail->company_city,
                    'Region' => $deal->customer_type === 'individual'
                        ? $deal->state
                        : $deal->dealCustomerTypeDetail->company_state,
                    'PostalCode' => $deal->customer_type === 'individual'
                        ? $deal->zipcode
                        : $deal->dealCustomerTypeDetail->company_zip_code,
                    'Country' => $deal->customer_type === 'individual'
                        ? $deal->country
                        : $deal->dealCustomerTypeDetail->company_country,
                    'AddressType' => 'STREET'
                ]],
                'ContactPersons' => []
            ];

            // Upsert supplier contact
            $contact = $xero->findSupplierByName($contactData['Name']);
            if (!$contact) {
                $contact = $xero->createSupplier($contactData);
            }
            $deal->supplier_xero_id = $contact['contact_id'];
            $deal->save();

            // Prepare line item for bill (use purchase account code and purchase price)
            $lineItems = [[
                'description' => ($deal->watchBrandDetail?->name ?? 'Unknown Brand') . ' - ' . ($deal->model_number ?? 'No Model'),
                'quantity' => 1,
                'unit_amount' => (float)$deal->purchase_price,
                'gst_type' => 'WOS',
                'account_code' => env('XERO_PURCHASE_ACCOUNT_CODE', '6-1200'),
            ]];
            $bill = $xero->createBill($deal->supplier_xero_id, $lineItems, null, null, $deal->purchase_invoice_number);
            $deal->xero_bill_id = $bill['bill_id'];
            $deal->save();
        } 
        catch (\Exception $ex) {
            \Log::error('Xero purchase bill creation failed', [
                'exception_message' => $ex->getMessage(),
                'deal_id' => $dealId,
                'contact_id' => isset($deal) ? $deal->supplier_xero_id ?? null : null,
                'trace' => $ex->getTraceAsString(),
            ]);
        }
    }

    public function store(Request $request)
    {
        DB::beginTransaction(); // Start a transaction
        try {
            // 1. Validate request
            $rules = [
                'customer_type' => 'required',
                'model_number' => 'required|string|max:255',
                'serial_number' => 'required|string|max:255|unique:deals,serial_number',
                'material_watch' => 'nullable|string|max:255',
                'condition' => 'nullable|string',
                'year' => 'nullable|integer|min:1900|max:' . date('Y'),
                'full_set' => 'nullable',
                'purchase_price' => 'nullable|numeric|min:0',
                'purchase_currency' => 'nullable|in:aud,usd',
                'brand_id' => 'nullable|integer',
                'sale_price' => 'nullable|numeric|min:0|gte:purchase_price',
                'gst_code' => 'nullable|string|max:20',
                'deal_supplier_status' => 'nullable|string|max:50',
                'purchase_invoice_number' => 'nullable|string|max:255',
                'purchase_invoice_date' => 'nullable|date',
                'images' => 'nullable|array|max:7',
                'images.*' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            ];
            $validatedData = $request->validate($rules);

            // 2. Create deal
            $payload = $request->all();
            $payload['review_status'] = 'under_review';
            $payload['reviewed_by'] = null;
            $payload['reviewed_at'] = null;
            $deal = Deal::create($payload);

         

            // 3. Handle company customer type
            if ($request->customer_type == 'company') {
                $invoiceFile = $this->handleInvoiceFileUpload($request, $deal);
                $deal->dealCustomerTypeDetail()->updateOrCreate(
                    ['deal_id' => $deal->id],
                    [
                        'customer_type' => $request->customer_type,
                        'company_name' => $request->company_name,
                        'company_email' => $request->company_email,
                        'company_mobile' => $request->company_mobile,
                        'invoice_file' => $invoiceFile,
                        'company_country' => $request->company_country,
                        'company_state' => $request->company_state,
                        'company_city' => $request->company_city,
                        'company_address' => $request->company_address,
                        'company_zip_code' => $request->company_zip_code,
                        'abn_number' => $request->abn_number,
                        'director_name' => $request->director_name,
                        'dealer_licence_number' => $request->dealer_licence_number,
                    ]
                );
                $deal->update($this->getEmptyIndividualFields());
            }

            // 4. Handle image uploads
            $this->handleDealImagesUpload($request, $deal);

            // 5. Checklist steps
            $this->handleChecklistSteps($request, $deal);

            DB::commit(); // Commit the transaction
            // Dispatch job to send deal created notification
            SendDealCreatedNotificationJob::dispatch($deal);
            // 6. Xero purchase invoice (supplier bill)
            if ($deal->dealBuyerDetail === null) {
                // Prepare Xero supplier bill logic inline (no need to call dealPurchaseInvoiceGenerate)
                try {
                    $xero = app(XeroService::class);
                    $contactName = $deal->customer_type === 'individual'
                        ? "{$deal->first_name} {$deal->last_name}"
                        : $deal->dealCustomerTypeDetail->company_name;
                    $company_firstName = '';
                    $company_lastName = '';
                    if ($deal->customer_type != 'individual') {
                        $companyName = $deal->dealCustomerTypeDetail->company_name;
                        $nameParts = explode(' ', trim($companyName));
                        $company_firstName = $nameParts[0] ?? '';
                        $company_lastName = count($nameParts) > 1 ? implode(' ', array_slice($nameParts, 1)) : '-';
                    }
                    $contactData = [
                        'Name' => $contactName,
                        'FirstName' => $deal->customer_type === 'individual' ? $deal->first_name : $company_firstName,
                        'LastName' => $deal->customer_type === 'individual' ? $deal->last_name : $company_lastName,
                        'EmailAddress' => $deal->customer_type === 'individual' ? $deal->email : $deal->dealCustomerTypeDetail->company_email,
                        'Phones' => [[
                            'PhoneType' => 'MOBILE',
                            'PhoneNumber' => $deal->customer_type === 'individual'
                                ? $deal->mobile
                                : $deal->dealCustomerTypeDetail->company_mobile,
                        ]],
                        'Addresses' => [[
                            'AddressLine1' => $deal->customer_type === 'individual'
                                ? $deal->address
                                : $deal->dealCustomerTypeDetail->company_address,
                            'City' => $deal->customer_type === 'individual'
                                ? $deal->city
                                : $deal->dealCustomerTypeDetail->company_city,
                            'Region' => $deal->customer_type === 'individual'
                                ? $deal->state
                                : $deal->dealCustomerTypeDetail->company_state,
                            'PostalCode' => $deal->customer_type === 'individual'
                                ? $deal->zipcode
                                : $deal->dealCustomerTypeDetail->company_zip_code,
                            'Country' => $deal->customer_type === 'individual'
                                ? $deal->country
                                : $deal->dealCustomerTypeDetail->company_country,
                            'AddressType' => 'STREET'
                        ]],
                        'ContactPersons' => []
                    ];
                    // Upsert supplier contact
                    $contact = $xero->findSupplierByName($contactData['Name']);
                    if (!$contact) {
                        $contact = $xero->createSupplier($contactData);
                    }
                    $deal->supplier_xero_id = $contact['contact_id'];
                    $deal->save();
                    // Prepare line item for bill (use purchase account code and purchase price)
                    $lineItems = [[
                        'description' =>
                            'Brand: ' . ($deal->watchBrandDetail?->name ?? 'Unknown Brand') .
                            ' / Model: ' . ($deal->model_number ?? 'No Model') .
                            ' / Serial: ' . ($deal->serial_number ?? 'No Serial') .
                            ' / Reference Number: ' . ($deal->material_watch ?? 'N/A') .
                            ' / Condition: ' . ($deal->condition ?? 'N/A') .
                            ' / Year: ' . ($deal->year ?? 'N/A') .
                            ' / Full set: ' . ($deal->full_set == '1' ? 'Yes' : 'No').
                            ' / Dial: ' . ($deal->dial ?? 'N/A'),
                        'quantity' => 1,
                        'unit_amount' => (float)$deal->purchase_price,
                        'gst_type' => 'WOS',
                        'account_code' => env('XERO_PURCHASE_ACCOUNT_CODE', '6-1200'),
                    ]];
                    // If xero_bill_id exists, update; else create
                    $bill = $xero->createBill($deal->supplier_xero_id, $lineItems, $deal->xero_bill_id, $deal->purchase_invoice_date, $deal->purchase_invoice_number);
                    $deal->xero_bill_id = $bill['bill_id'];
                    $deal->save();
                } catch (\Exception $ex) {
                    \Log::error('Xero purchase bill creation failed (store)', [
                        'exception_message' => $ex->getMessage(),
                        'deal_id' => $deal->id,
                        'contact_id' => $deal->supplier_xero_id ?? null,
                        'trace' => $ex->getTraceAsString(),
                    ]);
                }
            }

            return redirect()->route("admin.deals.edit", $deal->id)
                ->with('alert-success', 'Deal has been created successfully');
                
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating deal: ' . $e->getMessage());
            return redirect()->back()->with('alert-danger', 'An error occurred while creating the deal. Please try again.')->withInput();
        }
    }

    /**
     * Handle invoice file upload for company customer type
     */
    private function handleInvoiceFileUpload(Request $request, $deal)
    {
        if ($request->hasFile('invoice_file')) {
            return $request->file('invoice_file')->storeAs(
                'deal_invoices',
                Str::random(10) . '_' . uniqid() . '.' . $request->file('invoice_file')->getClientOriginalExtension(), 'public'
            );
        }
        return $deal->dealCustomerTypeDetail->invoice_file ?? "";
    }

    /**
     * Get array of empty individual fields for company customer type
     */
    private function getEmptyIndividualFields()
    {
        return [
            'first_name' => NULL,
            'last_name' => NULL,
            'email' => NULL,
            'mobile' => NULL,
            'country' => NULL,
            'state' => NULL,
            'city' => NULL,
            'address' => NULL,
            'zipcode' => NULL,
        ];
    }

    /**
     * Handle image uploads for deal
     */
    private function handleDealImagesUpload(Request $request, $deal)
    {
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $dealFolder = 'public/deals/' . $deal->id;
                $fullFolderPath = storage_path('app/' . $dealFolder);
                if (!File::exists($fullFolderPath)) {
                    File::makeDirectory($fullFolderPath, 0755, true);
                }
                $fileName = Str::random(40) . '.' . $image->getClientOriginalExtension();
                $image->storeAs('public/deals/' . $deal->id, $fileName);
                DealImage::create([
                    'deal_id' => $deal->id,
                    'document_name' => $fileName,
                ]);
            }
        }
    }

    /**
     * Handle checklist steps for deal
     */
    private function handleChecklistSteps(Request $request, $deal)
    {
        $steps = $request->input('step', []);
        $deal->checklist_steps = json_encode($steps);
        $deal->save();
    }

    public function show($id)
    {
        $deal = Deal::find($id);
        if (empty($deal)) {
            return redirect()->route('admin.deals.index')->with('alert-error', 'Deal not found');
        }
        $countries = $this->getCountires();
        $states = $this->getStates();
        $cities = $this->getCities($deal->state_id);

        return view('admin.deals.view', compact('deal', 'countries', 'states', 'cities'));
    }

    public function edit($id)
    {
        $deal = Deal::find($id);
        if (empty($deal)) {
            return redirect()->route('admin.deals.index')->with('alert-error', 'Deal not found');
        }

        $countries = $this->getCountires();
        $states = $this->getStates();
        $cities = $this->getCities($deal->state_id);
        $watchBrands = WatchBrand::orderBy('name', 'ASC')->pluck('name', 'id')->prepend("Select Brand", "");
        return view('admin.deals.edit', compact('deal', 'countries', 'states', 'cities', 'watchBrands'));
    }


    public function update(Request $request, $id)
    {
        //dd($request->all());
        DB::beginTransaction();
        $deal = Deal::findOrFail($id);
        $existingImagesCount = $deal->images->count();
        $deletedImagesCount = 0;
        if ($request->has('deleted_images') && !empty($request->input('deleted_images'))) {
            $deletedImages = explode(',', $request->input('deleted_images'));
            $deletedImagesCount = count($deletedImages);
        }
        $newImagesCount = 0;
        $remainingImages = max(0, 7 - ($existingImagesCount - $deletedImagesCount + $newImagesCount));
        try {
            $rules = [
                'customer_type' => 'required',
                'model_number' => 'required|string|max:255',
                'serial_number' => 'required|string|max:255|unique:deals,serial_number,' . $id,
                'material_watch' => 'nullable|string|max:255',
                'condition' => 'nullable|string',
                'year' => 'nullable|integer|min:1900|max:' . date('Y'),
                'full_set' => 'nullable',
                'purchase_price' => 'nullable|numeric|min:0',
                'sale_price' => 'nullable|numeric|min:0|gte:purchase_price',
                'delivery_cost' => 'nullable|numeric|min:0',
                'brand_id' => 'nullable|integer',
                'deal_status' => 'nullable|integer',
                'gst_code' => 'nullable|string|max:20',
                'deal_supplier_status' => 'nullable|string|max:50',
                'purchase_invoice_number' => 'nullable|string|max:255',
                'purchase_invoice_date' => 'nullable|date',
                'images' => 'nullable|array|max:' . $remainingImages,
                'images.*' => [
                    'image',
                    'mimes:jpg,jpeg,png,gif,webp',
                    'max:10240',
                ],
                'deleted_images' => 'nullable|string'
            ];
            if ($request->deal_status == 3 || $request->deal_status == 4) {
                $rules = array_merge($rules, [
                    'buyer_name' => 'required|string|max:255',
                    'buyer_email' => 'nullable|email|max:255',
                    'buyer_country' => 'required',
                    'buyer_state' => 'required',
                    'buyer_city' => 'required',
                    'buyer_address' => 'required|string|max:255',
                    'buyer_zipcode' => 'required|string|max:10',
                    'buyer_sale_price' => 'required|numeric|min:0',
                    'buyer_currency' => 'required|in:aud,usd',
                    'invoice_number' => 'nullable|string|max:255',
                    'invoice_date' => 'nullable|date',
                ]);
            }
            $validatedData = $request->validate($rules);
            $oldBuyerDetail = $deal->dealBuyerDetail ? $deal->dealBuyerDetail->toArray() : null;
            $deal->update($request->all());
            // Buyer details update
            if ($request->deal_status == 3 || $request->deal_status == 4) {
                
                $deal->dealBuyerDetail()->updateOrCreate(
                    ['deal_id' => $deal->id],
                    [
                        'buyer_name' => $request->buyer_name,
                        'buyer_email' => $request->filled('buyer_email') ? $request->buyer_email : null,
                        'buyer_country' => $request->buyer_country,
                        'buyer_state' => $request->buyer_state,
                        'buyer_city' => $request->buyer_city,
                        'buyer_address' => $request->buyer_address,
                        'buyer_zipcode' => $request->buyer_zipcode,
                            'buyer_sale_price' => $request->buyer_sale_price,
                            'buyer_currency' => $request->buyer_currency,
                        'invoice_number' => $request->invoice_number,
                        'invoice_date' => $request->invoice_date,
                        'gst_type' => $request->gst_type,
                        'buyer_loss_remark' => $request->buyer_loss_remark ?? null,
                    ]
                );

                // Compute sale in purchase currency and require remark on loss
                try {
                    $buyerRefreshed = $deal->fresh()->dealBuyerDetail;
                    $salePrice = (float)$request->buyer_sale_price;
                    $saleCurrency = $request->buyer_currency ?? ($buyerRefreshed->buyer_currency ?? 'aud');
                    $purchasePrice = (float)$deal->purchase_price;
                    $purchaseCurrency = $deal->purchase_currency ?? 'aud';
                    $rateUsed = 1.0;
                    if (strtolower($saleCurrency) === strtolower($purchaseCurrency)) {
                        $saleInPurchase = $salePrice;
                    } else {
                        $xero = app(XeroService::class);
                        $date = $buyerRefreshed->invoice_date ? new \DateTime($buyerRefreshed->invoice_date) : null;
                        $rateUsed = $xero->getExchangeRate($saleCurrency, $purchaseCurrency, $date);
                        $saleInPurchase = round($salePrice * $rateUsed, 2);
                    }
                    $profit = round($saleInPurchase - $purchasePrice, 2);
                    // Persist P/L and conversion on deal for auditability
                    $deal->sale_to_purchase_rate = $rateUsed;
                    $deal->sale_in_purchase_currency = $saleInPurchase;
                    $deal->profit_amount = $profit;
                    $deal->profit_currency = strtoupper($purchaseCurrency);
                    $deal->is_loss = $profit < 0;
                    $deal->save();

                    // Also persist conversion and P/L on the buyer detail for auditability
                    try {
                        if ($request->deal_status == 3 || $request->deal_status == 4) {
                            $deal->dealBuyerDetail()->update([
                                'buyer_sale_price_purchase_rate' => $rateUsed,
                                'buyer_sale_price_in_purchase_currency' => $saleInPurchase,
                                'buyer_profit_amount' => $profit,
                                'buyer_profit_currency' => strtoupper($purchaseCurrency),
                                'buyer_is_loss' => $profit < 0,
                                'buyer_loss_remark' => $request->buyer_loss_remark ?? $deal->dealBuyerDetail->buyer_loss_remark ?? null,
                            ]);
                        }
                    } catch (\Exception $e) {
                        Log::warning('Failed to persist buyer detail conversion/P&L', ['deal_id' => $deal->id, 'message' => $e->getMessage()]);
                    }

                    if ($profit < 0 && empty($request->buyer_loss_remark)) {
                        throw ValidationException::withMessages(['buyer_loss_remark' => 'Remark is required when sale results in a loss.']);
                    }
                } catch (ValidationException $ve) {
                    throw $ve;
                } catch (\Exception $ex) {
                    Log::warning('Failed to compute profit/loss in update', ['deal_id' => $deal->id, 'message' => $ex->getMessage()]);
                }
            } else {
                $deal->dealBuyerDetail()->delete();
            }
            if ($request->customer_type == 'company') {
                if ($request->hasFile('invoice_file')) {
                    $oldFile = $deal->dealCustomerTypeDetail->invoice_file ?? "";
                    if ($oldFile && Storage::disk('public')->exists($oldFile)) {
                        Storage::disk('public')->delete($oldFile);
                    }
                    $invoiceFile = $request->file('invoice_file')->storeAs(
                        'deal_invoices',
                        Str::random(10) . '_' . uniqid() . '.' . $request->file('invoice_file')->getClientOriginalExtension(), 'public'
                    );
                } else {
                    $invoiceFile = $deal->dealCustomerTypeDetail->invoice_file;
                }
                $deal->dealCustomerTypeDetail()->updateOrCreate(
                    ['deal_id' => $deal->id],
                    [
                        'customer_type' => $request->customer_type,
                        'company_name' => $request->company_name,
                        'company_email' => $request->company_email,
                        'company_mobile' => $request->company_mobile,
                        'invoice_file' => $invoiceFile,
                        'company_country' => $request->company_country,
                        'company_state' => $request->company_state,
                        'company_city' => $request->company_city,
                        'company_address' => $request->company_address,
                        'company_zip_code' => $request->company_zip_code,
                        'abn_number' => $request->abn_number,
                        'director_name' => $request->director_name,
                        'dealer_licence_number' => $request->dealer_licence_number,
                    ]
                );
                $updateIndividualArr = [
                    'first_name' => NULL,
                    'last_name' => NULL,
                    'email' => NULL,
                    'mobile' => NULL,
                    'country' => NULL,
                    'state' => NULL,
                    'city' => NULL,
                    'address' => NULL,
                    'zipcode' => NULL,
                ];
                $deal->update($updateIndividualArr);
            } else {
                $deal->dealCustomerTypeDetail()->delete();
            }
            // Handle new image uploads
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $image) {
                    $dealFolder = 'public/deals/' . $deal->id;
                    $fullFolderPath = storage_path('app/' . $dealFolder);
                    if (!File::exists($fullFolderPath)) {
                        File::makeDirectory($fullFolderPath, 0755, true);
                    }
                    $fileName = Str::random(40) . '.' . $image->getClientOriginalExtension();
                    $image->storeAs('public/deals/' . $deal->id, $fileName);
                    DealImage::create([
                        'deal_id' => $deal->id,
                        'document_name' => $fileName,
                    ]);
                }
            }
            // Handle image deletions
            if ($request->has('deleted_images')) {
                $deletedImages = explode(',', $request->input('deleted_images'));
                foreach ($deletedImages as $imageId) {
                    $image = DealImage::find($imageId);
                    if ($image) {
                        Storage::delete('public/deals/' . $deal->id . '/' . $image->document_name);
                        $image->delete();
                    }
                }
            }
            $steps = $request->input('step', []);
            $deal->checklist_steps = json_encode($steps);
            $deal->save();
            // --- Xero Sync for Customer/Sales Invoice ---
           
            DB::commit();
            // Dispatch background job to generate invoice after update
            //GenerateInvoiceFromDealJob::dispatch($deal->id);
            $this->generateInvoiceFromDeal($deal->id);
            // Dispatch job to send deal updated notification only if buyer exists
            if ($deal->dealBuyerDetail) {
                //SendDealUpdatedNotificationJob::dispatch($deal);
            }
            return redirect()->route('admin.deals.show', $deal->id)->with('alert-success', 'Deal has been updated successfully');
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating deal: ' . $e->getMessage());
            return redirect()->back()->with('alert-danger', $e->getMessage())->withInput();
        }
    }


    public function destroy($id)
    {
        $deal = Deal::find($id);
        $deal->delete();
        return redirect()->route('admin.deals.index')->with('alert-success', 'Deal has been deleted successfully');
    }


    public function downloadProductExcel(Request $request)
    {

        $input = $request->all();
        $deals = new Deal();

        if (isset($input['search_text']) && $input['search_text'] != '') {
            $search_text = $input['search_text'];
            $deals = $deals->where('model_number', 'LIKE', "%$search_text%");
        }
        $deals = $deals->orderBy('id', 'desc')->get();


        $time = date('dmY');
        $fileName = 'deals_' . $time . '.csv';
        $headers = [
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];

        $columns = [
            'ID',
            'Model Number',
            'Serial Number',
            'Reference Number',
            'Condition',
            'Year',
            'Full set or not',
            'Purchase Price',
            'Sale Price',
            'Country',
            'State',
            'City',
            'Address',
            'Zipcode',
            'Deal Status'

        ];

        $callback = function () use ($deals, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);
            if (!empty($deals)) {
                $countries = $this->getCountires();
                $states = $this->getStates();
                $deal_status = Config::get('constants.DEAL_STATUS');
                foreach ($deals as $deal) {
                    $cities = $this->getCities($deal->state_id);
                    fputcsv($file, [
                        $deal['id'],
                        $deal['model_number'],
                        $deal['serial_number'],
                        $deal['material_watch'],
                        $deal['condition'],
                        $deal['year'],
                        $deal['full_set'],
                        $deal['purchase_price'],
                        $deal['sale_price'],
                        (!empty($deal['country_id']) && isset($countries[$deal['country_id']])) ? $countries[$deal['country_id']] : '',
                        (!empty($deal['state_id']) && isset($states[$deal['state_id']])) ? $states[$deal['state_id']] : '',
                        (!empty($deal['city_id']) && isset($cities[$deal['city_id']])) ? $cities[$deal['city_id']] : '',
                        $deal['address'],
                        $deal['zipcode'],
                        $deal_status[$deal['deal_status']]
                    ]);
                }
            }
            fclose($file);
        };
        return response()->stream($callback, 200, $headers);
    }

    public function getCitiesByStateID($state_id)
    {
        $cities = City::where(['status' => '1', 'state_id' => $state_id])->orderBy('city_name', 'asc')->pluck('city_name', 'id');
        return response()->json($cities);
    }

    /**
     * AJAX: compute conversion and profit/loss for a deal given a sale price and currency.
     */
    public function computeProfitLoss(Request $request, $id)
    {
        $request->validate([
            'buyer_sale_price' => 'required|numeric',
            'buyer_currency' => 'required|string',
            'invoice_date' => 'nullable|date',
            'purchase_price' => 'nullable|numeric',
            'purchase_currency' => 'nullable|string',
        ]);

        $deal = Deal::findOrFail($id);
        $salePrice = (float)$request->buyer_sale_price;
        $saleCurrency = $request->buyer_currency;
        $purchasePrice = $request->filled('purchase_price') ? (float)$request->purchase_price : (float)$deal->purchase_price;
        $purchaseCurrency = $request->filled('purchase_currency') ? $request->purchase_currency : ($deal->purchase_currency ?? 'aud');
      
        try {
            if (strtolower($saleCurrency) === strtolower($purchaseCurrency)) {
                $saleInPurchase = $salePrice;
                $rateUsed = 1.0;
            } else {
                $xero = app(XeroService::class);
                $date = $request->invoice_date ? new \DateTime($request->invoice_date) : null;
                $rateUsed = $xero->getExchangeRate($saleCurrency, $purchaseCurrency, $date);
              
                $saleInPurchase = round($salePrice * $rateUsed, 2);
            }
            $profit = round($saleInPurchase - $purchasePrice, 2);
            return response()->json([
                'success' => true,
                'sale_in_purchase_currency' => $saleInPurchase,
                'purchase_currency' => strtoupper($purchaseCurrency),
                'rate' => $rateUsed,
                'profit' => $profit,
                'is_loss' => $profit < 0,
            ]);
        } catch (\Exception $ex) {
            Log::warning('computeProfitLoss failed', ['deal_id' => $id, 'error' => $ex->getMessage()]);
            $message = config('app.debug')
                ? $ex->getMessage()
                : 'Unable to load exchange rate from Xero. Check the Xero connection, that the foreign currency has a real rate (not 1:1), or an AUTHORISED/PAID invoice in that currency.';

            return response()->json(['success' => false, 'message' => $message], 422);
        }
    }

    public function getAccountDeal($id)
    {
        $deal = Deal::find($id);
        if (empty($deal)) {
            return redirect()->route('admin.deals.index')->with('alert-error', 'Deal not found');
        }
        return view('admin.deals.account_view', compact('deal',));
    }

    public function brandAutocomplete(Request $request)
    {
        $search = $request->get('term');

        $brands = WatchBrand::where('name', 'LIKE', "%{$search}%")
            ->orderBy('name')
            ->limit(10)
            ->get();

        return response()->json(
            $brands->map(function ($brand) {
                return [
                    'label' => $brand->name, // What the user sees
                    'value' => $brand->id     // What we store in hidden input
                ];
            })
        );
    }

    public function getBrandName(Request $request)
    {
        $brand = WatchBrand::find($request->id);

        if ($brand) {
            return response()->json(['name' => $brand->name]);
        }

        return response()->json(['name' => ''], 404);
    }

    public function markReviewed($id)
    {
        $deal = Deal::findOrFail($id);

        if (($deal->review_status ?? 'under_review') === 'reviewed') {
            return redirect()
                ->route('admin.deals.show', $deal->id)
                ->with('alert-info', 'Deal is already marked as reviewed.');
        }

        $deal->review_status = 'reviewed';
        $deal->reviewed_by = auth()->guard('admin')->id();
        $deal->reviewed_at = now();
        $deal->save();

        SendDealReviewedNotificationJob::dispatch($deal);

        return redirect()
            ->route('admin.deals.show', $deal->id)
            ->with('alert-success', 'Deal has been marked as reviewed and funding team notified.');
    }

    public function loadApproveDealModal($dealId, Request $request)
    {

        // Start the transaction
        DB::beginTransaction();

        try {
            // Find the history record by ID
            $dealDetails = Deal::findOrFail($dealId);

            // If the request is GET, return the modal view
            if ($request->isMethod('get')) {
                return view("admin.deals.model-approve-deal", compact('dealDetails'));
            }

            // If the request is POST, handle the form submission
            if ($request->isMethod('post')) {

                $validationRules = [
                    'note' => 'required|string|max:1000',
                ];
                $request->validate($validationRules);

                $dealDetails->deal_status = '2';
                $dealDetails->deal_approve_status = '1';
                $dealDetails->deal_approve_at = now();
                $dealDetails->deal_approve_notes = $request->input('note');
                $dealDetails->approved_by = auth()->guard('admin')->id();
                $dealDetails->save();

                DB::commit();

                // Return a JSON response indicating success
                return response()->json([
                    'success' => true,
                    'message' => 'Deal Approved successfully!',
                ]);
            }

        } catch (ValidationException $e) {
            // If validation fails, catch the exception and return errors
            DB::rollBack();  // Rollback the transaction if validation fails
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $e->errors(), // Provide the validation errors
            ], 422); // Send back validation errors with a 422 status
        } catch (\Exception $e) {
            // If any other exception occurs, rollback the transaction
            DB::rollBack();

            // Log the error (optional, for debugging purposes)
            Log::error('Error cancelling order: ' . $e->getMessage());

            // Return a JSON response with error details
            return response()->json([
                'success' => false,
                'message' => 'Error cancelling order, please try again later.',
            ]);
        }
    }

    public function downloadInvoice($deal_id, $currency)
    {
        $dealDetails = Deal::findOrFail($deal_id);
        $companyDetail = CompanyDetail::first() ?? new CompanyDetail();

        $buyerDetail = $dealDetails->dealBuyerDetail;

        // Use the Xero invoice number if available, otherwise fall back to computed number
        $invoiceNo = ($buyerDetail && !empty($buyerDetail->invoice_number))
            ? $buyerDetail->invoice_number
            : (100000 + $deal_id);

        $customerName = $buyerDetail
            ? $buyerDetail->buyer_name
            : ($dealDetails->customer_type == 'individual'
                ? $dealDetails->first_name . ' ' . $dealDetails->last_name
                : optional($dealDetails->dealCustomerTypeDetail)->company_name ?? 'N/A');

        $customerAddress = $buyerDetail
            ? $buyerDetail->buyer_address
            : ($dealDetails->customer_type == 'individual'
                ? $dealDetails->address
                : optional($dealDetails->dealCustomerTypeDetail)->company_address ?? '');

        $customerZipcode = $buyerDetail
            ? $buyerDetail->buyer_zipcode
            : ($dealDetails->customer_type == 'individual'
                ? $dealDetails->zipcode
                : optional($dealDetails->dealCustomerTypeDetail)->company_zip_code ?? '');

        $customerCity = $buyerDetail
            ? $buyerDetail->buyer_city
            : ($dealDetails->customer_type == 'individual'
                ? $dealDetails->city
                : optional($dealDetails->dealCustomerTypeDetail)->company_city ?? '');

        $customerState = $buyerDetail
            ? $buyerDetail->buyer_state
            : ($dealDetails->customer_type == 'individual'
                ? $dealDetails->state
                : optional($dealDetails->dealCustomerTypeDetail)->company_state ?? '');
        // Determine base price (use buyer sale price when available)
        $basePrice = (float)(optional($dealDetails->dealBuyerDetail)->buyer_sale_price ?? $dealDetails->sale_price ?? 0);

        // Determine GST percent from buyer detail gst_type if set, otherwise 0
        $gstPercent = 0;
        if (!empty(optional($dealDetails->dealBuyerDetail)->gst_type)) {
            $gstMap = Config::get('constants.GST_TYPE_PERCENT', []);
            $gstType = $dealDetails->dealBuyerDetail->gst_type;
            $gstPercent = isset($gstMap[$gstType]) ? (float)$gstMap[$gstType] : (float)$gstType;
        }

        // Price entered is GST-inclusive (same basis as Xero LineAmountTypes::INCLUSIVE).
        // GST component = total × rate / (100 + rate); net (subtotal) = total − GST.
        $total = $basePrice;
        if ($gstPercent > 0) {
            $gstAmount = round($total * $gstPercent / (100 + $gstPercent), 2);
            $subtotal = round($total - $gstAmount, 2);
        } else {
            $gstAmount = 0;
            $subtotal = round($total, 2);
        }

        $invoiceDate = optional($dealDetails->dealBuyerDetail)->invoice_date
            ? Carbon::parse($dealDetails->dealBuyerDetail->invoice_date)->format('d/m/Y')
            : date('d/m/Y');

        $deal = (object)[
            'invoice_no' => $invoiceNo,
            'date' => $invoiceDate,
            'customer_name' => $customerName,
            'customer_address' => $customerAddress,
            'customer_zipcode' => $customerZipcode,
            'customer_city' => $customerCity,
            'customer_state' => $customerState,
            'item_details' => (object)[
                'brand' => $dealDetails->watchBrandDetail->name ?? "N/A",
                'model' => $dealDetails->model_number,
                'reference' => $dealDetails->material_watch ?? 'N/A',
                'serial' => $dealDetails->serial_number,
                'year' => $dealDetails->year,
                'condition' => $dealDetails->condition,
                'dial' => $dealDetails->dial,
                'complete_set' => $dealDetails->full_set == '1' ? 'Yes' : 'No',
            ],
            'subtotal' => number_format($subtotal, 2, '.', ','),
            'gst_percent' => $gstPercent, // numeric percent, view can append '%'
            'gst_amount' => number_format($gstAmount, 2, '.', ','),
            'total' => number_format($total, 2, '.', ','),
            'amount_due' => number_format($total, 2, '.', ','),
        ];

        // Choose the appropriate view
        $view = $currency === 'aud' ? 'admin.deals.invoice-aud' : 'admin.deals.invoice-usd';
        $fileSuffix = strtoupper($currency);

        $html = view($view, compact('deal', 'companyDetail'))->render();

        $pdf = new CustomPdf(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        $pdf->invoiceNo = $deal->invoice_no;
        $pdf->amountDue = $deal->amount_due;
        $pdf->currency = $currency;

        $pdf->SetCreator('Zman Watches');
        $pdf->SetAuthor('Zman Watches');
        $pdf->SetTitle($deal->customer_name . ' Invoice #' . $deal->invoice_no);
        $pdf->SetSubject('Invoice');

        $pdf->SetMargins(10, 10, 10);
        $pdf->SetHeaderMargin(15);
        $pdf->SetFooterMargin(15);
        $pdf->SetAutoPageBreak(TRUE, 15);
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(true);

        $pdf->AddPage();
        $pdf->writeHTML($html, true, false, true, false, '');

        return response(
            $pdf->Output($deal->customer_name . ' Invoice_' . $fileSuffix . ' #' . $deal->invoice_no . '.pdf', 'D')
        )->header('Content-Type', 'application/pdf');
    }

    /**
     * Queue invoice email: SendInvoiceEmailToCustomerJob sends invoice_to_customer + PDF only to sales notification addresses (sales_invoice_created). Manual action only.
     */
    public function emailInvoiceToCustomer(Request $request, Deal $deal)
    {
        if (!$deal->dealBuyerDetail) {
            return redirect()
                ->route('admin.deals.show', $deal->id)
                ->with('alert-error', 'Buyer details are required before emailing an invoice.');
        }

        SendInvoiceEmailToCustomerJob::dispatch($deal);

        return redirect()
            ->route('admin.deals.show', $deal->id)
            ->with('alert-success', 'Invoice email has been queued.');
    }

    public function downloadShippingInvoiceTemplate($dealId)
    {
        $deal = Deal::with(['watchBrandDetail', 'dealBuyerDetail'])->findOrFail($dealId);
        $service = app(InvoicePdfService::class);
        $pdf = $service->generateShippingInvoicePdf($deal);

        return response($pdf['content'], 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $pdf['filename'] . '"',
        ]);
    }

    /**
     * Show all sold deals with checkboxes for bulk shipping invoice download
     */
    public function shippingDeals(Request $request)
    {
        $query = Deal::where('deal_status', 3)
            ->with(['watchBrandDetail', 'dealBuyerDetail']);

        if ($request->filled('search_text')) {
            $s = $request->search_text;
            $query->where(function ($q) use ($s) {
                $q->where('model_number', 'like', "%{$s}%")
                  ->orWhere('serial_number', 'like', "%{$s}%");
            });
        }

        if ($request->filled('brand_id')) {
            $query->where('brand_id', $request->brand_id);
        }

        if ($request->filled('date_from')) {
            $query->where('updated_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('updated_at', '<=', $request->date_to . ' 23:59:59');
        }

        $deals = $query->orderBy('updated_at', 'desc')->paginate(20)->withQueryString();
        $brands = WatchBrand::orderBy('name')->pluck('name', 'id');

        return view('admin.deals.shipping-deals', compact('deals', 'brands'));
    }

    /**
     * Bulk download shipping invoices for selected deals as a ZIP file
     */
    public function bulkDownloadShippingInvoices(Request $request)
    {
        $request->validate([
            'deal_ids' => 'required|array|min:1',
            'deal_ids.*' => 'integer|exists:deals,id',
        ]);

        $dealIds = $request->input('deal_ids');
        $deals = Deal::whereIn('id', $dealIds)->where('deal_status', 3)->get();

        if ($deals->isEmpty()) {
            return back()->with('alert-error', 'No valid sold deals selected.');
        }

        $deals->load(['watchBrandDetail', 'dealBuyerDetail']);
        $service = app(InvoicePdfService::class);
        if ($deals->count() === 1) {
            $pdf = $service->generateShippingInvoicePdf($deals->first());
        } else {
            $pdf = $service->generateBulkShippingInvoicePdf($deals);
        }

        return response($pdf['content'], 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $pdf['filename'] . '"',
        ]);
    }


    public function generateInvoiceFromDealMYOB($dealId)
    {
        try {
            // 1. Get Deal with customer and item
            $dealDetails = Deal::findOrFail($dealId);
            $invoiceNo = 100000 + $dealId;

            $customerName = $dealDetails->customer_type == 'individual'
                ? $dealDetails->first_name . ' ' . $dealDetails->last_name
                : $dealDetails->dealCustomerTypeDetail->company_name;


            $customerAddress = $dealDetails->customer_type == 'individual'
                ? $dealDetails->address
                : $dealDetails->dealCustomerTypeDetail->company_address;

            $customerZipcode = $dealDetails->customer_type == 'individual'
                ? $dealDetails->zipcode
                : $dealDetails->dealCustomerTypeDetail->company_zip_code;


            $state = $dealDetails->customer_type == 'individual' ? $dealDetails->state : $dealDetails->dealCustomerTypeDetail->company_state;
            //$cities = $this->getCities($state);

            $city = $dealDetails->customer_type == 'individual' ? $dealDetails->city : $dealDetails->dealCustomerTypeDetail->company_city;
            $country = $dealDetails->customer_type == 'individual' ? $dealDetails->country : $dealDetails->dealCustomerTypeDetail->company_country;
            $customerCity = $city;
            $customerState = $state;
            $deal = [
                'invoice_no' => $invoiceNo,
                'date' => date('d/m/Y'),
                'customer_name' => $customerName,
                'customer_address' => $customerAddress,
                'customer_zipcode' => $customerZipcode,
                'customer_city' => $customerCity,
                'customer_state' => $customerState,
                'item_details' => [
                    'brand' => $dealDetails->watchBrandDetail->name ?? "N/A",
                    'model' => $dealDetails->model_number,
                    'reference' => '226627',
                    'serial' => $dealDetails->serial_number,
                    'year' => $dealDetails->year,
                    'condition' => $dealDetails->condition,
                    'complete_set' => $dealDetails->full_set == '1' ? 'Yes' : 'No',
                ],
                'subtotal' => number_format($dealDetails->sale_price, 2, '.', ','),
                'total' => number_format($dealDetails->sale_price, 2, '.', ','),
                'amount_due' => number_format($dealDetails->sale_price, 2, '.', ','),
            ];


            $displayIdPrefix = $dealDetails->customer_type === 'individual' ? 'IND' : 'ORG';
            $customerNameSlug = strtoupper(substr(preg_replace('/[^A-Za-z]/', '', $deal['customer_name']), 0, 3));
            $formattedId = str_pad($dealDetails->id, 4, '0', STR_PAD_LEFT);
            $displayId = "{$displayIdPrefix}-{$customerNameSlug}{$formattedId}";
            // ✅ Create customer only if UID is not set
            if (!$dealDetails->customer_uid) {
                // 1. Create customer
                $customerData = [
                    'IsIndividual' => $dealDetails->customer_type == 'individual',
                    'DisplayID' => $displayId,
                    'SellingDetails' => [
                        'TaxCode' => ['UID' => env('MYOB_TAX_CODE_UID')],
                        'FreightTaxCode' => ['UID' => env('MYOB_TAX_CODE_UID')]
                    ]
                ];
                if ($dealDetails->customer_type == 'individual') {
                    $customerData['FirstName'] = $dealDetails->first_name;
                    $customerData['LastName'] = $dealDetails->last_name;
                } else {
                    $customerData['CompanyName'] = $deal['customer_name'];
                }
                $this->myob->createCustomer($customerData);

                // 2. Find and store UID
                $foundCustomer = $this->myob->findCustomerByDisplayId($displayId);
                $customerUID = $foundCustomer['UID'] ?? null;

                if (!$customerUID) {
                    throw new \Exception("Customer UID not found after creation.");
                }

                $dealDetails->customer_uid = $customerUID;
                $dealDetails->save();
            } else {
                $customerUID = $dealDetails->customer_uid;
            }


            // 3. Create Item in MYOB
// 3. Create Item in MYOB only if not already linked
            if (!$dealDetails->myob_item_uid) {
                $itemNumber = 'ITM' . str_pad($dealDetails->id, 6, '0', STR_PAD_LEFT);

                $itemData = [
                    'Number' => $itemNumber,
                    'Name' => $deal['item_details']['model'] ?? "",
                    'IsActive' => true,
                    'Description' => $deal['item_details']['model'] ?? "",
                    'IncomeAccount' => [
                        'UID' => env('MYOB_INCOME_ACCOUNT_UID') // Must exist in MYOB
                    ],
                    'BuyingDetails' => [
                        'TaxCode' => [
                            'UID' => env('MYOB_TAX_CODE_UID')
                        ]
                    ],
                    'SellingDetails' => [
                        'TaxCode' => [
                            'UID' => env('MYOB_TAX_CODE_UID')
                        ]
                    ]
                ];

                $this->myob->createItem($itemData);

                // Fetch item by Number (or use response if createItem returns UID)
                $foundItem = $this->myob->findItemByNumber($itemNumber); // You need to implement this in your MyobService
                $itemUID = $foundItem['UID'] ?? null;

                if (!$itemUID) {
                    throw new \Exception("Item UID not found after creation.");
                }

                $dealDetails->myob_item_uid = $itemUID;
                $dealDetails->save();
            } else {
                $itemUID = $dealDetails->myob_item_uid;
            }


            // 4. Create Invoice in MYOB if not already created
            if (!$dealDetails->myob_invoice_uid) {
                $invoiceData = [
                    "Number" => $deal['invoice_no'] ?? 'INV-' . now()->format('YmdHis'),
                    "Date" => Carbon::now()->toDateString() . "T00:00:00",
                    "SupplierInvoiceNumber" => null,
                    "Customer" => [
                        "UID" => $customerUID
                    ],
                    "ShipToAddress" => $deal['customer_address'] ?? 'Default Address',
                    "Terms" => [
                        "PaymentIsDue" => "DayOfMonthAfterEOM",
                        "DiscountDate" => 1,
                        "BalanceDueDate" => 30,
                        "DiscountForEarlyPayment" => 0,
                        "MonthlyChargeForLatePayment" => 0,
                        "DiscountExpiryDate" => "2018-03-01T00:00:00",
                        "Discount" => 0,
                        "DueDate" => "2018-03-30T00:00:00"
                    ],
                    "IsTaxInclusive" => true,
                    "IsReportable" => false,
                    "Lines" => [
                        [
                            "Type" => "Transaction",
                            "Description" => $deal['item_details']['model'] ?? 'No Description',
                            "Account" => [
                                "UID" => "3501c849-4c56-4f05-804d-555e25ca05e5"
                            ],
                            "Total" => (float)($deal['total'] ?? 0),
                            "Job" => null,
                            "TaxCode" => [
                                "UID" => $deal['MYOB_TAX_CODE_UID'] ?? env('MYOB_TAX_CODE_UID')
                            ]
                        ]
                    ],
                    "Subtotal" => (float)($deal['total'] ?? 0),
                    "Freight" => 0,
                    "FreightTaxCode" => [
                        "UID" => env('MYOB_TAX_CODE_UID')
                    ],
                    "TotalTax" => 0, // Optional override
                    "TotalAmount" => (float)($deal['total'] ?? 0),
                    "Category" => null,
                    "Comment" => "",
                    "ShippingMethod" => null,
                    "PromisedDate" => Carbon::now()->toDateString() . "T00:00:00",
                    "JournalMemo" => "Laravel dynamic invoice",
                    "BillDeliveryStatus" => "Print",
                    "AppliedToDate" => 0,
                    "BalanceDueAmount" => (float)($deal['total'] ?? 0),
                    "Status" => "Open",
                    "LastPaymentDate" => null,
                    "Order" => null,
                    "ForeignCurrency" => null
                ];


                // Try to create invoice
                $createdInvoice = $this->myob->createInvoice($invoiceData);
                $invoiceUID = $createdInvoice['UID'] ?? null;

                // If UID not returned, attempt to fetch it by invoice number
                if (!$invoiceUID) {
                    $fetchedInvoice = $this->myob->findInvoiceByNumber($deal['invoice_no']);
                    $invoiceUID = $fetchedInvoice['UID'] ?? null;
                }

                if (!$invoiceUID) {
                    throw new \Exception("Invoice UID could not be retrieved after creation.");
                }

                // Save to deal
                $dealDetails->myob_invoice_uid = $invoiceUID;
                $dealDetails->save();
            } else {
                $invoiceUID = $dealDetails->myob_invoice_uid;
            }

            return redirect()->route('admin.deals.show', $dealDetails->id)->with('alert-success', 'Deal Invoice created successfully');

            /*return response()->json([
                'message' => 'Invoice created successfully.',
            ]);*/

        } catch (\Exception $e) {
            /* report($e);
             return response()->json([
                 'error' => true,
                 'message' => 'Invoice generation failed: ' . $e->getMessage(),
             ], 500);*/

            return redirect()->route('admin.deals.show', $dealDetails->id)->with('alert-error', 'Invoice generation failed: ' . $e->getMessage());
        }
    }


    public function generateInvoiceFromDeal($dealId)
    {
       
        try {
            $deal = Deal::findOrFail($dealId);
            $invoiceNo = 100000 + $dealId;
            $xero = app(XeroService::class);
            // Determine contact type: if deal has buyer details, treat as customer (sales invoice), else supplier (purchase bill)
            $hasBuyerDetails = $deal->dealBuyerDetail !== null;
            $contactType = $hasBuyerDetails ? 'customer' : 'supplier';
            // Prepare contact data
            if ($hasBuyerDetails && $deal->dealBuyerDetail) {
                $contactName = $deal->dealBuyerDetail->buyer_name;
                $buyerFirstName = $contactName;
                $buyerLastName = '';
                if (strpos($contactName, ' ') !== false) {
                    $parts = explode(' ', $contactName, 2);
                    $buyerFirstName = $parts[0];
                    $buyerLastName = $parts[1];
                }
                $contactData = [
                    'Name' => $contactName,
                    'FirstName' => $buyerFirstName,
                    'LastName' => $buyerLastName,
                    'EmailAddress' => '',
                    'Phones' => [[
                        'PhoneType' => 'MOBILE',
                        'PhoneNumber' => '',
                    ]],
                    'Addresses' => [[
                        'AddressLine1' => $deal->dealBuyerDetail->buyer_address,
                        'City' => $deal->dealBuyerDetail->buyer_city,
                        'Region' => $deal->dealBuyerDetail->buyer_state,
                        'PostalCode' => $deal->dealBuyerDetail->buyer_zipcode,
                        'Country' => $deal->dealBuyerDetail->buyer_country,
                        'AddressType' => 'STREET'
                    ]],
                    'ContactPersons' => []
                ];
                //dd($contactData);
            } 
            else {
                $contactName = $deal->customer_type === 'individual'
                    ? "{$deal->first_name} {$deal->last_name}"
                    : $deal->dealCustomerTypeDetail->company_name;
                $company_firstName = '';
                $company_lastName = '';
                if ($deal->customer_type != 'individual') {
                    $companyName = $deal->dealCustomerTypeDetail->company_name;
                    $nameParts = explode(' ', trim($companyName));
                    $company_firstName = $nameParts[0] ?? '';
                    $company_lastName = count($nameParts) > 1 ? implode(' ', array_slice($nameParts, 1)) : '-';
                }
                $contactData = [
                    'Name' => $contactName,
                    'FirstName' => $deal->customer_type === 'individual' ? $deal->first_name : $company_firstName,
                    'LastName' => $deal->customer_type === 'individual' ? $deal->last_name : $company_lastName,
                    'EmailAddress' => $deal->customer_type === 'individual' ? $deal->email : $deal->dealCustomerTypeDetail->company_email,
                    'Phones' => [[
                        'PhoneType' => 'MOBILE',
                        'PhoneNumber' => $deal->customer_type === 'individual'
                            ? $deal->mobile
                            : $deal->dealCustomerTypeDetail->company_mobile,
                    ]],
                    'Addresses' => [[
                        'AddressLine1' => $deal->customer_type === 'individual'
                            ? $deal->address
                            : $deal->dealCustomerTypeDetail->company_address,
                        'City' => $deal->customer_type === 'individual'
                            ? $deal->city
                            : $deal->dealCustomerTypeDetail->company_city,
                        'Region' => $deal->customer_type === 'individual'
                            ? $deal->state
                            : $deal->dealCustomerTypeDetail->company_state,
                        'PostalCode' => $deal->customer_type === 'individual'
                            ? $deal->zipcode
                            : $deal->dealCustomerTypeDetail->company_zip_code,
                        'Country' => $deal->customer_type === 'individual'
                            ? $deal->country
                            : $deal->dealCustomerTypeDetail->company_country,
                        'AddressType' => 'STREET'
                    ]],
                    'ContactPersons' => []
                ];
            }
            // --- Xero Sync Logic ---
            if ($contactType === 'supplier') {
                
                $contact = $xero->findSupplierByName($contactData['Name']);
                if (!$contact) {
                    $contact = $xero->createSupplier($contactData);
                }
                $deal->supplier_xero_id = $contact['contact_id'];
                $deal->save();
                $lineItems = [[
                     'description' =>
                            'Brand: ' . ($deal->watchBrandDetail?->name ?? 'Unknown Brand') .
                            ' / Model: ' . ($deal->model_number ?? 'No Model') .
                            ' / Serial: ' . ($deal->serial_number ?? 'No Serial') .
                            ' / Reference Number: ' . ($deal->material_watch ?? 'N/A') .
                            ' / Condition: ' . ($deal->condition ?? 'N/A') .
                            ' / Year: ' . ($deal->year ?? 'N/A') .
                            ' / Full set: ' . ($deal->full_set == '1' ? 'Yes' : 'No').
                            ' / Dial: ' . ($deal->dial ?? 'N/A'),
                    'quantity' => 1,
                    'unit_amount' => (float)$deal->purchase_price,
                    'gst_type' => 'WOS', // Assuming purchase, adjust if needed
                    'account_code' => env('XERO_PURCHASE_ACCOUNT_CODE', '6-1200'),
                ]];
               
                $bill = $xero->createBill(
                    $deal->supplier_xero_id,
                    $lineItems,
                    $deal->xero_bill_id,
                    $deal->purchase_invoice_date,
                    $deal->purchase_invoice_number
                );
                $deal->xero_bill_id = $bill['bill_id'];
                $deal->save();
            } 
            else if ($contactType === 'customer') {
                $contact = $xero->createCustomer($contactData);
                $deal->customer_xero_id = $contact['contact_id'];
                $deal->save();
                $gstType = $deal->dealBuyerDetail->gst_type ?? null;
                $lineItems = [[
                    'description' =>
                            'Brand: ' . ($deal->watchBrandDetail?->name ?? 'Unknown Brand') .
                            ' / Model: ' . ($deal->model_number ?? 'No Model') .
                            ' / Serial: ' . ($deal->serial_number ?? 'No Serial') .
                            ' / Reference Number: ' . ($deal->material_watch ?? 'N/A') .
                            ' / Condition: ' . ($deal->condition ?? 'N/A') .
                            ' / Year: ' . ($deal->year ?? 'N/A') .
                            ' / Full set: ' . ($deal->full_set == '1' ? 'Yes' : 'No').
                            ' / Dial: ' . ($deal->dial ?? 'N/A'),
                    'quantity' => 1,
                    'unit_amount' => (float)$deal->dealBuyerDetail->buyer_sale_price,
                    'account_code' => env('XERO_SALES_ACCOUNT_CODE', '6-1200'),
                    'gst_type' => $gstType,
                ]];
                $invoiceMeta = [];
                if (!empty($deal->dealBuyerDetail->invoice_date)) {
                    $invoiceMeta['invoice_date'] = Carbon::parse($deal->dealBuyerDetail->invoice_date)->toDateString();
                    $invNo = trim((string)($deal->dealBuyerDetail->invoice_number ?? ''));
                    if ($invNo !== '') {
                        $invoiceMeta['invoice_number'] = $invNo;
                    }
                }
                try {
                    $invoice = $xero->createOrUpdateInvoice($deal->customer_xero_id, $lineItems, $deal->xero_invoice_id, $invoiceMeta);
                  
                    \Log::info('Xero sales invoice createOrUpdate response', [
                        'invoice' => $invoice,
                        'deal_id' => $deal->id,
                        'contact_id' => $deal->customer_xero_id,
                        'line_items' => $lineItems
                    ]);
                    $deal->xero_invoice_id = $invoice['invoice_id'] ?? null;
                    // Save invoice number/date into buyer details (and create if missing)
                    $invoiceNumber = $invoice['invoice_number'] ?? null;
                    $invoiceDate = $invoice['date'] ?? null;
                    if ($deal->dealBuyerDetail) {
                        $deal->dealBuyerDetail()->updateOrCreate(
                            ['deal_id' => $deal->id],
                            [
                                'invoice_number' => $invoiceNumber,
                                'invoice_date' => $invoiceDate
                    
                            ]
                        );
                    } else {
                        // Create buyer detail record with invoice fields if buyer detail missing
                        $deal->dealBuyerDetail()->create([
                            'deal_id' => $deal->id,
                            'invoice_number' => $invoiceNumber,
                            'invoice_date' => $invoiceDate,
                        ]);
                    }
                    $deal->save();
                    if($deal->deal_status == 3 && $deal->dealBuyerDetail) {
                      SendDealUpdatedNotificationJob::dispatch($deal);
                    }                   
                    
                } catch (\Exception $ex) {
                    \Log::error('Xero sales invoice createOrUpdate failed', [
                        'exception_message' => $ex->getMessage(),
                        'deal_id' => $deal->id,
                        'contact_id' => $deal->customer_xero_id,
                        'line_items' => $lineItems,
                        'trace' => $ex->getTraceAsString(),
                    ]);
                    throw $ex;
                }
            }
            return redirect()
                ->route('admin.deals.show', $deal->id)
                ->with('alert-success', $contactType === 'customer' ? 'Xero Invoice created/updated successfully' : 'Xero Bill created successfully');
        } catch (\Exception $e) {
            Log::error('Xero Invoice/Bill generation error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return redirect()
                ->route('admin.deals.show', $deal->id)
                ->with('alert-error', 'Xero Invoice/Bill generation failed: ' . $e->getMessage());
        }
    }




    public function generateShippingFromDeal($dealId)
    {
        try {

            $deal = Deal::findOrFail($dealId);
            $invoiceNo = 100000 + $dealId;

            $customerName = $deal->customer_type === 'individual'
                ? "{$deal->first_name} {$deal->last_name}"
                : $deal->dealCustomerTypeDetail->company_name;

            $company_firstName = '';
            $company_lastName = '';
            if($deal->customer_type!='individual'){
                $companyName = $deal->dealCustomerTypeDetail->company_name;
                $nameParts = explode(' ', trim($companyName));
                $company_firstName = $nameParts[0] ?? '';
                $company_lastName = count($nameParts) > 1 ? implode(' ', array_slice($nameParts, 1)) : '-';
            }
            $customerData = [
                'Name' => $customerName,
                'FirstName' => $deal->customer_type === 'individual' ? $deal->first_name : $company_firstName,
                'LastName' => $deal->customer_type === 'individual' ? $deal->last_name : $company_lastName,
                'EmailAddress' => $deal->customer_type === 'individual' ? $deal->email : $deal->dealCustomerTypeDetail->company_email,
                'Phones' => [[
                    'PhoneType' => 'MOBILE',
                    'PhoneNumber' => $deal->customer_type === 'individual'
                        ? $deal->mobile
                        : $deal->dealCustomerTypeDetail->company_mobile,
                ]],
                'Addresses' => [[
                    'AddressLine1' => $deal->customer_type === 'individual'
                        ? $deal->address
                        : $deal->dealCustomerTypeDetail->company_address,
                    'City' => $deal->customer_type === 'individual'
                        ? $deal->city
                        : $deal->dealCustomerTypeDetail->company_city,
                    'Region' => $deal->customer_type === 'individual'
                        ? $deal->state
                        : $deal->dealCustomerTypeDetail->company_state,
                    'PostalCode' => $deal->customer_type === 'individual'
                        ? $deal->zipcode
                        : $deal->dealCustomerTypeDetail->company_zip_code,
                    'Country' => $deal->customer_type === 'individual'
                        ? $deal->country
                        : $deal->dealCustomerTypeDetail->company_country,
                    'AddressType' => 'STREET'
                ]]
            ];


            $ss = app(ShipStationService::class);
            //$ss->getCarriers();

            // Step 1: Create order with sample data
            $orderPayload = [
                "orderNumber" => $invoiceNo,
                "orderDate" => now()->toISOString(),
                "orderStatus" => "awaiting_shipment",
                "customerEmail" => filled(optional($deal->dealBuyerDetail)->buyer_email)
                    ? $deal->dealBuyerDetail->buyer_email
                    : ($customerData['EmailAddress'] ?: 'customer@example.com'),
                // Required billing details
                "billTo" => [
                    "name"       => trim($customerData['FirstName'] . ' ' . $customerData['LastName']),
                    "company"    => $deal->customer_type === 'individual' ? '' : $customerData['Name'],
                    "street1"    => $customerData['Addresses'][0]['AddressLine1'],
                    "city"       => $customerData['Addresses'][0]['City'],
                    "state"      => $customerData['Addresses'][0]['Region'],
                    "postalCode" => $customerData['Addresses'][0]['PostalCode'],
                    "country"    => $customerData['Addresses'][0]['Country'],
                    "phone"      => $customerData['Phones'][0]['PhoneNumber'],
                ],

                "shipTo" => [
                    "name" => $deal->dealBuyerDetail->buyer_name ?? "N/A",
                    "street1" => $deal->dealBuyerDetail->buyer_address ?? "N/A",
                    "city" => $deal->dealBuyerDetail->buyer_city ?? "N/A",
                    "state" => $deal->dealBuyerDetail->buyer_state ?? "N/A",
                    "postalCode" => $deal->dealBuyerDetail->buyer_zipcode ?? "N/A",
                    "country" => $deal->dealBuyerDetail->buyer_country ?? "N/A",
                    "residential" => false
                ],

                "items" => [
                    [
                        "sku" => $deal->model_number ?? "N/A",
                        "name" => $deal->watchBrandDetail->name ?? "N/A",
                        "quantity" => 1,
                        "unitPrice" => 0.00,
                        "weight" => [
                            "value" => 16,
                            "units" => "ounces"
                        ]
                    ]
                ],

                "amountPaid" => 0.00
            ];

            $orderResponse = $ss->createOrder($orderPayload);
            $orderId = $orderResponse['orderId'] ?? null;

            if (!$orderId) {
                return response()->json(['error' => 'Order creation failed', 'response' => $orderResponse], 500);
            }

            // Step 2: Create label for this order
            $labelPayload = [
                'carrierCode' => 'stamps_com',       // Carrier name from ShipStation
                'serviceCode' => 'usps_priority_mail', // Service type
                'packageCode' => 'package',            // Package type
                'confirmation' => 'none',              // Delivery confirmation (optional)
                'shipDate' => date('Y-m-d'),            // Today or future date
            ];

            $labelResponse = $ss->createLabelForOrder($orderId,$labelPayload);
            // Save in DB
            $record = DealShipStationOrder::create([
                'deal_id' => $dealId,
                'order_number' => $orderPayload['orderNumber'],
                'shipstation_order_id' => $orderId,
                'order_data' => $orderResponse,
                'label_pdf_path' => $labelResponse['saved_path'] ?? null,
                'label_data' => $labelResponse,
            ]);

            return response()->json([
                'success' => true,
                'db_record' => $record,
                'order' => $orderResponse,
                'label' => $labelResponse
            ]);

        }
        catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }


}
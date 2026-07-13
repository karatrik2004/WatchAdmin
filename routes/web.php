<?php

use App\Http\Controllers\Admin\StockController;
use App\Http\Controllers\Admin\XeroController;
use App\Http\Controllers\Admin\ShippingInvoiceController;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\PageController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DealController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\BrandController;
use App\Http\Controllers\Admin\VendorController;

/*

|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Clear All:
Route::get('/clear-all', function () {
    Artisan::call('route:clear');
    Artisan::call('config:cache');
    Artisan::call('config:clear');
    Artisan::call('cache:clear');
    Artisan::call('view:clear');
    return 'All cache cleared';
});

Route::get('/health', function () {
    try {
        \Illuminate\Support\Facades\DB::connection()->getPdo();
    } catch (\Throwable $e) {
        return response()->json([
            'status' => 'unhealthy',
            'db' => 'failed',
            'error' => $e->getMessage(),
        ], 503);
    }

    try {
        \Illuminate\Support\Facades\Redis::connection()->ping();
    } catch (\Throwable $e) {
        return response()->json([
            'status' => 'unhealthy',
            'redis' => 'failed',
            'error' => $e->getMessage(),
        ], 503);
    }

    return response()->json([
        'status' => 'healthy',
        'app' => 'ok',
        'db' => 'ok',
        'redis' => 'ok',
    ]);
})->name('health');

require __DIR__ . '/auth.php';

/** Front-end routes START  */
Route::get('/', [PageController::class, 'index'])->name('home');

/** Admin routes START */
Route::group(['middleware' => 'disablepreventback'], function () {
    Route::group(['namespace' => '', 'prefix' => 'admin', 'as' => 'admin.'], function () {

        Route::get('/', [AdminController::class, 'login'])->name('login');
        Route::get('/login', [AdminController::class, 'login'])->name('login');
        Route::post('/loginProcess', [AdminController::class, 'loginProcess'])->name('loginprocess');
        Route::get('/forgot-password', [AdminController::class, 'forgotPassword'])->name('forgotpassword');
        Route::get('/logout', [AdminController::class, 'logout'])->name('logout');

        Route::group(['middleware' => ['admin', 'role:super admin,account']], function () {

            Route::get('/xero/login', [XeroController::class, 'redirectToXero'])->name('xero.login');
            Route::get('/xero/callback', [XeroController::class, 'callback']);
            Route::get('/xero/create-invoice', [XeroController::class, 'createInvoice'])->name('xero.create-invoice');

            // dashboard route
            Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

            // Change password
            Route::get('/change-password', [DashboardController::class, 'changePassword'])->name('changepassword');
            Route::post('/updatepassword', [DashboardController::class, 'UpdatePassword'])->name('updatepassword');

            // My profile routes
            Route::get('/profile', [DashboardController::class, 'profile'])->name('profile');
            Route::post('/submit-profile', [DashboardController::class, 'submit_profile'])->name('submit_profile');

            /** products routes */
            Route::resource('deals', DealController::class);
            Route::post('deals/{deal}/email-invoice-to-customer', [DealController::class, 'emailInvoiceToCustomer'])
                ->name('deals.email-invoice-customer');
            Route::post('deals/{deal}/mark-reviewed', [DealController::class, 'markReviewed'])
                ->name('deals.mark-reviewed');
            Route::post('deals/{id}/compute-pl', [DealController::class, 'computeProfitLoss'])->name('deals.compute-pl');
            Route::any('delete-deal/{id}', [DealController::class, 'destroy'])->name('delete-deal');
            Route::get('download-deal-excel', [DealController::class, 'downloadProductExcel'])->name('deal-download-excel');
            Route::get('account/deals/{id}/details', [DealController::class, 'getAccountDeal'])->name('deal.account.details');
            Route::get('/get-cities/{state_id}', [DealController::class, 'getCitiesByStateID'])->name('get-cities');

            Route::get('/brand-autocomplete',[DealController::class, 'brandAutocomplete'])->name('deal.brand.autocomplete');
            Route::get('/deal/brand-name', [DealController::class, 'getBrandName'])->name('deal.brand.name');

            Route::resource('stocks', StockController::class);
            Route::resource('brands', BrandController::class);
            Route::resource('vendors', VendorController::class);

// Show the approve deal modal (GET)
            Route::get('deals/{deal}/approve-modal', [DealController::class, 'loadApproveDealModal'])
                ->name('deals.approve.modal.show');

// Handle approval of the deal (POST)
            Route::post('deals/{deal}/approve', [DealController::class, 'loadApproveDealModal'])
                ->name('deals.approve');

            Route::get('/admin/invoice/download/{deal_id}/{currency}', [DealController::class, 'downloadInvoice'])
                ->name('invoice.download');

            Route::get('/admin/shipping-invoice/template/download/{deal_id}', [DealController::class, 'downloadShippingInvoiceTemplate'])
                ->name('shipping.invoice.template.download');

            // Shipping Deals
            Route::get('/shipping-deals', [DealController::class, 'shippingDeals'])
                ->name('shipping-deals');
            Route::post('/shipping-deals/bulk-download', [DealController::class, 'bulkDownloadShippingInvoices'])
                ->name('shipping-deals.bulk-download');

            // routes/web.php
            Route::get('/deals/{id}/myob-invoice', [DealController::class, 'generateInvoiceFromDeal'])
                ->name('deals.myob-invoice');

            Route::post('/generate-shipping/{dealId}', [DealController::class, 'generateShippingFromDeal'])
                ->name('deals.generate.shipping');

            // Shipping Invoices (saved in portal)
            Route::get('/shipping-invoices', [ShippingInvoiceController::class, 'index'])
                ->name('shipping-invoices.index');
            Route::get('/shipping-invoices/create', [ShippingInvoiceController::class, 'create'])
                ->name('shipping-invoices.create');
            Route::post('/shipping-invoices', [ShippingInvoiceController::class, 'store'])
                ->name('shipping-invoices.store');
            Route::get('/shipping-invoices/search-deals', [ShippingInvoiceController::class, 'searchDeals'])
                ->name('shipping-invoices.search-deals');
            // More specific route before /shipping-invoices/{id}
            Route::get('/shipping-invoices/{id}/download', [ShippingInvoiceController::class, 'download'])
                ->name('shipping-invoices.download');
            Route::get('/shipping-invoices/{id}', [ShippingInvoiceController::class, 'show'])
                ->name('shipping-invoices.show');
            Route::delete('/shipping-invoices/{id}', [ShippingInvoiceController::class, 'destroy'])
                ->name('shipping-invoices.destroy');
        });
    });
});

// Company Settings routes
require __DIR__ . '/company_settings.php';
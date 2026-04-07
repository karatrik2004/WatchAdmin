<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\PageController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DealController;
use App\Http\Controllers\Admin\ProductController;

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
		
		Route::group(['middleware' => ['admin']], function () {

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
			Route::any('delete-deal/{id}', [DealController::class, 'destroy'])->name('delete-deal');
			Route::get('download-deal-excel', [DealController::class, 'downloadProductExcel'])->name('deal-download-excel');
			
			Route::get('/get-cities/{state_id}', [DealController::class, 'getCitiesByStateID'])->name('get-cities');

			/** product routes */
			Route::resource('products', ProductController::class);
			Route::any('delete-product/{id}', [ProductController::class, 'destroy'])->name('delete-product');
		});	
	});
});
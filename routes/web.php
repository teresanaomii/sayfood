<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\EventCustController;
use App\Http\Controllers\FoodController;
use App\Http\Controllers\GoogleAuthController;
use App\Http\Controllers\HomeDishesController;
use App\Http\Controllers\HomeRestaurantController;
use App\Http\Controllers\LanguageController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\RestaurantAdminController;
use App\Http\Controllers\RestaurantController;
use App\Http\Controllers\TransactionController;
use Illuminate\Support\Facades\Auth;
use Spatie\Activitylog\Models\Activity;

// HOME
Route::get('/', [HomeDishesController::class, 'show'])->name('home');

// FORGOT PASSWORD
Route::get('/forgot-password', [PasswordResetController::class, 'requestForm'])->name('password.request');
Route::post('/forgot-password', [PasswordResetController::class, 'sendResetLink'])->name('password.email');
Route::get('/reset-password/{token}', [PasswordResetController::class, 'resetForm'])->name('password.reset');
Route::post('/reset-password', [PasswordResetController::class, 'resetPassword'])->name('password.update');

Route::middleware('twofactor')->group(function () {

    // LOGOUT
    Route::get('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // PROFILE OPTIONS
    Route::get('/profile', [AuthController::class, 'profile'])->name('profile');
    Route::post('/delete-account', [AuthController::class, 'deleteAccount'])->name('delete.account');
    Route::post('/profile-image', [AuthController::class, 'updateProfileImage'])->name('update.profile.image');


    // CUSTOMER & ADMIN ROUTES
    Route::middleware('role:admincustomer')->group(function () {

        Route::post('/profile', [AuthController::class, 'updateProfile'])->name('update.profile');
        Route::post('/created-event/completed/{event}', [EventController::class, 'completed'])->name('completed.event');
    });

    // CUSTOMER ROUTES
    Route::middleware('role:customer')->group(function () {

        Route::post('/login-as-restaurant', [AuthController::class, 'redirectToRestaurantLogin'])->name('login.as.restaurant');

        Route::get('/activity', [TransactionController::class, 'customerActivities'])->name('activity');
        Route::post('/events/propose', [EventController::class, 'store'])->name('events.store');

        Route::get('/created-event/{id}', [EventController::class, 'showCreatedEvent'])->name('show.created-event');

        Route::get('/cart', [CartController::class, 'show'])->name('show.cart')->middleware('auth');
        Route::post('/cart/add/{food}', [CartController::class, 'store'])->name('add.cart')->middleware('auth');
        Route::post('/cart/increase/{cart}', [CartController::class, 'increase'])->name('increase.cart')->middleware('auth');
        Route::post('/cart/decrease/{cart}', [CartController::class, 'decrease'])->name('decrease.cart')->middleware('auth');
        Route::post('/cart/note/{cart}', [CartController::class, 'updateNote'])->name('note.cart')->middleware('auth');
        Route::post('/cart/clear', [CartController::class, 'clearCart']);
        Route::post('/checkout/confirm', [TransactionController::class, 'confirmPayment'])->name('checkout.confirm');
        Route::post('/cart/cancel', [CartController::class, 'cancelCart'])->name('cart.cancel');

        Route::post('/orders/{id}/rate', [TransactionController::class, 'rate'])->name('orders.rate');

        Route::post('/post-event', [HomeDishesController::class, 'store'])->name('event.join');;

        Route::get('/events', [EventCustController::class, 'index'])->name('events');

        Route::get('/foods', [FoodController::class, 'index'])->name('foods');
        Route::get('/foods/resto/{id}', [RestaurantController::class, 'show'])->name('resto.show');

        Route::post('/report', [ReportController::class, 'store'])->name('report.store');
    });


    // RESTAURANT ROUTES
    Route::middleware('role:restaurant')->group(function () {

        Route::post('/profile-restaurant', [AuthController::class, 'updateProfileRestaurant'])->name('update.profile.restaurant');
        Route::post('/login-as-customer', [AuthController::class, 'redirectToCustomerLogin'])->name('login.as.customer');

        Route::get('/restaurant-home', [HomeRestaurantController::class, 'index'])->name('restaurant-home');

        Route::get('/restaurant-activity', [TransactionController::class, 'restaurantActivity'])->name('restaurant-activity');

        Route::get('/restaurant-transactions', [TransactionController::class, 'index'])->name('restaurant-transactions');
        Route::get('/restaurant-transactions/filter', [TransactionController::class, 'filter'])->name('restaurant-transactions.filter');
        Route::get('/restaurant-transactions/download', [TransactionController::class, 'download'])->name('restaurant-transactions.download');

        Route::get('/restaurant-orders', [TransactionController::class, 'manageOrders'])->name('restaurant-orders');
        Route::post('/restaurant-orders/{id}/update-status', [TransactionController::class, 'updateStatus'])->name('restaurant-orders.update-status');

        Route::get('/restaurant-foods', [RestaurantController::class, ('manageFood')])->name('manage.food.restaurant');
        Route::post('/restaurant-foods/create', [RestaurantController::class, 'store'])->name('create.food.restaurant');
        Route::patch('/restaurant-foods/update/{id}', [RestaurantController::class, 'update'])->name('update.food.restaurant');
        Route::delete('/restaurant-foods/delete/{id}', [RestaurantController::class, 'destroy'])->name('delete.food.restaurant');

        Route::post('/foods/upload', [FoodController::class, 'processZipUpload'])->name('foods.upload.process');
        Route::get('/foods/template/download', [FoodController::class, 'downloadTemplate'])->name('foods.template.download');
    });


    // ADMIN ROUTES
    Route::middleware('role:admin')->group(function () {

        // ADMIN APPROVE RESTAURANT REGISTRATION (DELETE SOON)!
        Route::get('/approve-registration/{id}', [AuthController::class, 'approveRegistration'])->name('approve.registration');

        Route::get('/admin/manage-events', [EventController::class, 'index'])->name('show.manage.events');
        Route::get('/admin/manage-events/{event}', [EventController::class, 'show'])->name('show.manage.events.detail');

        Route::post('/admin/manage-events/approve/{event}', [EventController::class, 'approve'])->name('admin.approve.event');
        Route::post('/admin/manage-events/reject/{event}', [EventController::class, 'reject'])->name('admin.reject.event');

        Route::post('admin/create/event', [EventController::class, 'adminStoreEvent'])->name('admin.create.event');

        Route::get('/admin/manage-restaurants', [RestaurantAdminController::class, 'index'])->name('show.manage.restaurants');
        Route::get('/admin/manage-restaurants/{id}', [RestaurantAdminController::class, 'show'])->name('show.manage.restaurants.detail');
        Route::post('/admin/manage-restaurants/export/{id}', [RestaurantAdminController::class, 'export'])->name('show.manage.restaurants.detail.export');
        Route::post('/admin/manage-restaurants/reject/{id}', [RestaurantAdminController::class, 'reject'])->name('show.manage.restaurants.detail.reject');
        Route::post('/admin/manage-restaurants/approve/{id}', [RestaurantAdminController::class, 'approve'])->name('show.manage.restaurants.detail.approve');

        Route::get('/admin/manage-reports', [ReportController::class, 'index'])->name('show.manage.reports');
        Route::get('/admin/manage-reports/{report}', [ReportController::class, 'show'])->name('show.manage.reports.detail');
        Route::post('/admin/manage-reports/suspend/{report}', [ReportController::class, 'suspend'])->name('show.manage.reports.detail.suspend');
        Route::post('/admin/manage-reports/safe/{report}', [ReportController::class, 'safe'])->name('show.manage.reports.detail.safe');

        Route::get('/admin/logs', function () {
            return Activity::all();
        });

        Route::get('/popup-report-resto', function () {
            return view('popup-report-resto');
        })->name('popup.report.resto');
    });
});

Route::middleware('auth')->group(function () {

    // TWO-FACTOR AUTH
    Route::get('/two-factor', [AuthController::class, 'twoFactorVerification'])->name('twofactor.verif');
    Route::post('/two-factor', [AuthController::class, 'twoFactorSubmit'])->name('twofactor.submit');
    Route::post('/two-factor-resend', [AuthController::class, 'twoFactorResend'])->name('twofactor.resend');
});

Route::middleware('guest')->group(function () {

    // LOGIN SELECTION
    Route::get('/login', function () {
        return view('login');
    })->name('selection.login');

    // CUSTOMER LOCAL LOGIN
    Route::get('/login-customer', function () {
        return view('login-customer');
    })->name('show.login');

    // CUSTOMER LOCAL REGISTER
    Route::get('/register-customer', function () {
        return view('register-customer');
    })->name('show.register');

    // CUSTOMER LOCAL AUTH POST ROUTES
    Route::post('/login', [AuthController::class, 'login'])->name('login');
    Route::post('/register', [AuthController::class, 'register'])->name('register');

    // GOOGLE AUTH (CUSTOMER ONLY)
    Route::get('/auth/google/redirect', [GoogleAuthController::class, 'redirect'])->name('auth.google.redirect');
    Route::get('/auth/google/callback', [GoogleAuthController::class, 'callback'])->name('auth.google.callback');

    // RESTAURANT LOCAL LOGIN
    Route::get('/login-restaurant', function () {
        return view('login-restaurant');
    })->name('show.login.restaurant');

    // RESTAURANT LOCAL REGISTER
    Route::get('/register-restaurant', function () {
        return view('register-restaurant');
    })->name('show.register.restaurant');

    // RESTAURANT LOCAL AUTH POST ROUTES 
    Route::post('/login-restaurant', [AuthController::class, 'loginRestaurant'])->name('login.restaurant');
    Route::post('/register-restaurant', [AuthController::class, 'registerRestaurant'])->name('register.restaurant');
});

// LANGUAGE
Route::get('/lang/{locale}', [LanguageController::class, 'switch'])->name('language.switch');

Route::get('/test-session', function () {
    session(['locale' => 'id']);
    return 'Session set: ' . session('locale');
});

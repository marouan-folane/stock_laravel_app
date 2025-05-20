<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\ReportsController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\ActivityController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\AlertController;
use App\Http\Controllers\SmsController;
use App\Http\Controllers\SensibleCategoryController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\EmployeeProfileController;
use App\Http\Controllers\ClientProfileController;
use App\Http\Controllers\GoogleController;
use App\Http\Controllers\PublicController;
use App\Http\Controllers\StatsController;
use App\Http\Controllers\SupplierProfileController;
use App\Http\Controllers\Auth\SupplierRegisterController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Public routes - these must be defined before admin routes to prevent conflicts
Route::get('/', [PublicController::class, 'home']);
Route::get('/products/all', [PublicController::class, 'allProducts'])->name('public.all-products');
Route::get('/products/{id}', [PublicController::class, 'productDetails'])->name('public.product.details');
Route::get('/categories/{id}/products', [PublicController::class, 'categoryProducts'])->name('public.category.products');
Route::get('/auth/google', [GoogleController::class, 'redirect'])->name('google-auth');
Route::get('/auth/google/call-back', [GoogleController::class, 'callbackGoogle'])->name('google-callback');

// Public signup routes
Route::get('/signup', [PublicController::class, 'showSignupForm'])->name('public.signup');
Route::post('/signup', [PublicController::class, 'register'])->name('public.register');

// Supplier registration routes
Route::get('/register/supplier', [SupplierRegisterController::class, 'showRegistrationForm'])->name('register.supplier.form');
Route::post('/register/supplier', [SupplierRegisterController::class, 'register'])->name('register.supplier');

// Authentication routes
Auth::routes();

// Home route with role-based redirection
Route::get('/home', [HomeController::class, 'index'])->name('home');

// Protected routes
Route::middleware(['auth'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/mark-as-read/{id}', [NotificationController::class, 'markAsRead'])->name('notifications.mark-as-read');
    Route::post('/notifications/mark-all-as-read', [NotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-as-read');

    // Profile Management
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/notifications', [ProfileController::class, 'updateNotificationSettings'])->name('profile.notifications');
    Route::get('/activity-log', [ActivityLogController::class, 'index'])->name('activity-log.index');

    // Employee Profile Management
    Route::middleware(['auth', 'role:employee,admin'])->group(function () {
        Route::get('/employee/profile', [EmployeeProfileController::class, 'edit'])->name('employee.profile.edit');
        Route::put('/employee/profile', [EmployeeProfileController::class, 'update'])->name('employee.profile.update');
    });

    // Client Profile Management
    Route::middleware(['auth', 'role:client'])->group(function () {
        Route::get('/client/profile', [ClientProfileController::class, 'edit'])->name('client.profile.edit');
        Route::put('/client/profile', [ClientProfileController::class, 'update'])->name('client.profile.update');
    });

    // Routes accessible to both employees and admins
    Route::middleware(['role:admin,employee'])->group(function () {
        // Customers
        Route::resource('customers', CustomerController::class);

        // Statistics
        Route::get('/stats', [StatsController::class, 'index'])->name('stats.index');
    });

    // Admin only routes
    Route::middleware(['role:admin'])->group(function () {
        // Products
        Route::resource('products', ProductController::class);
        Route::get('/products/create/admin', [ProductController::class, 'create'])->name('products.create');
        Route::get('/products/expiring/soon', [ProductController::class, 'expiringSoon'])->name('products.expiring');

        // Categories
        Route::resource('categories', CategoryController::class);

        // Suppliers
        Route::resource('suppliers', SupplierController::class);
        Route::get('/suppliers/{supplier}/order-history', [SupplierController::class, 'orderHistory'])->name('suppliers.order-history');

        // Stock Management
        Route::get('/stock', [StockController::class, 'index'])->name('stock.index');
        Route::get('/products/{product}/stock/add', [StockController::class, 'add'])->name('products.stock.create');
        Route::post('/products/{product}/stock/add', [StockController::class, 'store'])->name('stock.store');
        Route::get('/products/{product}/stock/remove', [StockController::class, 'remove'])->name('products.stock.remove');
        Route::post('/products/{product}/stock/remove', [StockController::class, 'destroy'])->name('stock.destroy');
        Route::get('/products/{product}/stock/history', [StockController::class, 'history'])->name('products.stock.history');

        // Supplier Stock Movements
        Route::get('/stock/supplier-movements', [StockController::class, 'supplierMovements'])->name('stock.supplier-movements');

        // New unified stock management routes
        Route::get('/products/{product}/stock/adjust', [StockController::class, 'createStock'])->name('products.stock.create');
        Route::post('/products/{product}/stock/adjust', [StockController::class, 'storeStock'])->name('products.stock.store');

        // Reports
        Route::get('/reports', [ReportsController::class, 'index'])->name('reports.index');
        Route::match(['get', 'post'], '/reports/generate', [ReportsController::class, 'generate'])->name('reports.generate');
        Route::match(['get', 'post'], '/reports/download', [ReportsController::class, 'download'])->name('reports.download.post');
        Route::get('/reports/download/{reportId?}', [ReportsController::class, 'download'])->name('reports.download');
        Route::match(['get', 'post'], '/reports/download-csv', [ReportsController::class, 'downloadCsv'])->name('reports.download-csv.post');
        Route::get('/reports/download-csv/{reportId?}', [ReportsController::class, 'downloadCsv'])->name('reports.download-csv');

        // Sensible Categories
        Route::resource('sensible-categories', SensibleCategoryController::class);
        Route::put('/sensible-categories/{sensibleCategory}/toggle-active', [SensibleCategoryController::class, 'toggleActive'])
            ->name('sensible-categories.toggle-active');
        Route::get('/sensible-categories/{sensibleCategory}/test-notification', [SensibleCategoryController::class, 'testNotification'])
            ->name('sensible-categories.test-notification');

        // Sales routes
        Route::resource('sales', SaleController::class);
        Route::get('sales/{sale}/pdf', [SaleController::class, 'generatePdf'])->name('sales.pdf');
        Route::get('sales/{sale}/payments/create', [PaymentController::class, 'create'])->name('sales.payments.create');

        // Payment routes
        Route::resource('payments', PaymentController::class);

        // Activity routes
        Route::get('/activities', [ActivityController::class, 'index'])->name('activities.index');

        // Alerts
        Route::get('/alerts', [AlertController::class, 'index'])->name('alerts.index');
        Route::post('/alerts/{id}/mark-as-read', [AlertController::class, 'markAsRead'])->name('alerts.mark-as-read');
        Route::post('/alerts/mark-all-as-read', [AlertController::class, 'markAllAsRead'])->name('alerts.mark-all-as-read');
        Route::get('/alerts/check/expiring', [AlertController::class, 'checkExpiringProducts'])->name('alerts.check.expiring');
        Route::get('/alerts/check/low-stock', [AlertController::class, 'checkLowStockProducts'])->name('alerts.check.low-stock');
        Route::get('/alerts/check/out-of-stock', [AlertController::class, 'checkOutOfStockProducts'])->name('alerts.check.out-of-stock');
        Route::delete('/alerts/{id}', [AlertController::class, 'delete'])->name('alerts.delete');
        Route::delete('/alerts', [AlertController::class, 'deleteAll'])->name('alerts.delete-all');
        Route::post('/alerts/delete-multiple', [AlertController::class, 'deleteMultiple'])->name('alerts.delete-multiple');

        // Test stock email alerts
        Route::get('/alerts/test-emails', function() {
            \Illuminate\Support\Facades\Artisan::call('alerts:send-emails');
            return back()->with('success', 'Stock email alerts test has been initiated. Check your email inbox.');
        })->name('alerts.test-emails');
    });

    // Admin only routes using Gate
    Route::middleware(['can:manage-users'])->group(function () {
        // User Management
        Route::resource('users', UserController::class);

        // Settings
        Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
        Route::put('/settings', [SettingController::class, 'update'])->name('settings.update');

        // Email Notification Settings
        Route::get('/settings/notifications', [SettingController::class, 'showNotifications'])->name('settings.notifications');
        Route::put('/settings/notifications', [SettingController::class, 'updateNotifications'])->name('settings.notifications.update');
        Route::post('/settings/notifications/test', [SettingController::class, 'testEmail'])->name('settings.notifications.test');

        // SMS Management - Admin only
        Route::get('/sms', [SmsController::class, 'showSmsForm'])->name('sms.form');
        Route::post('/sms/send-direct', [SmsController::class, 'sendDirectSms'])->name('sms.send-direct');
        Route::post('/sms/send-alert', [SmsController::class, 'sendAlertSms'])->name('sms.send-alert');
        Route::post('/sms/send-bulk', [SmsController::class, 'sendBulkSms'])->name('sms.send-bulk');

        // New SMS routes with InfoBip and MSG91
        Route::get('/sms/new', [SmsController::class, 'index'])->name('sms.index');
        Route::post('/sms/send', [SmsController::class, 'send'])->name('sms.send');
        Route::post('/sms/send-to-user', [SmsController::class, 'sendToUser'])->name('sms.send-to-user');
    });

    // Employee Routes
    Route::middleware(['auth', 'role:employee,admin'])->prefix('employee')->name('employee.')->group(function () {
        Route::get('/dashboard', [EmployeeController::class, 'index'])->name('dashboard');
        Route::get('/products', [EmployeeController::class, 'products'])->name('products');
        Route::get('/sales', [EmployeeController::class, 'sales'])->name('sales');
        Route::get('/customers', [EmployeeController::class, 'customers'])->name('customers');
        Route::get('/pending-orders', [EmployeeController::class, 'pendingOrders'])->name('pending-orders');
        Route::get('/orders/{id}', [EmployeeController::class, 'orderDetails'])->name('order-details');
        Route::post('/orders/{id}/process', [EmployeeController::class, 'processOrder'])->name('process-order');
        // Stock adjustment routes
        Route::get('/products/{id}/adjust-stock', [EmployeeController::class, 'adjustStock'])->name('products.adjust-stock');
        Route::post('/products/{id}/update-stock', [EmployeeController::class, 'updateStock'])->name('products.update-stock');
        Route::get('/sales/{sale}/pdf', [SaleController::class, 'generatePdf'])->name('sales.pdf');
    });

    // Client Routes
    Route::middleware(['auth', 'role:client'])->prefix('client')->group(function () {
        Route::get('/dashboard', [ClientController::class, 'index'])->name('client.dashboard');
        Route::get('/products', [ClientController::class, 'products'])->name('client.products');
        Route::get('/orders', [ClientController::class, 'orders'])->name('client.orders');

        Route::get('/orders/{id}', [ClientController::class, 'orderDetails'])->name('client.orders.details');
        Route::post('/client/place-order', [SaleController::class, 'store'])->name('client.place.order');
        Route::post('/orders/{id}/cancel', [ClientController::class, 'cancelOrder'])->name('client.orders.cancel');
        // Cart functionality
        Route::post('/cart/add', [ClientController::class, 'addToCart'])->name('client.cart.add');
        Route::get('/cart', [ClientController::class, 'viewCart'])->name('client.cart');
        Route::delete('/cart/{id}', [ClientController::class, 'removeFromCart'])->name('client.cart.remove');
    });

    // Supplier Routes
    Route::middleware(['auth', 'role:supplier'])->prefix('supplier')->group(function () {
        Route::get('/dashboard', [SupplierProfileController::class, 'index'])->name('supplier.profile');
        Route::get('/movements', [SupplierProfileController::class, 'movements'])->name('supplier.movements');
        Route::get('/profile/edit', [SupplierProfileController::class, 'edit'])->name('supplier.profile.edit');
        Route::put('/profile/update', [SupplierProfileController::class, 'update'])->name('supplier.profile.update');
        Route::get('/profile/change-password', [SupplierProfileController::class, 'changePassword'])->name('supplier.profile.change-password');
        Route::put('/profile/update-password', [SupplierProfileController::class, 'updatePassword'])->name('supplier.profile.update-password');
    });
});


// Special route for client orders - must be placed outside the admin middleware

// Make sure to update HomeController to redirect based on role
// Route::get('/stock', [HomeController::class, 'index'])->name('home');


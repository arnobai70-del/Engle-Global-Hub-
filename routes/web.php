<?php

use App\Http\Controllers\AboutController;
use App\Http\Controllers\Admin\ActivityLogController;
use App\Http\Controllers\Admin\AffiliateController;
use App\Http\Controllers\Admin\AgentController;
use App\Http\Controllers\Admin\AppointmentController;
use App\Http\Controllers\Admin\BookingPageController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\CategoryPageController;
use App\Http\Controllers\Admin\CityController;
use App\Http\Controllers\Admin\ContactDirectoryController;
use App\Http\Controllers\Admin\ContentApprovalController;
use App\Http\Controllers\Admin\ContentEntryController;
use App\Http\Controllers\Admin\CountryController;
use App\Http\Controllers\Admin\CourseController;
use App\Http\Controllers\Admin\CurrencyController;
use App\Http\Controllers\Admin\CurrencyPageController;
use App\Http\Controllers\Admin\DestinationController;
use App\Http\Controllers\Admin\EligibilityRuleController;
use App\Http\Controllers\Admin\FeatureControlController;
use App\Http\Controllers\Admin\ForumModerationController;
use App\Http\Controllers\Admin\InstitutionController;
use App\Http\Controllers\Admin\IntegrationController;
use App\Http\Controllers\Admin\LanguageController;
use App\Http\Controllers\Admin\LanguagePageController;
use App\Http\Controllers\Admin\MasterDataPageController;
use App\Http\Controllers\Admin\MediaAssetController;
use App\Http\Controllers\Admin\MenuItemController;
use App\Http\Controllers\Admin\PaymentRecordController;
use App\Http\Controllers\Admin\ReportPageController;
use App\Http\Controllers\Admin\ReviewModerationController;
use App\Http\Controllers\Admin\RewardProgramController;
use App\Http\Controllers\Admin\SeoController;
use App\Http\Controllers\Admin\ServiceDestinationController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\SettingPageController;
use App\Http\Controllers\Admin\SiteFactsController;
use App\Http\Controllers\Admin\StudentController;
use App\Http\Controllers\Admin\SuccessPipelineController;
use App\Http\Controllers\Admin\SystemLogPageController;
use App\Http\Controllers\Admin\TeamMemberController;
use App\Http\Controllers\Admin\TranslationController;
use App\Http\Controllers\Agent\AgentPortalController;
use App\Http\Controllers\Agent\PartnerRegistrationController;
use App\Http\Controllers\Consultancy\CostCalculatorController;
use App\Http\Controllers\Consultancy\EligibilityController;
use App\Http\Controllers\Consultancy\InstitutionDirectoryController;
use App\Http\Controllers\Consultancy\MalaysiaStudyController;
use App\Http\Controllers\Consultancy\SingaporeGuideIndexController;
use App\Http\Controllers\Consultancy\SingaporeStudyController;
use App\Http\Controllers\Consultancy\StudentEnquiryController;
use App\Http\Controllers\ContentPageController;
use App\Http\Controllers\ExploreBangladeshController;
use App\Http\Controllers\FeaturePageController;
use App\Http\Controllers\Flight\FlightBookingConfirmationIntentController;
use App\Http\Controllers\Flight\FlightBookingDraftController;
use App\Http\Controllers\Flight\FlightBookingDraftReviewController;
use App\Http\Controllers\Flight\FlightEnquiryController;
use App\Http\Controllers\Flight\FlightOfferSelectionController;
use App\Http\Controllers\Flight\FlightOrderAttemptStatusController;
use App\Http\Controllers\Flight\FlightOrderConfirmationController;
use App\Http\Controllers\Flight\FlightOrderExecutionController;
use App\Http\Controllers\Flight\FlightOrderPaymentAttemptStatusController;
use App\Http\Controllers\Flight\FlightOrderPaymentExecutionController;
use App\Http\Controllers\Flight\FlightOrderPaymentReadinessController;
use App\Http\Controllers\Flight\FlightOrderPaymentReconciliationController;
use App\Http\Controllers\Flight\FlightOrderReconciliationController;
use App\Http\Controllers\Flight\FlightSearchController;
use App\Http\Controllers\Flight\FlightTravelerValidationController;
use App\Http\Controllers\FlightBookingController;
use App\Http\Controllers\ForumController;
use App\Http\Controllers\Hotel\HotelController;
use App\Http\Controllers\Hotel\HotelSearchController;
use App\Http\Controllers\LeadController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\OfferController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\RewardController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\ServiceEnquiryController;
use App\Http\Controllers\ServicePageController;
use App\Http\Controllers\Student\StudentApplicationController;
use App\Http\Controllers\Student\StudentApplicationMessageController;
use App\Http\Controllers\Student\StudentAppointmentController;
use App\Http\Controllers\Student\StudentDocumentController;
use App\Http\Controllers\Tour\TourController;
use App\Http\Controllers\Tour\TourSearchController;
use App\Http\Controllers\TravelServicesController;
use App\Http\Controllers\Visa\VisaController;
use App\Http\Controllers\Visa\VisaRequirementController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'home')->name('home');

Route::get('/about', AboutController::class)
    ->middleware('feature:about')
    ->name('about');

Route::view('/support', 'public.support')
    ->middleware('feature:support')
    ->name('support');

Route::view('/terms', 'public.terms')
    ->name('terms');

Route::view('/why-us', 'public.why-us')
    ->name('why-us');

Route::view('/privacy', 'public.privacy')
    ->name('privacy');

Route::redirect('/contact', '/support', 301)->name('contact');

Route::view('/b2b', 'public.b2b')
    ->name('b2b.info');

/*
 * Work visa pages.
 *
 * Presentation only. No work visa application, document upload, fee, payment,
 * provider call or customer record is created from these views, and they hold
 * no customer data, so they stay public and follow only the visa feature
 * visibility. Both pages state plainly that work visa processing is not
 * connected yet.
 */
Route::get('/work-visa', [ServicePageController::class, 'workVisa'])
    ->middleware('feature:visa')
    ->name('work-visa.index');

/*
 * Public service pages (one template, quote form). The online searches behind them
 * (POST routes below) still require a signed-in customer with the right permission.
 */
Route::get('/flights', [ServicePageController::class, 'flights'])->middleware('feature:flights')->name('flights.index');
Route::get('/hotels', HotelController::class)->middleware('feature:hotels')->name('hotels.index');
Route::get('/tours', TourController::class)->middleware('feature:tours')->name('tours.index');
Route::get('/visa', VisaController::class)->middleware('feature:visa')->name('visa.index');

// The old disabled preview form now goes to the real quote form on the work visa page.
Route::get('/work-visa/apply', static fn () => redirect()->to(route('work-visa.index').'#quote-form', 301))
    ->middleware('feature:visa')
    ->name('work-visa.apply');

Route::middleware(['auth', 'verified'])->group(function () {

    Route::view('/dashboard', 'dashboard')
        ->middleware('feature:dashboard')
        ->name('dashboard');

    Route::view('/account', 'account.overview')
        ->middleware('feature:account')
        ->name('account.overview');

    Route::post('/hotels/search', HotelSearchController::class)
        ->middleware(['feature:hotels', 'permission:hotels.search'])
        ->name('hotels.search');

    /*
     * Hotel booking steps.
     *
     * These two views are presentation only. No room inventory, guest record,
     * booking, payment or supplier call is created from them: they render the
     * approved layout and state plainly that the live steps are not connected
     * yet. They stay behind the same guest / verification / permission
     * middleware as every other customer hotel route.
     */
    Route::view('/hotels/rooms', 'hotels.rooms')
        ->middleware(['feature:hotels', 'permission:hotels.search'])
        ->name('hotels.rooms');

    Route::view('/hotels/booking', 'hotels.booking')
        ->middleware(['feature:hotels', 'permission:hotels.search'])
        ->name('hotels.booking');

    Route::post('/tours/search', TourSearchController::class)
        ->middleware(['feature:tours', 'permission:tours.search'])
        ->name('tours.search');

    Route::post('/visa/requirements', VisaRequirementController::class)
        ->middleware(['feature:visa', 'permission:visa.view'])
        ->name('visa.requirements');

    Route::get('/bookings', [FlightBookingController::class, 'index'])
        ->middleware(['feature:bookings', 'permission:flights.book'])
        ->name('bookings.index');

    Route::get('/bookings/{booking}/invoice', [FlightBookingController::class, 'invoice'])
        ->middleware(['feature:bookings', 'permission:flights.book'])
        ->name('bookings.invoice');

    Route::get('/bookings/{booking}', [FlightBookingController::class, 'show'])
        ->middleware(['feature:bookings', 'permission:flights.book'])
        ->name('bookings.show');

    Route::post(
        '/flights/search',
        FlightSearchController::class
    )
        ->middleware(['feature:flights', 'permission:flights.search'])
        ->name('flights.search');
    Route::post(
        '/flights/offers/select',
        FlightOfferSelectionController::class
    )
        ->middleware(['feature:flights', 'permission:flights.search'])
        ->name('flights.offers.select');
    Route::post(
        '/flights/travelers/validate',
        FlightTravelerValidationController::class
    )
        ->middleware(['feature:flights', 'permission:flights.search'])
        ->name('flights.travelers.validate');

    Route::prefix('admin')
        ->name('admin.')
        ->middleware('role:admin|super-admin')
        ->group(function () {

            /**
             * |--------------------------------------------------------------------------
             * | Authorization Test Routes
             * |--------------------------------------------------------------------------
             */
            Route::get('/role-test', function () {
                return response()->json([
                    'message' => 'Admin role authorized',
                ]);
            })->name('role-test');

            Route::get('/access-test', function () {
                return response()->json([
                    'message' => 'Authorized',
                ]);
            })
                ->middleware('can:users.manage')
                ->name('access-test');

            /**
             * |--------------------------------------------------------------------------
             * | Settings JSON Endpoints
             * |--------------------------------------------------------------------------
             */
            Route::prefix('settings')
                ->name('settings.')
                ->group(function () {

                    Route::get(
                        '/',
                        [SettingController::class, 'index']
                    )
                        ->middleware('permission:settings.view')
                        ->name('index');

                    Route::post(
                        '/{group}/{key}',
                        [SettingController::class, 'store']
                    )
                        ->middleware('permission:settings.manage')
                        ->name('store');

                    Route::get(
                        '/{group}/{key}',
                        [SettingController::class, 'show']
                    )
                        ->middleware('permission:settings.view')
                        ->name('show');

                    Route::match(
                        ['put', 'patch'],
                        '/{group}/{key}',
                        [SettingController::class, 'update']
                    )
                        ->middleware('permission:settings.manage')
                        ->name('update');

                    Route::delete(
                        '/{group}/{key}',
                        [SettingController::class, 'destroy']
                    )
                        ->middleware('permission:settings.manage')
                        ->name('destroy');
                });

            Route::prefix('features')
                ->name('features.')
                ->middleware('role:super-admin')
                ->group(function () {
                    Route::get(
                        '/',
                        [FeatureControlController::class, 'index']
                    )->name('index');

                    Route::patch(
                        '/{feature}',
                        [FeatureControlController::class, 'update']
                    )
                        ->where('feature', '[a-z][a-z0-9-]*')
                        ->name('update');
                });

            /**
             * Master Data - Countries
             */
            Route::prefix('master-data/countries')
                ->name('master-data.countries.')
                ->group(function () {

                    Route::get(
                        '/',
                        [CountryController::class, 'index']
                    )
                        ->middleware('permission:master-data.view')
                        ->name('index');

                    Route::post(
                        '/',
                        [CountryController::class, 'store']
                    )
                        ->middleware('permission:master-data.manage')
                        ->name('store');

                    Route::get(
                        '/{country}',
                        [CountryController::class, 'show']
                    )
                        ->middleware('permission:master-data.view')
                        ->name('show');

                    Route::match(
                        ['put', 'patch'],
                        '/{country}',
                        [CountryController::class, 'update']
                    )
                        ->middleware('permission:master-data.manage')
                        ->name('update');

                    Route::delete(
                        '/{country}',
                        [CountryController::class, 'destroy']
                    )
                        ->middleware('permission:master-data.manage')
                        ->name('destroy');
                });

            /**
             * Master Data - Categories
             */
            Route::prefix('master-data/categories')
                ->name('master-data.categories.')
                ->group(function () {

                    Route::get(
                        '/',
                        [CategoryController::class, 'index']
                    )
                        ->middleware('permission:master-data.view')
                        ->name('index');

                    Route::post(
                        '/',
                        [CategoryController::class, 'store']
                    )
                        ->middleware('permission:master-data.manage')
                        ->name('store');

                    Route::get(
                        '/{category}',
                        [CategoryController::class, 'show']
                    )
                        ->middleware('permission:master-data.view')
                        ->name('show');

                    Route::match(
                        ['put', 'patch'],
                        '/{category}',
                        [CategoryController::class, 'update']
                    )
                        ->middleware('permission:master-data.manage')
                        ->name('update');

                    Route::delete(
                        '/{category}',
                        [CategoryController::class, 'destroy']
                    )
                        ->middleware('permission:master-data.manage')
                        ->name('destroy');
                });

            /**
             * Master Data - Currencies
             */
            Route::prefix('master-data/currencies')
                ->name('master-data.currencies.')
                ->group(function () {

                    Route::get(
                        '/',
                        [CurrencyController::class, 'index']
                    )
                        ->middleware('permission:master-data.view')
                        ->name('index');

                    Route::post(
                        '/',
                        [CurrencyController::class, 'store']
                    )
                        ->middleware('permission:master-data.manage')
                        ->name('store');

                    Route::get(
                        '/{currency}',
                        [CurrencyController::class, 'show']
                    )
                        ->middleware('permission:master-data.view')
                        ->name('show');

                    Route::match(
                        ['put', 'patch'],
                        '/{currency}',
                        [CurrencyController::class, 'update']
                    )
                        ->middleware('permission:master-data.manage')
                        ->name('update');

                    Route::delete(
                        '/{currency}',
                        [CurrencyController::class, 'destroy']
                    )
                        ->middleware('permission:master-data.manage')
                        ->name('destroy');
                });

            /**
             * Master Data - Languages
             */
            Route::prefix('master-data/languages')
                ->name('master-data.languages.')
                ->group(function () {

                    Route::get('/', [LanguageController::class, 'index'])
                        ->middleware('permission:master-data.view')
                        ->name('index');

                    Route::post('/', [LanguageController::class, 'store'])
                        ->middleware('permission:master-data.manage')
                        ->name('store');

                    Route::get('/{language}', [LanguageController::class, 'show'])
                        ->middleware('permission:master-data.view')
                        ->name('show');

                    Route::match(
                        ['put', 'patch'],
                        '/{language}',
                        [LanguageController::class, 'update']
                    )
                        ->middleware('permission:master-data.manage')
                        ->name('update');

                    Route::delete('/{language}', [LanguageController::class, 'destroy'])
                        ->middleware('permission:master-data.manage')
                        ->name('destroy');
                });

            /**
             * Master Data - Cities
             */
            Route::prefix('master-data/cities')
                ->name('master-data.cities.')
                ->group(function () {

                    Route::get(
                        '/',
                        [CityController::class, 'index']
                    )
                        ->middleware('permission:master-data.view')
                        ->name('index');

                    Route::post(
                        '/',
                        [CityController::class, 'store']
                    )
                        ->middleware('permission:master-data.manage')
                        ->name('store');

                    Route::get(
                        '/{city}',
                        [CityController::class, 'show']
                    )
                        ->middleware('permission:master-data.view')
                        ->name('show');

                    Route::match(
                        ['put', 'patch'],
                        '/{city}',
                        [CityController::class, 'update']
                    )
                        ->middleware('permission:master-data.manage')
                        ->name('update');

                    Route::delete(
                        '/{city}',
                        [CityController::class, 'destroy']
                    )
                        ->middleware('permission:master-data.manage')
                        ->name('destroy');
                });
        });
});

/**
 * |--------------------------------------------------------------------------
 * | Settings Admin UI
 * |--------------------------------------------------------------------------
 */
Route::get(
    '/admin/settings/manage',
    SettingPageController::class
)
    ->middleware([
        'auth',
        'verified',
        'role:admin|super-admin',
        'permission:settings.view',
    ])
    ->name('admin.settings.manage');

Route::post('/flights/bookings/drafts', [FlightBookingDraftController::class, 'store'])
    ->middleware(['auth', 'verified', 'feature:flights,bookings', 'permission:flights.book'])
    ->name('flights.bookings.drafts.store');

Route::post('/flights/bookings/drafts/review', [FlightBookingDraftReviewController::class, 'store'])
    ->middleware(['auth', 'verified', 'feature:flights,bookings', 'permission:flights.book'])
    ->name('flights.bookings.drafts.review');

Route::post(
    '/flights/bookings/confirmation-intents',
    [
        FlightBookingConfirmationIntentController::class,
        'store',
    ],
)
    ->middleware([
        'auth',
        'verified',
        'feature:flights,bookings',
        'permission:flights.book',
    ])
    ->name(
        'flights.bookings.confirmation-intents.store'
    );

Route::get(
    '/flights/bookings/orders/attempts/{attemptReference}',
    [
        FlightOrderAttemptStatusController::class,
        'show',
    ],
)
    ->middleware([
        'auth',
        'verified',
        'feature:flights,bookings',
        'permission:flights.book',
    ])
    ->name(
        'flights.bookings.orders.attempts.show',
    );
Route::post(
    '/flights/bookings/orders/attempts/{attemptReference}/reconcile',
    [
        FlightOrderReconciliationController::class,
        'store',
    ],
)
    ->middleware([
        'auth',
        'verified',
        'feature:flights,bookings',
        'permission:flights.book',
    ])
    ->name(
        'flights.bookings.orders.attempts.reconcile',
    );
Route::post(
    '/flights/bookings/orders/execute',
    [
        FlightOrderExecutionController::class,
        'store',
    ],
)
    ->middleware([
        'auth',
        'verified',
        'feature:flights,bookings',
        'permission:flights.book',
    ])
    ->name(
        'flights.bookings.orders.execute'
    );
Route::get(
    '/flights/bookings/orders/attempts/{attemptReference}/payment-readiness',
    FlightOrderPaymentReadinessController::class,
)
    ->middleware([
        'auth',
        'verified',
        'feature:flights,bookings,payments',
        'permission:flights.book',
    ])
    ->name('flights.bookings.orders.attempts.payment-readiness.show');
Route::post(
    '/flights/bookings/orders/attempts/{attemptReference}/payments',
    FlightOrderPaymentExecutionController::class,
)
    ->middleware([
        'auth',
        'verified',
        'feature:flights,bookings,payments',
        'permission:flights.book',
    ])
    ->name('flights.bookings.orders.attempts.payments.store');

Route::get(
    '/flights/bookings/orders/payments/attempts/{attemptReference}',
    FlightOrderPaymentAttemptStatusController::class,
)
    ->middleware([
        'auth',
        'verified',
        'feature:flights,bookings,payments',
        'permission:flights.book',
    ])
    ->name('flights.bookings.orders.payments.attempts.show');

Route::post(
    '/flights/bookings/orders/payments/attempts/{attemptReference}/reconcile',
    FlightOrderPaymentReconciliationController::class,
)
    ->middleware([
        'auth',
        'verified',
        'feature:flights,bookings,payments',
        'permission:flights.book',
    ])
    ->name('flights.bookings.orders.payments.attempts.reconcile');

Route::get(
    '/flights/bookings/orders/attempts/{attemptReference}/confirmation',
    FlightOrderConfirmationController::class,
)
    ->middleware([
        'auth',
        'verified',
        'feature:flights,bookings',
        'permission:flights.book',
    ])
    ->name('flights.bookings.orders.attempts.confirmation.show');

require __DIR__.'/admin-users-roles.php';

Route::middleware(['auth', 'verified', 'role:admin|super-admin'])
    ->prefix('admin/institutions')->name('admin.institutions.')
    ->group(function () {
        Route::middleware('can:institutions.view')->group(function () {
            Route::get('/', [InstitutionController::class, 'index'])->name('index');
            Route::get('/{institution}', [InstitutionController::class, 'show'])->whereNumber('institution')->name('show');
        });
        Route::middleware('can:institutions.manage')->group(function () {
            Route::get('/create', [InstitutionController::class, 'create'])->name('create');
            Route::post('/', [InstitutionController::class, 'store'])->name('store');
            Route::get('/{institution}/edit', [InstitutionController::class, 'edit'])->whereNumber('institution')->name('edit');
            Route::patch('/{institution}', [InstitutionController::class, 'update'])->whereNumber('institution')->name('update');
            Route::delete('/{institution}', [InstitutionController::class, 'destroy'])->whereNumber('institution')->name('destroy');
        });
    });

Route::middleware(['auth', 'verified', 'role:admin|super-admin'])
    ->prefix('admin/students')->name('admin.students.')
    ->group(function () {
        Route::middleware('can:students.view')->group(function () {
            Route::get('/', [StudentController::class, 'index'])->name('index');
            Route::get('/{student}', [StudentController::class, 'show'])->whereNumber('student')->name('show');
        });
        Route::middleware('can:students.manage')->group(function () {
            Route::get('/create', [StudentController::class, 'create'])->name('create');
            Route::post('/', [StudentController::class, 'store'])->name('store');
            Route::get('/{student}/edit', [StudentController::class, 'edit'])->whereNumber('student')->name('edit');
            Route::patch('/{student}', [StudentController::class, 'update'])->whereNumber('student')->name('update');
            Route::delete('/{student}', [StudentController::class, 'destroy'])->whereNumber('student')->name('destroy');
        });
    });

Route::middleware(['auth', 'verified', 'role:admin|super-admin'])
    ->prefix('admin/affiliates')
    ->name('admin.affiliates.')
    ->group(function () {
        Route::middleware('can:affiliates.view')->group(function () {
            Route::get('/', [AffiliateController::class, 'index'])->name('index');
            Route::get('/{affiliate}', [AffiliateController::class, 'show'])->whereNumber('affiliate')->name('show');
        });
        Route::middleware('can:affiliates.manage')->group(function () {
            Route::get('/create', [AffiliateController::class, 'create'])->name('create');
            Route::post('/', [AffiliateController::class, 'store'])->name('store');
            Route::get('/{affiliate}/edit', [AffiliateController::class, 'edit'])->whereNumber('affiliate')->name('edit');
            Route::patch('/{affiliate}', [AffiliateController::class, 'update'])->whereNumber('affiliate')->name('update');
            Route::delete('/{affiliate}', [AffiliateController::class, 'destroy'])->whereNumber('affiliate')->name('destroy');
        });
    });

Route::middleware(['auth', 'verified', 'role:admin|super-admin'])
    ->prefix('admin/agents')
    ->name('admin.agents.')
    ->group(function () {
        Route::middleware('can:agents.view')->group(function () {
            Route::get('/', [AgentController::class, 'index'])->name('index');
            Route::get('/{agent}', [AgentController::class, 'show'])
                ->whereNumber('agent')
                ->name('show');
        });

        Route::middleware('can:agents.manage')->group(function () {
            Route::get('/create', [AgentController::class, 'create'])->name('create');
            Route::post('/', [AgentController::class, 'store'])->name('store');
            Route::get('/{agent}/edit', [AgentController::class, 'edit'])
                ->whereNumber('agent')
                ->name('edit');
            Route::patch('/{agent}', [AgentController::class, 'update'])
                ->whereNumber('agent')
                ->name('update');
            Route::delete('/{agent}', [AgentController::class, 'destroy'])
                ->whereNumber('agent')
                ->name('destroy');
        });
    });

Route::get(
    '/admin/bookings',
    [BookingPageController::class, 'index']
)
    ->middleware([
        'auth',
        'verified',
        'role:admin|super-admin',
    ])
    ->name('admin.bookings.index');

Route::get(
    '/admin/bookings/{booking}',
    [BookingPageController::class, 'show']
)
    ->middleware([
        'auth',
        'verified',
        'role:admin|super-admin',
    ])
    ->name('admin.bookings.show');

Route::get(
    '/admin/reports',
    [ReportPageController::class, 'index']
)
    ->middleware([
        'auth',
        'verified',
        'role:admin|super-admin',
        'permission:reports.view',
    ])
    ->name('admin.reports.index');

Route::get(
    '/admin/system-logs',
    [SystemLogPageController::class, 'index']
)
    ->middleware([
        'auth',
        'verified',
        'role:super-admin',
        'permission:system-logs.view',
    ])
    ->name('admin.system-logs.index');

Route::get(
    '/admin/categories',
    CategoryPageController::class
)
    ->middleware([
        'auth',
        'verified',
        'role:admin|super-admin',
        'permission:master-data.view',
    ])
    ->name('admin.categories.manage');

Route::get(
    '/admin/currencies',
    CurrencyPageController::class
)
    ->middleware([
        'auth',
        'verified',
        'role:admin|super-admin',
        'permission:master-data.view',
    ])
    ->name('admin.currencies.manage');

Route::get(
    '/admin/languages',
    LanguagePageController::class
)
    ->middleware([
        'auth',
        'verified',
        'role:admin|super-admin',
        'permission:master-data.view',
    ])
    ->name('admin.languages.manage');

Route::get(
    '/admin/master-data',
    MasterDataPageController::class
)
    ->middleware([
        'auth',
        'verified',
        'role:admin|super-admin',
        'permission:master-data.view',
    ])
    ->name('admin.master-data.manage');

/*
 * Destination catalogue (Explore Bangladesh and other destinations).
 *
 * Follows the same admin role and master-data permissions as the other
 * catalogue pages. Only published destinations are rendered publicly.
 */
Route::middleware(['auth', 'verified', 'role:super-admin|admin|admission-consultant|visa-officer|crm-staff|content-editor|seo-manager'])
    ->prefix('admin/destinations')->name('admin.destinations.')
    ->group(function () {
        Route::get('/', [DestinationController::class, 'index'])
            ->middleware('permission:master-data.view')->name('index');
        Route::middleware('permission:master-data.manage|content.manage')->group(function () {
            Route::get('/create', [DestinationController::class, 'create'])->name('create');
            Route::post('/', [DestinationController::class, 'store'])->name('store');
            Route::get('/{destination}/edit', [DestinationController::class, 'edit'])->whereNumber('destination')->name('edit');
            Route::patch('/{destination}', [DestinationController::class, 'update'])->whereNumber('destination')->name('update');
            Route::delete('/{destination}', [DestinationController::class, 'destroy'])->whereNumber('destination')->name('destroy');
            Route::delete('/{destination}/images/{image}', [DestinationController::class, 'destroyImage'])
                ->whereNumber(['destination', 'image'])->name('images.destroy');
        });
    });

Route::get('/explore-bangladesh', [ExploreBangladeshController::class, 'index'])
    ->name('explore-bangladesh.index');
Route::get('/explore-bangladesh/{slug}', [ExploreBangladeshController::class, 'show'])
    ->where('slug', '[a-z0-9]+(?:-[a-z0-9]+)*')->name('explore-bangladesh.show');

/*
 * Manual flight enquiries are separate from supplier reservations and payment.
 * Search providers may be unavailable: no offer, fare or ticket is fabricated.
 */
Route::middleware(['auth', 'verified', 'feature:flights', 'permission:flights.search'])
    ->group(function () {
        Route::get('/flights/enquiries/create', [FlightEnquiryController::class, 'create'])->name('flights.enquiries.create');
        Route::post('/flights/enquiries', [FlightEnquiryController::class, 'store'])
            ->middleware('throttle:10,1')->name('flights.enquiries.store');
        Route::get('/flights/enquiries/{enquiry}', [FlightEnquiryController::class, 'show'])
            ->whereNumber('enquiry')->name('flights.enquiries.show');
    });

Route::middleware(['auth', 'verified', 'role:admin|super-admin'])
    ->prefix('admin/flight-enquiries')->name('admin.flight-enquiries.')
    ->group(function () {
        Route::get('/', [App\Http\Controllers\Admin\FlightEnquiryController::class, 'index'])->name('index');
        Route::get('/{enquiry}', [App\Http\Controllers\Admin\FlightEnquiryController::class, 'show'])
            ->whereNumber('enquiry')->name('show');
        Route::patch('/{enquiry}', [App\Http\Controllers\Admin\FlightEnquiryController::class, 'update'])
            ->whereNumber('enquiry')->name('update');
    });

/*
 * Singapore Student Consultancy.
 *
 * The public page collects consultation enquiries (rate limited, consent
 * required). Students track their own applications; staff manage enquiries
 * and applications. Statuses are manual staff records: no institution or ICA
 * system is called, and no admission or Student's Pass outcome is implied.
 */
/*
 * Study destinations: Malaysia (main) and Singapore. Guides and institutions share
 * one controller each; the route's "country" default selects the destination.
 */
Route::middleware('feature:student-consultancy')->group(function () {
    Route::get('/study-in-malaysia', MalaysiaStudyController::class)->name('consultancy.malaysia');
    Route::get('/study-in-malaysia/guides', SingaporeGuideIndexController::class)->defaults('country', 'MY')->name('consultancy.malaysia.guides.index');
    Route::get('/study-in-malaysia/institutions', InstitutionDirectoryController::class)->defaults('country', 'MY')->name('consultancy.malaysia.institutions.index');
    // Planning tools: each one redirects home until admin has entered the data it needs.
    Route::get('/study-in-malaysia/eligibility', [EligibilityController::class, 'show'])->middleware('has-content')->name('consultancy.malaysia.eligibility');
    Route::post('/study-in-malaysia/eligibility', [EligibilityController::class, 'store'])->middleware('throttle:5,1')->name('consultancy.malaysia.eligibility.store');
    Route::get('/study-in-malaysia/cost-calculator', CostCalculatorController::class)->middleware('has-content')->name('consultancy.malaysia.cost-calculator');
});
Route::get('/study-in-malaysia/guides/{slug}', [ContentPageController::class, 'show'])->where('slug', '[a-z0-9-]+')->name('consultancy.malaysia.guides.show');

Route::get('/study-in-singapore', SingaporeStudyController::class)
    ->middleware('feature:student-consultancy')
    ->name('consultancy.singapore');

Route::get('/study-in-singapore/guides', SingaporeGuideIndexController::class)
    ->middleware('feature:student-consultancy')
    ->name('consultancy.guides.index');

Route::get('/study-in-singapore/institutions', InstitutionDirectoryController::class)
    ->middleware('feature:student-consultancy')
    ->name('consultancy.institutions.index');

Route::get('/travel-services', TravelServicesController::class)->name('travel-services.index');
Route::get('/services', [ServicePageController::class, 'index'])->name('services.index');
Route::get('/services/{slug}', [ServicePageController::class, 'show'])->where('slug', '[a-z]+(?:-[a-z]+)*')->name('services.show');
Route::get('/map', [FeaturePageController::class, 'map'])->middleware('has-content')->name('map');
Route::get('/mobile-app', [FeaturePageController::class, 'mobileApp'])->middleware('has-content')->name('mobile-app');

Route::post('/study-in-singapore/enquiries', [StudentEnquiryController::class, 'store'])
    ->middleware(['feature:student-consultancy', 'throttle:5,1'])
    ->name('consultancy.enquiries.store');

Route::middleware(['auth', 'verified', 'feature:student-consultancy'])
    ->prefix('my-applications')->name('student.applications.')
    ->group(function () {
        Route::get('/', [StudentApplicationController::class, 'index'])->name('index');
        Route::get('/{application}', [StudentApplicationController::class, 'show'])
            ->whereNumber('application')->name('show');
        Route::post('/{application}/documents', [StudentDocumentController::class, 'store'])
            ->whereNumber('application')->middleware('throttle:20,1')->name('documents.store');
        Route::get('/{application}/documents/{document}', [StudentDocumentController::class, 'download'])
            ->whereNumber(['application', 'document'])->name('documents.download');
        Route::delete('/{application}/documents/{document}', [StudentDocumentController::class, 'destroy'])
            ->whereNumber(['application', 'document'])->name('documents.destroy');
        Route::post('/{application}/messages', [StudentApplicationMessageController::class, 'store'])
            ->whereNumber('application')->middleware('throttle:10,1')->name('messages.store');
    });

Route::middleware(['auth', 'verified'])
    ->prefix('notifications')->name('notifications.')
    ->group(function () {
        Route::get('/', [NotificationController::class, 'index'])->name('index');
        Route::post('/read', [NotificationController::class, 'markAllRead'])->name('read');
    });

Route::get('/admin/activity-logs', [ActivityLogController::class, 'index'])
    ->middleware(['auth', 'verified', 'role:admin|super-admin', 'permission:activity-logs.view'])
    ->name('admin.activity-logs.index');

Route::middleware(['auth', 'verified', 'role:super-admin|admin|admission-consultant|visa-officer|crm-staff|content-editor|seo-manager'])
    ->prefix('admin/student-enquiries')->name('admin.student-enquiries.')
    ->group(function () {
        Route::middleware('can:student-enquiries.view')->group(function () {
            Route::get('/', [App\Http\Controllers\Admin\StudentEnquiryController::class, 'index'])->name('index');
            Route::get('/{enquiry}', [App\Http\Controllers\Admin\StudentEnquiryController::class, 'show'])
                ->whereNumber('enquiry')->name('show');
        });
        Route::middleware('can:student-enquiries.manage')->group(function () {
            Route::patch('/{enquiry}', [App\Http\Controllers\Admin\StudentEnquiryController::class, 'update'])
                ->whereNumber('enquiry')->name('update');
            Route::post('/{enquiry}/convert', [App\Http\Controllers\Admin\StudentEnquiryController::class, 'convert'])
                ->whereNumber('enquiry')->middleware('can:student-applications.manage')->name('convert');
        });
    });

Route::middleware(['auth', 'verified', 'role:super-admin|admin|admission-consultant|visa-officer|crm-staff|content-editor|seo-manager'])
    ->prefix('admin/student-applications')->name('admin.student-applications.')
    ->group(function () {
        Route::middleware('can:student-applications.view')->group(function () {
            Route::get('/', [App\Http\Controllers\Admin\StudentApplicationController::class, 'index'])->name('index');
            Route::get('/{application}', [App\Http\Controllers\Admin\StudentApplicationController::class, 'show'])
                ->whereNumber('application')->name('show');
            Route::get('/{application}/documents/{document}', [App\Http\Controllers\Admin\StudentDocumentController::class, 'download'])
                ->whereNumber(['application', 'document'])->name('documents.download');
        });
        Route::patch('/{application}/documents/{document}/review', [App\Http\Controllers\Admin\StudentDocumentController::class, 'review'])
            ->whereNumber(['application', 'document'])->middleware('can:student-documents.verify')->name('documents.review');
        Route::middleware('can:student-applications.manage')->group(function () {
            Route::post('/{application}/documents', [App\Http\Controllers\Admin\StudentDocumentController::class, 'store'])
                ->whereNumber('application')->name('documents.store');
            Route::delete('/{application}/documents/{document}', [App\Http\Controllers\Admin\StudentDocumentController::class, 'destroy'])
                ->whereNumber(['application', 'document'])->name('documents.destroy');
            Route::post('/{application}/messages', [App\Http\Controllers\Admin\StudentApplicationMessageController::class, 'store'])
                ->whereNumber('application')->name('messages.store');
            Route::get('/students/{student}/create', [App\Http\Controllers\Admin\StudentApplicationController::class, 'create'])
                ->whereNumber('student')->name('create');
            Route::post('/students/{student}', [App\Http\Controllers\Admin\StudentApplicationController::class, 'store'])
                ->whereNumber('student')->name('store');
            Route::patch('/{application}', [App\Http\Controllers\Admin\StudentApplicationController::class, 'update'])
                ->whereNumber('application')->name('update');
            Route::post('/{application}/transitions', [App\Http\Controllers\Admin\StudentApplicationController::class, 'transition'])
                ->whereNumber('application')->name('transition');
        });
    });

/*
 * Website CMS: team profiles. Drafts are never rendered publicly.
 */
Route::middleware(['auth', 'verified', 'role:super-admin|admin|admission-consultant|visa-officer|crm-staff|content-editor|seo-manager'])
    ->prefix('admin/team')->name('admin.team-members.')
    ->group(function () {
        Route::get('/', [TeamMemberController::class, 'index'])
            ->middleware('can:content.view')->name('index');
        Route::middleware('can:content.manage')->group(function () {
            Route::get('/create', [TeamMemberController::class, 'create'])->name('create');
            Route::post('/', [TeamMemberController::class, 'store'])->name('store');
            Route::get('/{teamMember}/edit', [TeamMemberController::class, 'edit'])->whereNumber('teamMember')->name('edit');
            Route::patch('/{teamMember}', [TeamMemberController::class, 'update'])->whereNumber('teamMember')->name('update');
            Route::delete('/{teamMember}', [TeamMemberController::class, 'destroy'])->whereNumber('teamMember')->name('destroy');
        });
    });

/*
 * Services and their destination countries (master data).
 */
Route::middleware(['auth', 'verified', 'role:super-admin|admin|admission-consultant|visa-officer|crm-staff|content-editor|seo-manager'])
    ->prefix('admin/service-destinations')->name('admin.service-destinations.')
    ->group(function () {
        Route::get('/', [ServiceDestinationController::class, 'index'])
            ->middleware('permission:master-data.view')->name('index');
        Route::middleware('permission:master-data.manage')->group(function () {
            Route::post('/', [ServiceDestinationController::class, 'store'])->name('store');
            Route::patch('/{serviceDestination}', [ServiceDestinationController::class, 'update'])->whereNumber('serviceDestination')->name('update');
            Route::delete('/{serviceDestination}', [ServiceDestinationController::class, 'destroy'])->whereNumber('serviceDestination')->name('destroy');
        });
    });

/*
 * Service enquiries: the manual fallback for services without a live booking
 * provider. Guests may submit; signed-in customers can follow their own.
 */
Route::get('/enquiries/{service}', [ServiceEnquiryController::class, 'create'])
    ->where('service', '[a-z]+(?:-[a-z]+)*')->name('service-enquiries.create');
Route::post('/enquiries/{service}', [ServiceEnquiryController::class, 'store'])
    ->where('service', '[a-z]+(?:-[a-z]+)*')->middleware('throttle:5,1')->name('service-enquiries.store');

/*
 * Short website lead forms (consultation, quote, contact). Saved in the CRM; the team is emailed.
 */
Route::post('/consultation', [LeadController::class, 'consultation'])->middleware('throttle:5,1')->name('leads.consultation');
Route::post('/quote/{service}', [LeadController::class, 'quote'])->where('service', '[a-z]+(?:-[a-z]+)*')->middleware('throttle:5,1')->name('leads.quote');
Route::post('/contact', [LeadController::class, 'contact'])->middleware('throttle:5,1')->name('leads.contact');
Route::get('/my-enquiries', [ServiceEnquiryController::class, 'mine'])
    ->middleware(['auth', 'verified'])->name('service-enquiries.mine');

Route::middleware(['auth', 'verified', 'role:super-admin|admin|admission-consultant|visa-officer|crm-staff|content-editor|seo-manager'])
    ->prefix('admin/service-enquiries')->name('admin.service-enquiries.')
    ->group(function () {
        Route::middleware('can:service-enquiries.view')->group(function () {
            Route::get('/', [App\Http\Controllers\Admin\ServiceEnquiryController::class, 'index'])->name('index');
            Route::get('/{enquiry}', [App\Http\Controllers\Admin\ServiceEnquiryController::class, 'show'])->whereNumber('enquiry')->name('show');
        });
        Route::patch('/{enquiry}', [App\Http\Controllers\Admin\ServiceEnquiryController::class, 'update'])
            ->whereNumber('enquiry')->middleware('can:service-enquiries.manage')->name('update');
    });

/*
 * Agent portal. Access requires a verified login whose email matches an
 * agent record staff have marked active.
 */
Route::middleware(['auth', 'verified'])
    ->prefix('agent')->name('agent.')
    ->group(function () {
        Route::get('/', [AgentPortalController::class, 'index'])->name('index');
        Route::get('/referrals/create', [AgentPortalController::class, 'create'])->name('referrals.create');
        Route::post('/referrals', [AgentPortalController::class, 'store'])
            ->middleware('throttle:20,1')->name('referrals.store');
    });

/*
 * Offers (staff-written) and reviews (customer-written, moderated).
 */
Route::get('/offers', OfferController::class)->name('offers.index');
// Signed-in customers can still open the page to write the first review.
Route::get('/reviews', [ReviewController::class, 'index'])->middleware('has-content:guests')->name('reviews.index');
Route::post('/reviews', [ReviewController::class, 'store'])
    ->middleware(['auth', 'verified', 'throttle:3,10'])->name('reviews.store');

Route::middleware(['auth', 'verified', 'role:super-admin|admin|admission-consultant|visa-officer|crm-staff|content-editor|seo-manager'])
    ->prefix('admin')->name('admin.')
    ->group(function () {
        Route::get('/offers', [App\Http\Controllers\Admin\OfferController::class, 'index'])->middleware('can:content.view')->name('offers.index');
        Route::middleware('can:content.manage')->group(function () {
            Route::get('/offers/create', [App\Http\Controllers\Admin\OfferController::class, 'create'])->name('offers.create');
            Route::post('/offers', [App\Http\Controllers\Admin\OfferController::class, 'store'])->name('offers.store');
            Route::get('/offers/{offer}/edit', [App\Http\Controllers\Admin\OfferController::class, 'edit'])->whereNumber('offer')->name('offers.edit');
            Route::patch('/offers/{offer}', [App\Http\Controllers\Admin\OfferController::class, 'update'])->whereNumber('offer')->name('offers.update');
            Route::delete('/offers/{offer}', [App\Http\Controllers\Admin\OfferController::class, 'destroy'])->whereNumber('offer')->name('offers.destroy');
            Route::patch('/reviews/{review}', [ReviewModerationController::class, 'update'])->whereNumber('review')->name('reviews.update');
        });
        Route::get('/reviews', [ReviewModerationController::class, 'index'])->middleware('can:content.view')->name('reviews.index');
    });

Route::get('/admin/integrations', [IntegrationController::class, 'index'])
    ->middleware(['auth', 'verified', 'role:admin|super-admin', 'permission:integrations.view'])
    ->name('admin.integrations.index');

Route::post('/admin/integrations/{integration}/test', [IntegrationController::class, 'test'])
    ->where('integration', '[a-z-]+')
    ->middleware(['auth', 'verified', 'role:admin|super-admin', 'permission:integrations.view', 'throttle:6,1'])
    ->name('admin.integrations.test');

/*
 * Back-office modules for staff roles. Route access needs a staff role;
 * permissions and StudentApplicationPolicy decide what each role can do.
 */
Route::middleware(['auth', 'verified', 'role:super-admin|admin|admission-consultant|visa-officer|crm-staff|content-editor|seo-manager'])
    ->prefix('admin')->name('admin.')
    ->group(function () {
        Route::get('/courses', [CourseController::class, 'index'])->middleware('can:courses.view')->name('courses.index');
        Route::get('/eligibility-rules', [EligibilityRuleController::class, 'index'])->middleware('can:courses.view')->name('eligibility-rules.index');
        Route::middleware('can:courses.manage')->group(function () {
            Route::post('/eligibility-rules', [EligibilityRuleController::class, 'store'])->name('eligibility-rules.store');
            Route::get('/eligibility-rules/{rule}/edit', [EligibilityRuleController::class, 'edit'])->whereNumber('rule')->name('eligibility-rules.edit');
            Route::patch('/eligibility-rules/{rule}', [EligibilityRuleController::class, 'update'])->whereNumber('rule')->name('eligibility-rules.update');
            Route::delete('/eligibility-rules/{rule}', [EligibilityRuleController::class, 'destroy'])->whereNumber('rule')->name('eligibility-rules.destroy');
        });
        Route::middleware('can:courses.manage')->group(function () {
            Route::post('/courses', [CourseController::class, 'store'])->name('courses.store');
            Route::get('/courses/{course}/edit', [CourseController::class, 'edit'])->whereNumber('course')->name('courses.edit');
            Route::patch('/courses/{course}', [CourseController::class, 'update'])->whereNumber('course')->name('courses.update');
            Route::post('/courses/{course}/intakes', [CourseController::class, 'storeIntake'])->whereNumber('course')->name('courses.intakes.store');
            Route::patch('/courses/{course}/intakes/{intake}', [CourseController::class, 'updateIntake'])->whereNumber(['course', 'intake'])->name('courses.intakes.update');
        });

        Route::get('/appointments', [AppointmentController::class, 'index'])->middleware('can:appointments.view')->name('appointments.index');
        Route::middleware('can:appointments.manage')->group(function () {
            Route::post('/appointments', [AppointmentController::class, 'store'])->name('appointments.store');
            Route::patch('/appointments/{appointment}', [AppointmentController::class, 'update'])->whereNumber('appointment')->name('appointments.update');
        });

        Route::middleware('can:payments.view')->group(function () {
            Route::get('/payments', [PaymentRecordController::class, 'index'])->name('payments.index');
            Route::get('/payments/{payment}', [PaymentRecordController::class, 'show'])->whereNumber('payment')->name('payments.show');
            Route::get('/payments/{payment}/receipt', [PaymentRecordController::class, 'receipt'])->whereNumber('payment')->name('payments.receipt');
        });
        Route::middleware('can:payments.record')->group(function () {
            Route::get('/payments/create', [PaymentRecordController::class, 'create'])->name('payments.create');
            Route::post('/payments', [PaymentRecordController::class, 'store'])->name('payments.store');
        });
        Route::patch('/payments/{payment}/verify', [PaymentRecordController::class, 'verify'])
            ->whereNumber('payment')->middleware('can:payments.verify')->name('payments.verify');

        Route::middleware('can:seo.manage')->group(function () {
            Route::get('/seo', [SeoController::class, 'index'])->name('seo.index');
            Route::patch('/seo/pages', [SeoController::class, 'updatePage'])->name('seo.pages.update');
            Route::post('/seo/redirects', [SeoController::class, 'storeRedirect'])->name('seo.redirects.store');
            Route::patch('/seo/redirects/{redirect}', [SeoController::class, 'updateRedirect'])->whereNumber('redirect')->name('seo.redirects.update');
            Route::delete('/seo/redirects/{redirect}', [SeoController::class, 'destroyRedirect'])->whereNumber('redirect')->name('seo.redirects.destroy');
        });

        Route::get('/media', [MediaAssetController::class, 'index'])->middleware('can:content.view')->name('media.index');
        Route::middleware('can:content.manage')->group(function () {
            Route::post('/media', [MediaAssetController::class, 'store'])->name('media.store');
            Route::patch('/media/{asset}', [MediaAssetController::class, 'update'])->whereNumber('asset')->name('media.update');
            Route::delete('/media/{asset}', [MediaAssetController::class, 'destroy'])->whereNumber('asset')->name('media.destroy');
            Route::patch('/translations', [TranslationController::class, 'update'])->name('translations.update');
        });
        Route::get('/translations', [TranslationController::class, 'index'])->middleware('can:content.view')->name('translations.index');
    });

Route::post('/my-applications/{application}/appointments', [StudentAppointmentController::class, 'store'])
    ->whereNumber('application')
    ->middleware(['auth', 'verified', 'feature:student-consultancy', 'throttle:5,1'])
    ->name('student.applications.appointments.store');

Route::get('/search', SearchController::class)
    ->middleware('throttle:30,1')->name('search');

/*
 * Published admin-managed content (company, business services, restaurants,
 * inspiration, success stories, Singapore guides). Drafts are never served.
 */
// The company page is merged into About (DESIGN_BRIEF.md); single profiles keep their own URL.
Route::redirect('/company', '/about', 301)->name('company.index');
foreach ([
    'business-services' => ['/business-services', 'business-services'],
    'restaurants' => ['/restaurants', 'restaurants'],
    'inspiration' => ['/get-inspired', 'inspiration'],
    'success-stories' => ['/success-stories', 'success-stories'],
] as $listing => [$path, $name]) {
    // Success stories redirect to the homepage until the first one is published.
    Route::get($path, [ContentPageController::class, 'index'])->defaults('listing', $listing)
        ->middleware($listing === 'success-stories' ? ['has-content'] : [])->name($name.'.index');
}
Route::get('/company/{slug}', [ContentPageController::class, 'show'])->where('slug', '[a-z0-9-]+')->name('company.show');
Route::get('/business-services/projects/{slug}', [ContentPageController::class, 'show'])->where('slug', '[a-z0-9-]+')->name('business-services.projects.show');
Route::get('/business-services/{slug}', [ContentPageController::class, 'show'])->where('slug', '[a-z0-9-]+')->name('business-services.show');
Route::get('/restaurants/{slug}', [ContentPageController::class, 'show'])->where('slug', '[a-z0-9-]+')->name('restaurants.show');
Route::get('/get-inspired/{slug}', [ContentPageController::class, 'show'])->where('slug', '[a-z0-9-]+')->name('inspiration.show');
Route::get('/success-stories/{slug}', [ContentPageController::class, 'show'])->where('slug', '[a-z0-9-]+')->name('success-stories.show');
Route::get('/study-in-singapore/guides/{slug}', [ContentPageController::class, 'show'])->where('slug', '[a-z0-9-]+')->name('consultancy.guides.show');

/*
 * Community forums: anyone may read public categories; posting needs a verified login.
 */
Route::get('/forums', [ForumController::class, 'index'])->name('forums.index');
Route::get('/forums/{category}', [ForumController::class, 'category'])->where('category', '[a-z0-9-]+')->name('forums.category');
Route::get('/forums/{category}/{thread}', [ForumController::class, 'thread'])->where(['category' => '[a-z0-9-]+', 'thread' => '[a-z0-9-]+'])->name('forums.thread');
Route::middleware(['auth', 'verified'])->group(function () {
    Route::post('/forums/{category}', [ForumController::class, 'storeThread'])->where('category', '[a-z0-9-]+')->middleware('throttle:3,10')->name('forums.threads.store');
    Route::post('/forums/{category}/{thread}', [ForumController::class, 'storeReply'])->where(['category' => '[a-z0-9-]+', 'thread' => '[a-z0-9-]+'])->middleware('throttle:6,1')->name('forums.replies.store');
    Route::post('/forum-posts/{post}/report', [ForumController::class, 'report'])->whereNumber('post')->middleware('throttle:10,1')->name('forums.posts.report');

    Route::get('/b2b/register', [PartnerRegistrationController::class, 'create'])->name('b2b.register');
    Route::post('/b2b/register', [PartnerRegistrationController::class, 'store'])->middleware('throttle:3,10')->name('b2b.register.store');
    Route::get('/agent/service-referrals/create', [AgentPortalController::class, 'createServiceReferral'])->name('agent.service-referrals.create');
    Route::post('/agent/service-referrals', [AgentPortalController::class, 'storeServiceReferral'])->middleware('throttle:20,1')->name('agent.service-referrals.store');
});

Route::get('/rewards', RewardController::class)->name('rewards.index');

Route::middleware(['auth', 'verified', 'role:super-admin|admin|admission-consultant|visa-officer|crm-staff|content-editor|seo-manager'])
    ->prefix('admin')->name('admin.')
    ->group(function () {
        Route::get('/content', [ContentEntryController::class, 'index'])->middleware('can:content.view')->name('content.index');
        Route::middleware('can:content.manage')->group(function () {
            Route::get('/content/create', [ContentEntryController::class, 'create'])->name('content.create');
            Route::post('/content', [ContentEntryController::class, 'store'])->name('content.store');
            Route::get('/content/{entry}/edit', [ContentEntryController::class, 'edit'])->whereNumber('entry')->name('content.edit');
            Route::patch('/content/{entry}', [ContentEntryController::class, 'update'])->whereNumber('entry')->name('content.update');
            Route::patch('/content/{entry}/status', [ContentEntryController::class, 'status'])->whereNumber('entry')->name('content.status');
            Route::post('/content/{entry}/restore', [ContentEntryController::class, 'restore'])->whereNumber('entry')->name('content.restore');
            Route::get('/content/{entry}/preview', [ContentEntryController::class, 'preview'])->whereNumber('entry')->name('content.preview');

            Route::get('/contact-directory', [ContactDirectoryController::class, 'index'])->name('contact-directory.index');
            Route::post('/contact-directory', [ContactDirectoryController::class, 'store'])->name('contact-directory.store');
            Route::patch('/contact-directory/{entry}', [ContactDirectoryController::class, 'update'])->whereNumber('entry')->name('contact-directory.update');
            Route::delete('/contact-directory/{entry}', [ContactDirectoryController::class, 'destroy'])->whereNumber('entry')->name('contact-directory.destroy');
        });
        Route::middleware('can:settings.manage')->group(function () {
            Route::get('/business-facts', [SiteFactsController::class, 'edit'])->name('facts.edit');
            Route::patch('/business-facts', [SiteFactsController::class, 'update'])->name('facts.update');
        });
        Route::middleware('can:menus.manage')->group(function () {
            Route::get('/menus', [MenuItemController::class, 'index'])->name('menus.index');
            Route::get('/menus/create', [MenuItemController::class, 'create'])->name('menus.create');
            Route::post('/menus', [MenuItemController::class, 'store'])->name('menus.store');
            Route::patch('/menus/branding', [MenuItemController::class, 'branding'])->name('menus.branding');
            Route::get('/menus/{item}/edit', [MenuItemController::class, 'edit'])->whereNumber('item')->name('menus.edit');
            Route::patch('/menus/{item}', [MenuItemController::class, 'update'])->whereNumber('item')->name('menus.update');
            Route::patch('/menus/{item}/toggle', [MenuItemController::class, 'toggle'])->whereNumber('item')->name('menus.toggle');
            Route::patch('/menus/{item}/move', [MenuItemController::class, 'move'])->whereNumber('item')->name('menus.move');
            Route::delete('/menus/{item}', [MenuItemController::class, 'destroy'])->whereNumber('item')->name('menus.destroy');
        });
        Route::post('/content/{entry}/approve', [ContentEntryController::class, 'approve'])->whereNumber('entry')->middleware('can:content.approve')->name('content.approve');
        Route::middleware('can:content.approve')->group(function () {
            Route::get('/content-approvals', [ContentApprovalController::class, 'index'])->name('content.approvals');
            Route::post('/content-approvals/{entry}/approve', [ContentApprovalController::class, 'approve'])->whereNumber('entry')->name('content.approvals.approve');
            Route::post('/content-approvals/{entry}/reject', [ContentApprovalController::class, 'reject'])->whereNumber('entry')->name('content.approvals.reject');
        });

        Route::get('/upcoming-success', [SuccessPipelineController::class, 'index'])->middleware('can:student-applications.view')->name('success-pipeline.index');
        Route::patch('/upcoming-success/{application}', [SuccessPipelineController::class, 'followUp'])->whereNumber('application')->middleware('can:student-applications.manage')->name('success-pipeline.follow-up');

        Route::middleware('can:rewards.manage')->group(function () {
            Route::get('/rewards', [RewardProgramController::class, 'index'])->name('rewards.index');
            Route::post('/rewards', [RewardProgramController::class, 'store'])->name('rewards.store');
            Route::patch('/rewards/{program}', [RewardProgramController::class, 'update'])->whereNumber('program')->name('rewards.update');
            Route::post('/rewards/adjust', [RewardProgramController::class, 'adjust'])->name('rewards.adjust');
        });

        Route::middleware('can:forums.moderate')->group(function () {
            Route::get('/forums', [ForumModerationController::class, 'index'])->name('forums.index');
            Route::post('/forums/categories', [ForumModerationController::class, 'storeCategory'])->name('forums.categories.store');
            Route::patch('/forums/categories/{category}', [ForumModerationController::class, 'updateCategory'])->whereNumber('category')->name('forums.categories.update');
            Route::patch('/forums/posts/{post}', [ForumModerationController::class, 'moderatePost'])->whereNumber('post')->name('forums.posts.moderate');
            Route::patch('/forums/threads/{thread}/lock', [ForumModerationController::class, 'lockThread'])->whereNumber('thread')->name('forums.threads.lock');
        });
    });

/*
 * Unknown URLs go through the web middleware so the 404 page uses the visitor's language and session.
 */
Route::fallback(static fn () => abort(404));

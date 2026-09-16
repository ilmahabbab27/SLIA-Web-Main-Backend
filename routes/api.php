<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\HeroImageController;
use App\Http\Controllers\HeroButtonController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\AnnualEventController;
use App\Http\Controllers\AwardCategoryController;
use App\Http\Controllers\AwardSettingController;
use App\Http\Controllers\NewsItemController;
use App\Http\Controllers\BoardController;
use App\Http\Controllers\BapPublicationController;
use App\Http\Controllers\BapSettingController;
use App\Http\Controllers\BaeDocumentController;
use App\Http\Controllers\BaeMemberController;
use App\Http\Controllers\BaeSettingController;
use App\Http\Controllers\BomItemController;
use App\Http\Controllers\BomSettingController;
use App\Http\Controllers\FaqController;
use App\Http\Controllers\TickerController;
use App\Http\Controllers\CouncilMemberController;
use App\Http\Controllers\BoardMemberController;
use App\Http\Controllers\ContactEntryController;
use App\Http\Controllers\PublicCalendarEventController;
use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\PabSettingController;
use App\Http\Controllers\PabEventController;
use App\Http\Controllers\PabApplicationController;
use App\Http\Controllers\PabPublicationController;
use App\Http\Controllers\SliaMemberController;
use App\Http\Controllers\ComplaintController;

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('admin/login', [AdminAuthController::class, 'login']);
Route::post('admin/logout', [AdminAuthController::class, 'logout'])->middleware('auth:api');

Route::apiResource('hero-images', HeroImageController::class)->only(['index', 'show']);
Route::apiResource('hero-buttons', HeroButtonController::class)->only(['index', 'show']);
Route::apiResource('events', EventController::class)->only(['index', 'show']);
Route::apiResource('annual-events', AnnualEventController::class)->only(['index', 'show']);
Route::apiResource('award-categories', AwardCategoryController::class)->only(['index', 'show']);
Route::get('award-settings', [AwardSettingController::class, 'show']);
Route::apiResource('news-items', NewsItemController::class)->only(['index', 'show']);
Route::apiResource('boards', BoardController::class)->only(['index', 'show']);
Route::get('bap-settings', [BapSettingController::class, 'show']);
Route::apiResource('bap-publications', BapPublicationController::class)->only(['index', 'show']);
Route::apiResource('bae-documents', BaeDocumentController::class)->only(['index', 'show']);
Route::apiResource('bae-members', BaeMemberController::class)->only(['index', 'show']);
Route::get('bae-settings', [BaeSettingController::class, 'show']);
Route::get('bom-settings', [BomSettingController::class, 'show']);
Route::apiResource('bom-items', BomItemController::class)->only(['index', 'show']);
Route::get('pab-settings', [PabSettingController::class, 'show']);
Route::apiResource('pab-events', PabEventController::class)->only(['index', 'show']);
Route::apiResource('pab-applications', PabApplicationController::class)->only(['index', 'show']);
Route::apiResource('pab-publications', PabPublicationController::class)->only(['index', 'show']);
Route::apiResource('faqs', FaqController::class)->only(['index', 'show']);
Route::apiResource('council-members', CouncilMemberController::class)->only(['index', 'show']);
Route::apiResource('board-members', BoardMemberController::class)->only(['index', 'show']);
Route::apiResource('contact-entries', ContactEntryController::class)->only(['index', 'show']);
Route::apiResource('tickers', TickerController::class)->only(['index', 'show']);
Route::apiResource('public-calendar-events', PublicCalendarEventController::class);
Route::apiResource('slia-members', SliaMemberController::class)->only(['index', 'show']);
Route::post('complaints', [ComplaintController::class, 'store']);
Route::post('member/login', [SliaMemberController::class, 'login']);
Route::post('member/registration-otp', [SliaMemberController::class, 'sendRegistrationOtp']);
Route::post('member/registration-otp/verify', [SliaMemberController::class, 'verifyRegistrationOtp']);
Route::post('member/reset-password', [SliaMemberController::class, 'resetPassword']);
Route::post('member/password-otp', [SliaMemberController::class, 'sendPasswordOtp']);
Route::post('member/password-otp/verify', [SliaMemberController::class, 'verifyPasswordOtp']);
Route::apiResource('slia-members', SliaMemberController::class)->except(['index', 'show']);
Route::post('slia-members/import', [SliaMemberController::class, 'import'])->middleware('throttle:10,1');
Route::get('architects', [SliaMemberController::class, 'getForArchitectFinder']);

Route::middleware('auth:api')->group(function () {
    Route::get('admin/users', [AdminUserController::class, 'index']);
    Route::put('admin/users/{user}', [AdminUserController::class, 'update']);

    Route::apiResource('hero-images', HeroImageController::class)->except(['index', 'show']);
    Route::apiResource('hero-buttons', HeroButtonController::class)->except(['index', 'show']);
    Route::apiResource('events', EventController::class)->except(['index', 'show']);
    Route::apiResource('annual-events', AnnualEventController::class)->except(['index', 'show']);
    Route::apiResource('award-categories', AwardCategoryController::class)->except(['index', 'show']);
    Route::put('award-settings', [AwardSettingController::class, 'update']);
    Route::apiResource('news-items', NewsItemController::class)->except(['index', 'show']);
    // Compatibility endpoint for hosts that do not forward DELETE requests correctly.
    Route::post('news-items/{newsItem}/delete', [NewsItemController::class, 'destroy']);
    Route::apiResource('boards', BoardController::class)->except(['index', 'show']);
    Route::put('bap-settings', [BapSettingController::class, 'update']);
    Route::apiResource('bap-publications', BapPublicationController::class)->except(['index', 'show']);
    Route::apiResource('bae-documents', BaeDocumentController::class)->except(['index', 'show']);
    Route::post('bae-members/import', [BaeMemberController::class, 'import']);
    Route::put('bae-members/bulk-update', [BaeMemberController::class, 'bulkUpdate']);
    Route::apiResource('bae-members', BaeMemberController::class)->except(['index', 'show']);
    Route::put('bae-settings', [BaeSettingController::class, 'update']);
    Route::put('bom-settings', [BomSettingController::class, 'update']);
    Route::apiResource('bom-items', BomItemController::class)->except(['index', 'show']);
    Route::put('pab-settings', [PabSettingController::class, 'update']);
    Route::apiResource('pab-events', PabEventController::class)->except(['index', 'show']);
    Route::apiResource('pab-applications', PabApplicationController::class)->except(['index', 'show']);
    Route::apiResource('pab-publications', PabPublicationController::class)->except(['index', 'show']);
    Route::apiResource('faqs', FaqController::class)->except(['index', 'show']);
    Route::apiResource('council-members', CouncilMemberController::class)->except(['index', 'show']);
    Route::apiResource('board-members', BoardMemberController::class)->except(['index', 'show']);
    Route::apiResource('contact-entries', ContactEntryController::class)->except(['index', 'show']);
    Route::get('complaints', [ComplaintController::class, 'index']);
    Route::patch('complaints/{complaint}', [ComplaintController::class, 'update']);
    Route::get('complaints/{complaint}/download', [ComplaintController::class, 'download']);
    Route::apiResource('tickers', TickerController::class)->except(['index', 'show']);
});

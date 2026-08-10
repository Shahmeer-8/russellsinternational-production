<?php

use App\Http\Controllers\Api\CareerController;
use App\Http\Controllers\Api\ContactController;
use App\Http\Controllers\Api\CourseController;
use App\Http\Controllers\Api\EventController;
use App\Http\Controllers\Api\GalleryController;
use App\Http\Controllers\Api\HeroSlideController;
use App\Http\Controllers\Api\InternshipController;
use App\Http\Controllers\Api\JobController;
use App\Http\Controllers\Api\LanguageProgramController;
use App\Http\Controllers\Api\LanguageSectionController;
use App\Http\Controllers\Api\NavigationController;
use App\Http\Controllers\Api\PageController;
use App\Http\Controllers\Api\PageSectionController;
use App\Http\Controllers\Api\ServiceController;
use App\Http\Controllers\Api\SettingController;
use App\Http\Controllers\Api\StatController;
use App\Http\Controllers\Api\StudyDestinationController;
use App\Http\Controllers\Api\TeamMemberController;
use App\Http\Controllers\Api\TestimonialController;
use App\Http\Controllers\Api\TickerItemController;
use App\Http\Controllers\Api\WhyChooseUsController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public API Routes — Russell's International
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->group(function () {

    // ── Home page data ──────────────────────────────────────────────────
    Route::get('/hero-slides', [HeroSlideController::class,       'index']);
    Route::get('/ticker-items', [TickerItemController::class,       'index']);
    Route::get('/stats', [StatController::class,             'index']);

    // ── Services & Why Choose Us ────────────────────────────────────────
    Route::get('/services', [ServiceController::class,          'index']);
    Route::get('/services/{id}', [ServiceController::class,          'show']);
    Route::get('/why-choose-us', [WhyChooseUsController::class,      'index']);

    // ── Courses (Skills page) ───────────────────────────────────────────
    // ?type=paid|navttc
    Route::get('/courses', [CourseController::class,           'index']);
    Route::get('/courses/{id}', [CourseController::class,           'show']);

    // ── Study Abroad ────────────────────────────────────────────────────
    Route::get('/study-destinations', [StudyDestinationController::class, 'index']);
    Route::get('/study-destinations/{id}', [StudyDestinationController::class, 'show']);

    // ── Languages ───────────────────────────────────────────────────────
    Route::get('/language-sections', [LanguageSectionController::class,  'index']);
    Route::get('/language-programs', [LanguageProgramController::class,  'index']);
    Route::get('/language-programs/{id}', [LanguageProgramController::class,  'show']);

    // ── Careers ─────────────────────────────────────────────────────────
    Route::get('/jobs', [JobController::class,              'index']);
    Route::get('/jobs/{id}', [JobController::class,              'show']);
    Route::get('/internships', [InternshipController::class,       'index']);
    Route::get('/internships/{id}', [InternshipController::class,       'show']);

    // ── Events & News ───────────────────────────────────────────────────
    // ?type=event|news  ?category=Workshop|Seminar|...
    Route::get('/events', [EventController::class,            'index']);
    Route::get('/events/{id}', [EventController::class,            'show']);

    // ── Gallery ──────────────────────────────────────────────────────────
    // ?category=Campus|Training|Events|...
    Route::get('/gallery', [GalleryController::class,          'index']);

    // ── Testimonials ─────────────────────────────────────────────────────
    // ?type=written|video
    Route::get('/testimonials', [TestimonialController::class,      'index']);

    // ── About page ───────────────────────────────────────────────────────
    Route::get('/team', [TeamMemberController::class,       'index']);

    // ── Settings (contact info, social links, footer, SEO) ───────────────
    // ?group=contact|social|footer|seo|general
    Route::get('/settings', [SettingController::class,          'index']);
    Route::get('/settings/{key}', [SettingController::class,          'show']);
    Route::get('/navigation', [NavigationController::class,       'index']);

    // ── SEO / Page meta ──────────────────────────────────────────────────
    Route::get('/pages/{slug}', [PageController::class,             'show']);
    Route::get('/pages/{page}/sections', [PageSectionController::class,   'index']);
    Route::get('/pages/{page}/sections/{section}', [PageSectionController::class, 'show']);

    // ── Form submissions (public POST) ───────────────────────────────────
    Route::post('/contact', [ContactController::class,          'store'])->middleware('throttle:5,1');
    Route::post('/careers/apply', [CareerController::class,           'apply'])->middleware('throttle:5,1');

});

// Fallback for undefined API routes
Route::fallback(function () {
    return response()->json([
        'success' => false,
        'message' => 'API endpoint not found.',
    ], 404);
});

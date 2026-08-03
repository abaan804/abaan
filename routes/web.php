<?php

use App\Http\Controllers\Web\HomeController;
use App\Http\Controllers\Web\PageController;
use App\Http\Controllers\Web\PricingController;
use App\Http\Controllers\Web\SolutionsController;
use App\Http\Controllers\Web\BlogController;
use App\Http\Controllers\Web\ContactController;
use App\Http\Controllers\Web\FaqPageController;
use App\Http\Controllers\Web\LegalController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Onboarding\OnboardingController;

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

Route::get('/locale/{locale}', function (Request $request, string $locale) {
    if (in_array($locale, array_keys(config('abaan.supported_locales')))) {
        $request->session()->put('locale', $locale);

        if ($request->user()) {
            $request->user()->update(['locale' => $locale]);
        }
    }

    return back();
})->name('locale.switch');



Route::get('/suspended', function () {
    return view('auth.suspended');
})->name('suspended');

Route::get('/account-inactive', function () {
    return view('auth.account-inactive');
})->name('account-inactive');

// Route::get('/dashboard', function () {
//     return view('dashboard');
// })->middleware(['auth', 'verified', 'company.selected', 'subscription.check'])->name('dashboard');

Route::middleware(['auth', 'company.selected'])->group(function () {
    Route::get('/onboarding/package', [OnboardingController::class, 'showPackages'])
        ->name('onboarding.package');

    Route::post('/onboarding/package', [OnboardingController::class, 'selectPackage'])
        ->name('onboarding.package.store');
});
 

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/about', [PageController::class, 'about'])->name('about');
Route::get('/services', [PageController::class, 'services'])->name('services');
Route::get('/features', [PageController::class, 'features'])->name('features');
Route::get('/solutions', [SolutionsController::class, 'index'])->name('solutions');
Route::get('/pricing', [PricingController::class, 'index'])->name('pricing');
Route::get('/contact', [ContactController::class, 'index'])->name('contact');
Route::post('/contact', [ContactController::class, 'store'])->name('contact.store');

Route::get('/faq', [FaqPageController::class, 'index'])->name('faq');

Route::get('/blog', [BlogController::class, 'index'])->name('blog.index');
Route::get('/blog/{slug}', [BlogController::class, 'show'])->name('blog.show');

Route::get('/privacy-policy', [LegalController::class, 'privacy'])->name('privacy');
Route::get('/terms-conditions', [LegalController::class, 'terms'])->name('terms');

// Tenant subscription (accessible even when expired)
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/subscription/expired',
        [\App\Http\Controllers\Tenant\SubscriptionController::class, 'expired']
    )->name('subscription.expired');

    Route::get('/subscription/renew',
        [\App\Http\Controllers\Tenant\SubscriptionController::class, 'renew']
    )->name('subscription.renew');

    Route::post('/subscription/renew',
        [\App\Http\Controllers\Tenant\SubscriptionController::class, 'submitRenewalRequest']
    )->name('subscription.renew.submit');

    Route::delete('/subscription/renew/{renewalRequest}/cancel',
        [\App\Http\Controllers\Tenant\SubscriptionController::class, 'cancelRequest']
    )->name('subscription.renew.cancel');
});

 // Temporary placeholders — replaced page-by-page in Step 8 sub-steps
// $sitePlaceholders = [
   
  
//     'privacy' => 'Privacy Policy',
//     'terms' => 'Terms & Conditions',
// ];

// foreach ($sitePlaceholders as $slug => $label) {
//     Route::get("/{$slug}", function () use ($label) {
//         return view('web.placeholder', ['label' => $label]);
//     })->name($slug);
// }



require __DIR__.'/auth.php';
require __DIR__.'/admin.php';
require __DIR__.'/tenant.php';
require __DIR__.'/modules/easykhata.php';
require __DIR__.'/modules/masjid.php';
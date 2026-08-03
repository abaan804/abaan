<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Modules\VideoDownloader\Models\VdDownload;
use Modules\VideoDownloader\Policies\VdDownloadPolicy;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        Gate::policy(\Modules\FamilyTree\Models\FtFamily::class, \Modules\FamilyTree\Policies\FtFamilyPolicy::class);
        Gate::policy(\Modules\FamilyTree\Models\FtMember::class, \Modules\FamilyTree\Policies\FtMemberPolicy::class);
        Gate::policy(\Modules\FamilyTree\Models\FtMarriage::class, \Modules\FamilyTree\Policies\FtMarriagePolicy::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(\Modules\Masjid\Models\MasjidMosque::class, \Modules\Masjid\Policies\MasjidMosquePolicy::class);
        Gate::policy(\Modules\Masjid\Models\MasjidMember::class, \Modules\Masjid\Policies\MasjidMemberPolicy::class);
        Gate::policy(\Modules\Masjid\Models\MasjidPayment::class, \Modules\Masjid\Policies\MasjidPaymentPolicy::class);
        Gate::policy(VdDownload::class, VdDownloadPolicy::class);
    }
}

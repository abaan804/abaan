<?php

return [
    App\Providers\AppServiceProvider::class,
    // App\Providers\Modules\Ledger\LedgerServiceProvider::class,
    Modules\Ledger\LedgerServiceProvider::class,
    Modules\Masjid\MasjidServiceProvider::class,
    Modules\FamilyTree\FamilyTreeServiceProvider::class,
    Modules\VideoDownloader\VideoDownloaderServiceProvider::class,
];

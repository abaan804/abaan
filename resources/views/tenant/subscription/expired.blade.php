<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ in_array(app()->getLocale(), ['ur','ar']) ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('Subscription Expired') }} — {{ config('app.name') }}</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body { background: #f0f4f8; min-height: 100vh; display: flex; align-items: center; }
        .expired-card {
            max-width: 540px; margin: 2rem auto; background: #fff;
            border-radius: 20px; box-shadow: 0 8px 40px rgba(0,0,0,.12);
            overflow: hidden;
        }
        .expired-header {
            background: linear-gradient(135deg, #c0392b, #922b21);
            color: #fff; padding: 2.5rem 2rem; text-align: center;
        }
        .expired-header i { font-size: 3.5rem; margin-bottom: 1rem; opacity: .9; }
    </style>
</head>
<body>
<div class="container">
    <div class="expired-card">
        <div class="expired-header">
            <i class="bi bi-hourglass-bottom d-block"></i>
            <h3 class="fw-bold mb-1">
                @if (session('reason') === 'trial_expired')
                    {{ __('Trial Period Ended') }}
                @else
                    {{ __('Subscription Expired') }}
                @endif
            </h3>
            <p class="mb-0 opacity-75">{{ $company->name }}</p>
        </div>

        <div class="p-4">
            @if (session('reason') === 'trial_expired')
                <div class="alert alert-warning border-0">
                    <i class="bi bi-info-circle"></i>
                    {{ __('Your free trial has ended.') }}
                    @if ($lastSub?->trial_ends_at)
                        {{ __('Trial expired on:') }}
                        <strong>{{ $lastSub->trial_ends_at->format('d M Y') }}</strong>
                    @endif
                </div>
                <p class="text-muted">
                    {{ __('To continue using all modules, please subscribe to a paid plan. Your data is safe and will be accessible once you renew.') }}
                </p>
            @else
                <div class="alert alert-danger border-0">
                    <i class="bi bi-x-circle"></i>
                    {{ __('Your subscription has expired.') }}
                    @if ($lastSub?->ends_at)
                        {{ __('Expired on:') }}
                        <strong>{{ $lastSub->ends_at->format('d M Y') }}</strong>
                    @endif
                </div>
                <p class="text-muted">
                    {{ __('Please renew your subscription to regain access to all features.') }}
                </p>
            @endif

            @if ($lastSub?->package)
                <div class="card border-0 bg-light p-3 mb-4" style="border-radius:12px;">
                    <div class="small text-muted mb-1">{{ __('Last Package') }}</div>
                    <div class="fw-bold">{{ $lastSub->package->name }}</div>
                    <div class="text-muted small">
                        {{ setting('currency_symbol', 'Rs.') }}{{ $lastSub->package->formatted_price }} / {{ __('month') }}
                    </div>
                    <div class="d-flex flex-wrap gap-1 mt-2">
                        @foreach ($lastSub->package->moduleDefinitions as $m)
                            <span class="badge bg-white border text-dark small">
                                <i class="bi {{ $m->icon }}"></i> {{ $m->name_en }}
                            </span>
                        @endforeach
                    </div>
                </div>
            @endif

            <div class="d-grid gap-2">
                <a href="{{ route('subscription.renew') }}" class="btn btn-primary btn-lg">
                    <i class="bi bi-arrow-repeat"></i>
                    {{ __('Renew Subscription') }}
                </a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="btn btn-outline-secondary w-100">
                        <i class="bi bi-box-arrow-right"></i> {{ __('Sign Out') }}
                    </button>
                </form>
            </div>

            <p class="text-muted small text-center mt-3 mb-0">
                {{ __('Need help? Contact') }}
                <a href="mailto:{{ setting('support_email', 'support@abaan.com') }}">
                    {{ setting('support_email', 'support@abaan.com') }}
                </a>
            </p>
        </div>
    </div>
</div>
</body>
</html>
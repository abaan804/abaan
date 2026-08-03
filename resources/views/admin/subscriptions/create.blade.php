@extends('layouts.admin')
@section('title', __('Assign Subscription'))

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between mb-4">
        <h4>{{ __('Assign Subscription') }}</h4>
        <a href="{{ route('admin.subscriptions.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left"></i> {{ __('Back') }}
        </a>
    </div>

    <div class="row g-4">
        <div class="col-12 col-lg-7">
            <div class="card border-0 shadow-sm" style="border-radius:14px;">
                <div class="card-body">
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            @foreach ($errors->all() as $e)
                                <div>{{ $e }}</div>
                            @endforeach
                        </div>
                    @endif

                    <form method="POST" action="{{ route('admin.subscriptions.store') }}" id="sub-form">
                        @csrf
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label">{{ __('Company') }} <span class="text-danger">*</span></label>
                                <select name="company_id" id="sub-company" class="form-select" required>
                                    <option value="">{{ __('— Select Company —') }}</option>
                                    @foreach ($companies as $company)
                                        <option value="{{ $company->id }}"
                                                data-trial-used="{{ $company->trial_used ? 1 : 0 }}"
                                                {{ old('company_id') == $company->id ? 'selected' : '' }}>
                                            {{ $company->name }}
                                            @if ($company->trial_used)
                                                ({{ __('Trial Used') }})
                                            @endif
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-12">
                                <label class="form-label">{{ __('Package') }} <span class="text-danger">*</span></label>
                                <select name="package_id" id="sub-package" class="form-select" required>
                                    <option value="">{{ __('— Select Package —') }}</option>
                                    @foreach ($packages as $package)
                                        <option value="{{ $package->id }}"
                                                data-price="{{ $package->monthly_price }}"
                                                data-trial-days="{{ $package->trial_days }}"
                                                data-has-trial="{{ $package->has_trial ? 1 : 0 }}"
                                                {{ old('package_id') == $package->id ? 'selected' : '' }}>
                                            {{ $package->name }}
                                            — {{ setting('currency_symbol', 'Rs.') }}{{ $package->formatted_price }}/mo
                                            @if ($package->trial_days > 0)
                                                ({{ $package->trial_days }} {{ __('day trial') }})
                                            @endif
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Package info box --}}
                            <div id="package-info-box" class="col-12 d-none">
                                <div class="alert alert-info border-0 mb-0">
                                    <div class="fw-semibold mb-2" id="pkg-info-name"></div>
                                    <div class="d-flex flex-wrap gap-2" id="pkg-info-modules"></div>
                                </div>
                            </div>

                            {{-- Trial option --}}
                            <div class="col-12" id="trial-option-row">
                                <div class="card border-0 bg-light p-3" style="border-radius:10px;">
                                    <div class="form-check form-switch mb-2">
                                        <input class="form-check-input" type="checkbox"
                                               name="use_trial" id="use-trial"
                                               value="1" {{ old('use_trial') ? 'checked' : '' }}>
                                        <label class="form-check-label fw-semibold" for="use-trial">
                                            <i class="bi bi-hourglass-split text-info"></i>
                                            {{ __('Start with Trial Period') }}
                                        </label>
                                    </div>
                                    <div id="trial-info" class="small text-muted">
                                        {{ __('Enable to give the company free access during the trial period. Each company can only use one trial ever.') }}
                                    </div>
                                    <div id="trial-warning" class="alert alert-warning small py-2 mb-0 mt-2 d-none">
                                        <i class="bi bi-exclamation-triangle"></i>
                                        {{ __('This company has already used their trial and cannot trial again.') }}
                                    </div>
                                    <div id="no-trial-warning" class="alert alert-secondary small py-2 mb-0 mt-2 d-none">
                                        <i class="bi bi-info-circle"></i>
                                        {{ __('This package does not include a trial period.') }}
                                    </div>
                                </div>
                            </div>

                            {{-- Billing (shown when not using trial) --}}
                            <div class="col-md-6" id="billing-months-row">
                                <label class="form-label">{{ __('Billing Months') }} <span class="text-danger">*</span></label>
                                <select name="billing_months" id="sub-months" class="form-select">
                                    @foreach ([1,2,3,6,12] as $m)
                                        <option value="{{ $m }}" {{ old('billing_months', 1) == $m ? 'selected' : '' }}>
                                            {{ $m }} {{ $m === 1 ? __('month') : __('months') }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6" id="price-paid-row">
                                <label class="form-label">{{ __('Price Paid') }}</label>
                                <div class="input-group">
                                    <span class="input-group-text">{{ setting('currency_symbol', 'Rs.') }}</span>
                                    <input type="number" name="price_paid" id="sub-price"
                                           step="0.01" min="0"
                                           value="{{ old('price_paid') }}"
                                           class="form-control"
                                           placeholder="{{ __('Auto-calculated') }}">
                                </div>
                            </div>

                            <div class="col-12">
                                <label class="form-label">{{ __('Notes') }}</label>
                                <textarea name="notes" rows="2" class="form-control"
                                          placeholder="{{ __('Payment reference, remarks...') }}">{{ old('notes') }}</textarea>
                            </div>
                        </div>

                        {{-- Summary box --}}
                        <div id="sub-summary" class="alert alert-light border mt-4 d-none">
                            <h6 class="fw-bold mb-3">{{ __('Subscription Summary') }}</h6>
                            <div id="summary-content"></div>
                        </div>

                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary px-4">
                                <i class="bi bi-check-lg"></i> {{ __('Assign Subscription') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
(function () {
    const companySelect  = document.getElementById('sub-company');
    const packageSelect  = document.getElementById('sub-package');
    const useTrialCheck  = document.getElementById('use-trial');
    const monthsRow      = document.getElementById('billing-months-row');
    const priceRow       = document.getElementById('price-paid-row');
    const trialWarning   = document.getElementById('trial-warning');
    const noTrialWarning = document.getElementById('no-trial-warning');
    const summaryBox     = document.getElementById('sub-summary');
    const summaryContent = document.getElementById('summary-content');
    const pkgInfoBox     = document.getElementById('package-info-box');

    function getSelectedPackage() {
        return packageSelect.options[packageSelect.selectedIndex];
    }
    function getSelectedCompany() {
        return companySelect.options[companySelect.selectedIndex];
    }

    function updateForm() {
        const pkg         = getSelectedPackage();
        const company     = getSelectedCompany();
        const usingTrial  = useTrialCheck.checked;
        const hasTrial    = pkg?.dataset.hasTrial === '1';
        const trialDays   = parseInt(pkg?.dataset.trialDays || 0);
        const price       = parseFloat(pkg?.dataset.price || 0);
        const months      = parseInt(document.getElementById('sub-months').value || 1);
        const trialUsed   = company?.dataset.trialUsed === '1';

        // Show/hide trial warnings
        trialWarning.classList.toggle('d-none', !trialUsed);
        noTrialWarning.classList.toggle('d-none', hasTrial || !pkg.value);

        // Disable trial checkbox if not eligible
        useTrialCheck.disabled = trialUsed || !hasTrial;
        if (trialUsed || !hasTrial) {
            useTrialCheck.checked = false;
        }

        // Show/hide billing fields
        const showBilling = !usingTrial || !hasTrial;
        monthsRow.style.display = showBilling ? '' : 'none';
        priceRow.style.display  = showBilling ? '' : 'none';

        // Package info modules
        if (pkg?.value) {
            const opts = packageSelect.selectedOptions[0];
            document.getElementById('pkg-info-name').textContent = opts.text;
            pkgInfoBox.classList.remove('d-none');
        } else {
            pkgInfoBox.classList.add('d-none');
        }

        // Summary
        if (!pkg?.value || !company?.value) {
            summaryBox.classList.add('d-none');
            return;
        }

        summaryBox.classList.remove('d-none');

        if (usingTrial && hasTrial && !trialUsed) {
            const ends = new Date();
            ends.setDate(ends.getDate() + trialDays);
            summaryContent.innerHTML = `
                <div class="d-flex justify-content-between border-bottom py-1">
                    <span>{{ __('Company') }}</span><strong>${company.text}</strong>
                </div>
                <div class="d-flex justify-content-between border-bottom py-1">
                    <span>{{ __('Package') }}</span><strong>${pkg.text.split('—')[0].trim()}</strong>
                </div>
                <div class="d-flex justify-content-between border-bottom py-1">
                    <span>{{ __('Type') }}</span>
                    <span class="badge bg-info text-dark">{{ __('Trial') }}</span>
                </div>
                <div class="d-flex justify-content-between border-bottom py-1">
                    <span>{{ __('Trial Duration') }}</span><strong>${trialDays} {{ __('days') }}</strong>
                </div>
                <div class="d-flex justify-content-between py-1">
                    <span>{{ __('Trial Ends') }}</span>
                    <strong>${ends.toLocaleDateString('en-GB', {day:'2-digit',month:'short',year:'numeric'})}</strong>
                </div>
                <div class="alert alert-warning small py-2 mt-2 mb-0">
                    <i class="bi bi-exclamation-triangle"></i>
                    {{ __('After this trial, the company cannot trial again. They must pay to continue.') }}
                </div>`;
        } else {
            const total = (price * months).toFixed(2);
            summaryContent.innerHTML = `
                <div class="d-flex justify-content-between border-bottom py-1">
                    <span>{{ __('Company') }}</span><strong>${company.text}</strong>
                </div>
                <div class="d-flex justify-content-between border-bottom py-1">
                    <span>{{ __('Package') }}</span><strong>${pkg.text.split('—')[0].trim()}</strong>
                </div>
                <div class="d-flex justify-content-between border-bottom py-1">
                    <span>{{ __('Type') }}</span>
                    <span class="badge bg-success">{{ __('Paid') }}</span>
                </div>
                <div class="d-flex justify-content-between border-bottom py-1">
                    <span>{{ __('Duration') }}</span><strong>${months} {{ __('month(s)') }}</strong>
                </div>
                <div class="d-flex justify-content-between py-1 fw-bold">
                    <span>{{ __('Total') }}</span>
                    <span class="text-success">{{ setting('currency_symbol', 'Rs.') }}${total}</span>
                </div>`;

            // Auto-fill price
            const priceField = document.getElementById('sub-price');
            if (!priceField.value || priceField.dataset.auto === '1') {
                priceField.value = total;
                priceField.dataset.auto = '1';
            }
        }
    }

    companySelect.addEventListener('change', updateForm);
    packageSelect.addEventListener('change', updateForm);
    useTrialCheck.addEventListener('change', updateForm);
    document.getElementById('sub-months').addEventListener('change', updateForm);

    updateForm();
})();
</script>
@endpush
@endsection
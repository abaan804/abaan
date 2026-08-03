<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ in_array(app()->getLocale(),['ur','ar']) ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('Renew Subscription') }} — {{ config('app.name') }}</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body { background: #f0f4f8; }
        .top-bar {
            background: linear-gradient(135deg, #1a3a5c, #2c5282);
            color: #fff; padding: 1.25rem 2rem;
        }
        .pkg-card {
            border: 2px solid #e5e7eb; border-radius: 14px;
            cursor: pointer; transition: all .18s; position: relative;
        }
        .pkg-card:hover   { border-color: #1a3a5c; background: #f8fafd; }
        .pkg-card.selected {
            border-color: #1a3a5c; background: #eaf2f8;
            box-shadow: 0 0 0 3px rgba(26,58,92,.15);
        }
        .pkg-price { font-size: 1.9rem; font-weight: 800; color: #1a3a5c; }
        .check-mark {
            position: absolute; top: .75rem; right: .75rem;
            width: 26px; height: 26px; border-radius: 50%;
            background: #1a3a5c; color: #fff;
            display: none; align-items: center;
            justify-content: center; font-size: .85rem;
        }
        .pkg-card.selected .check-mark { display: flex; }

        .step-badge {
            display: inline-flex; align-items: center; justify-content: center;
            width: 30px; height: 30px; border-radius: 50%;
            background: #1a3a5c; color: #fff;
            font-size: .85rem; font-weight: 700; flex-shrink: 0;
        }
        .upload-zone {
            border: 2px dashed #b8c9d9; border-radius: 12px;
            padding: 2rem; text-align: center;
            cursor: pointer; transition: border-color .2s, background .2s;
        }
        .upload-zone:hover, .upload-zone.drag-over {
            border-color: #1a3a5c; background: #eaf2f8;
        }
        .preview-img {
            max-width: 100%; max-height: 280px;
            border-radius: 10px; border: 2px solid #b8c9d9;
            margin-top: .75rem;
        }
        .pending-badge {
            background: linear-gradient(135deg, #fef3c7, #fde68a);
            border: 1px solid #f59e0b; border-radius: 14px;
        }
    </style>
</head>
<body>

{{-- Top Bar --}}
<div class="top-bar d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-0">{{ __('Renew Subscription') }}</h4>
        <div class="opacity-75 small">{{ $company->name }}</div>
    </div>
    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit" class="btn btn-outline-light btn-sm">
            <i class="bi bi-box-arrow-right"></i> {{ __('Sign Out') }}
        </button>
    </form>
</div>

<div class="container pb-5" style="max-width:820px;">

    @if (session('success'))
        <div class="alert alert-success border-0 shadow-sm mb-4">
            <i class="bi bi-check-circle-fill"></i> {{ session('success') }}
        </div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger border-0 mb-4">
            <i class="bi bi-x-circle"></i> {{ session('error') }}
        </div>
    @endif

    {{-- ── Pending Request Banner ──────────────────────────────────────────── --}}
    @if ($pendingRequest)
        <div class="pending-badge p-4 mb-4">
            <div class="d-flex align-items-center gap-3 mb-3">
                <div class="fs-2">⏳</div>
                <div>
                    <h5 class="fw-bold mb-1">{{ __('Request Under Review') }}</h5>
                    <p class="text-muted mb-0 small">
                        {{ __('Your renewal request has been submitted and is awaiting admin review.') }}
                    </p>
                </div>
            </div>

            <div class="card border-0 bg-white p-3 mb-3" style="border-radius:10px;">
                <div class="row g-2">
                    <div class="col-6 col-md-3">
                        <div class="text-muted small">{{ __('Package') }}</div>
                        <div class="fw-semibold">{{ $pendingRequest->package->name }}</div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="text-muted small">{{ __('Duration') }}</div>
                        <div class="fw-semibold">
                            {{ $pendingRequest->billing_months }} {{ __('month(s)') }}
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="text-muted small">{{ __('Amount') }}</div>
                        <div class="fw-semibold text-success">
                            {{ setting('currency_symbol','Rs.') }}{{ number_format($pendingRequest->amount, 2) }}
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="text-muted small">{{ __('Submitted') }}</div>
                        <div class="fw-semibold">{{ $pendingRequest->created_at->format('d M Y, H:i') }}</div>
                    </div>
                    @if ($pendingRequest->transaction_id)
                        <div class="col-12">
                            <div class="text-muted small">{{ __('Transaction ID') }}</div>
                            <div class="fw-semibold">{{ $pendingRequest->transaction_id }}</div>
                        </div>
                    @endif
                    @if ($pendingRequest->note)
                        <div class="col-12">
                            <div class="text-muted small">{{ __('Your Note') }}</div>
                            <div class="small">{{ $pendingRequest->note }}</div>
                        </div>
                    @endif
                </div>
            </div>

            <div class="d-flex gap-2 flex-wrap">
                <div class="badge bg-warning text-dark p-2 fs-6">
                    <i class="bi bi-clock"></i> {{ __('Pending Review') }}
                </div>
                <form method="POST"
                      action="{{ route('subscription.renew.cancel', $pendingRequest) }}"
                      onsubmit="return confirm('{{ __('Cancel this renewal request?') }}')">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-outline-danger">
                        <i class="bi bi-x-circle"></i> {{ __('Cancel Request') }}
                    </button>
                </form>
            </div>
        </div>

        <div class="alert alert-light border text-center">
            <i class="bi bi-info-circle text-primary"></i>
            {{ __('Once reviewed, your subscription will be activated automatically. You will be able to access all modules shortly.') }}
        </div>

    @else

    {{-- ── Payment Details Box ─────────────────────────────────────────────── --}}
    @if (array_filter($paymentDetails))
        <div class="card border-0 shadow-sm mb-4" style="border-radius:14px;">
            <div class="card-header bg-white border-0">
                <strong><i class="bi bi-bank text-primary"></i> {{ __('Payment Details') }}</strong>
                <div class="text-muted small mt-1">
                    {{ __('Send payment to one of the following accounts, then upload your screenshot below.') }}
                </div>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    @if ($paymentDetails['bank_name'] || $paymentDetails['account_number'])
                        <div class="col-12 col-md-6">
                            <div class="p-3 rounded" style="background:#f8fafd;border:1px solid #dbeafe;">
                                <div class="text-muted small fw-semibold mb-2">
                                    <i class="bi bi-building-fill text-primary"></i> {{ __('Bank Transfer') }}
                                </div>
                                @if ($paymentDetails['bank_name'])
                                    <div class="d-flex justify-content-between py-1">
                                        <span class="text-muted small">{{ __('Bank') }}</span>
                                        <span class="fw-semibold small">{{ $paymentDetails['bank_name'] }}</span>
                                    </div>
                                @endif
                                @if ($paymentDetails['account_title'])
                                    <div class="d-flex justify-content-between py-1">
                                        <span class="text-muted small">{{ __('Account Title') }}</span>
                                        <span class="fw-semibold small">{{ $paymentDetails['account_title'] }}</span>
                                    </div>
                                @endif
                                @if ($paymentDetails['account_number'])
                                    <div class="d-flex justify-content-between py-1 align-items-center">
                                        <span class="text-muted small">{{ __('Account No') }}</span>
                                        <span class="fw-bold font-monospace">
                                            {{ $paymentDetails['account_number'] }}
                                            <button type="button"
                                                    class="btn btn-xs btn-outline-secondary btn-sm ms-1"
                                                    onclick="copyText('{{ $paymentDetails['account_number'] }}')"
                                                    title="{{ __('Copy') }}">
                                                <i class="bi bi-copy" style="font-size:.7rem;"></i>
                                            </button>
                                        </span>
                                    </div>
                                @endif
                                @if ($paymentDetails['iban'])
                                    <div class="d-flex justify-content-between py-1 align-items-center">
                                        <span class="text-muted small">{{ __('IBAN') }}</span>
                                        <span class="fw-bold font-monospace small">
                                            {{ $paymentDetails['iban'] }}
                                            <button type="button"
                                                    class="btn btn-xs btn-outline-secondary btn-sm ms-1"
                                                    onclick="copyText('{{ $paymentDetails['iban'] }}')"
                                                    title="{{ __('Copy') }}">
                                                <i class="bi bi-copy" style="font-size:.7rem;"></i>
                                            </button>
                                        </span>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif

                    @if ($paymentDetails['jazzcash'] || $paymentDetails['easypaisa'])
                        <div class="col-12 col-md-6">
                            @if ($paymentDetails['jazzcash'])
                                <div class="p-3 rounded mb-2"
                                     style="background:#fff5f5;border:1px solid #fed7d7;">
                                    <div class="text-muted small fw-semibold mb-1">
                                        <i class="bi bi-phone-fill" style="color:#e11d48;"></i> JazzCash
                                    </div>
                                    <div class="fw-bold font-monospace d-flex justify-content-between align-items-center">
                                        {{ $paymentDetails['jazzcash'] }}
                                        <button type="button"
                                                class="btn btn-xs btn-outline-secondary btn-sm"
                                                onclick="copyText('{{ $paymentDetails['jazzcash'] }}')"
                                                title="{{ __('Copy') }}">
                                            <i class="bi bi-copy" style="font-size:.7rem;"></i>
                                        </button>
                                    </div>
                                </div>
                            @endif
                            @if ($paymentDetails['easypaisa'])
                                <div class="p-3 rounded"
                                     style="background:#f0fdf4;border:1px solid #bbf7d0;">
                                    <div class="text-muted small fw-semibold mb-1">
                                        <i class="bi bi-phone-fill" style="color:#16a34a;"></i> EasyPaisa
                                    </div>
                                    <div class="fw-bold font-monospace d-flex justify-content-between align-items-center">
                                        {{ $paymentDetails['easypaisa'] }}
                                        <button type="button"
                                                class="btn btn-xs btn-outline-secondary btn-sm"
                                                onclick="copyText('{{ $paymentDetails['easypaisa'] }}')"
                                                title="{{ __('Copy') }}">
                                            <i class="bi bi-copy" style="font-size:.7rem;"></i>
                                        </button>
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endif

    {{-- ── Renewal Form ────────────────────────────────────────────────────── --}}
    <form method="POST"
          action="{{ route('subscription.renew.submit') }}"
          enctype="multipart/form-data"
          id="renewal-form">
        @csrf

        @if ($errors->any())
            <div class="alert alert-danger border-0 mb-4">
                @foreach ($errors->all() as $e)
                    <div><i class="bi bi-x-circle"></i> {{ $e }}</div>
                @endforeach
            </div>
        @endif

        {{-- Step 1: Select Plan --}}
        <div class="d-flex align-items-center gap-2 mb-3">
            <span class="step-badge">1</span>
            <h5 class="fw-bold mb-0">{{ __('Select a Plan') }}</h5>
        </div>

        <div class="row g-3 mb-4">
            @foreach ($packages as $package)
                <div class="col-12 col-md-6">
                    <div class="pkg-card p-3 h-100 {{ $loop->first ? 'selected' : '' }}"
                         onclick="selectPackage(this)"
                         data-pkg-id="{{ $package->id }}"
                         data-price="{{ $package->monthly_price }}">
                        <div class="check-mark"><i class="bi bi-check2"></i></div>
                        <div class="fw-bold fs-5 mb-1">{{ $package->name }}</div>
                        <div class="pkg-price">
                            {{ setting('currency_symbol','Rs.') }}{{ $package->formatted_price }}
                            <span class="fs-6 fw-normal text-muted">/ {{ __('month') }}</span>
                        </div>
                        @if ($package->description)
                            <p class="text-muted small mt-1 mb-2">{{ $package->description }}</p>
                        @endif
                        <div class="d-flex flex-wrap gap-1 mt-2">
                            @foreach ($package->moduleDefinitions as $m)
                                <span class="badge bg-light text-dark border small">
                                    <i class="bi {{ $m->icon }}"></i> {{ $m->name_en }}
                                </span>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        <input type="hidden" name="package_id" id="selected-pkg-id"
               value="{{ $packages->first()?->id }}">

        {{-- Step 2: Duration + Amount --}}
        <div class="d-flex align-items-center gap-2 mb-3">
            <span class="step-badge">2</span>
            <h5 class="fw-bold mb-0">{{ __('Select Duration') }}</h5>
        </div>

        <div class="card border-0 shadow-sm mb-4" style="border-radius:14px;">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">{{ __('Billing Period') }} <span class="text-danger">*</span></label>
                        <select name="billing_months" id="billing-months" class="form-select" required>
                            @foreach ([1 => '1 Month', 2 => '2 Months', 3 => '3 Months', 6 => '6 Months', 12 => '12 Months (1 Year)'] as $m => $label)
                                <option value="{{ $m }}" {{ old('billing_months',1) == $m ? 'selected' : '' }}>
                                    {{ __($label) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6 d-flex align-items-end">
                        <div class="w-100 p-3 rounded text-center"
                             style="background:#f0fdf4;border:2px solid #22c55e;">
                            <div class="text-muted small">{{ __('Total Amount to Pay') }}</div>
                            <div class="h3 fw-bold text-success mb-0" id="total-amount">
                                {{ setting('currency_symbol','Rs.') }}<span id="amount-val">0.00</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Step 3: Payment Method --}}
        <div class="d-flex align-items-center gap-2 mb-3">
            <span class="step-badge">3</span>
            <h5 class="fw-bold mb-0">{{ __('Payment Details') }}</h5>
        </div>

        <div class="card border-0 shadow-sm mb-4" style="border-radius:14px;">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">{{ __('Payment Method') }} <span class="text-danger">*</span></label>
                        <select name="payment_method" class="form-select" required>
                            <option value="">{{ __('— Select Method —') }}</option>
                            <option value="Bank Transfer"  {{ old('payment_method') === 'Bank Transfer'  ? 'selected' : '' }}>{{ __('Bank Transfer') }}</option>
                            <option value="JazzCash"       {{ old('payment_method') === 'JazzCash'       ? 'selected' : '' }}>JazzCash</option>
                            <option value="EasyPaisa"      {{ old('payment_method') === 'EasyPaisa'      ? 'selected' : '' }}>EasyPaisa</option>
                            <option value="Cash"           {{ old('payment_method') === 'Cash'           ? 'selected' : '' }}>{{ __('Cash') }}</option>
                            <option value="Other"          {{ old('payment_method') === 'Other'          ? 'selected' : '' }}>{{ __('Other') }}</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">{{ __('Transaction ID / Reference') }}</label>
                        <input type="text" name="transaction_id"
                               value="{{ old('transaction_id') }}"
                               class="form-control"
                               placeholder="{{ __('e.g. TXN123456789') }}">
                    </div>
                    <div class="col-12">
                        <label class="form-label">{{ __('Note') }} <span class="text-muted small">({{ __('optional') }})</span></label>
                        <textarea name="note" rows="2" class="form-control"
                                  placeholder="{{ __('Any additional information for admin...') }}">{{ old('note') }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        {{-- Step 4: Upload Screenshot --}}
        <div class="d-flex align-items-center gap-2 mb-3">
            <span class="step-badge">4</span>
            <h5 class="fw-bold mb-0">{{ __('Upload Payment Screenshot') }}</h5>
        </div>

        <div class="card border-0 shadow-sm mb-4" style="border-radius:14px;">
            <div class="card-body">
                <input type="file" name="payment_screenshot" id="screenshot-input"
                       accept="image/*,.pdf" class="d-none" required>

                <div class="upload-zone" id="upload-zone"
                     onclick="document.getElementById('screenshot-input').click()"
                     ondrop="handleDrop(event)"
                     ondragover="handleDragOver(event)"
                     ondragleave="handleDragLeave(event)">
                    <div id="upload-placeholder">
                        <i class="bi bi-cloud-arrow-up fs-1 text-muted d-block mb-2"></i>
                        <div class="fw-semibold">{{ __('Click to upload or drag & drop') }}</div>
                        <div class="text-muted small mt-1">
                            {{ __('JPG, PNG or PDF — Max 5MB') }}
                        </div>
                    </div>
                    <div id="upload-preview" class="d-none">
                        <img id="preview-img" src="" class="preview-img" alt="{{ __('Payment Screenshot') }}">
                        <div id="pdf-preview" class="d-none text-center mt-3">
                            <i class="bi bi-file-earmark-pdf fs-1 text-danger"></i>
                            <div class="fw-semibold mt-1" id="pdf-filename"></div>
                        </div>
                        <div class="mt-2">
                            <button type="button" class="btn btn-sm btn-outline-secondary"
                                    onclick="clearUpload(event)">
                                <i class="bi bi-x"></i> {{ __('Remove') }}
                            </button>
                        </div>
                    </div>
                </div>

                @error('payment_screenshot')
                    <div class="text-danger small mt-2">{{ $message }}</div>
                @enderror
            </div>
        </div>

        {{-- Submit --}}
        <button type="submit" class="btn btn-primary btn-lg w-100" id="submit-btn">
            <i class="bi bi-send-fill"></i>
            {{ __('Submit Renewal Request') }}
        </button>

        <p class="text-muted small text-center mt-3">
            <i class="bi bi-shield-check text-success"></i>
            {{ __('Your payment screenshot is stored securely and only visible to our admin team.') }}
        </p>
    </form>

    @endif {{-- end if pendingRequest --}}

</div>

<script>
// ── Package selection ──────────────────────────────────────────────────────
const packages = @json($packages->map(fn($p) => ['id' => $p->id, 'price' => $p->monthly_price]));

function selectPackage(el) {
    document.querySelectorAll('.pkg-card').forEach(c => c.classList.remove('selected'));
    el.classList.add('selected');
    document.getElementById('selected-pkg-id').value = el.dataset.pkgId;
    updateTotal();
}

function updateTotal() {
    const pkgId   = document.getElementById('selected-pkg-id')?.value;
    const months  = parseInt(document.getElementById('billing-months')?.value || 1);
    const pkg     = packages.find(p => p.id == pkgId);
    const price   = pkg ? parseFloat(pkg.price) : 0;
    const total   = (price * months).toFixed(2);
    const amtEl   = document.getElementById('amount-val');
    if (amtEl) amtEl.textContent = total;
}

document.getElementById('billing-months')?.addEventListener('change', updateTotal);
updateTotal();

// ── Screenshot upload ──────────────────────────────────────────────────────
const input     = document.getElementById('screenshot-input');
const zone      = document.getElementById('upload-zone');
const placeholder = document.getElementById('upload-placeholder');
const preview   = document.getElementById('upload-preview');
const previewImg = document.getElementById('preview-img');
const pdfPreview = document.getElementById('pdf-preview');
const pdfName   = document.getElementById('pdf-filename');

input?.addEventListener('change', () => handleFile(input.files[0]));

function handleFile(file) {
    if (!file) return;
    placeholder.classList.add('d-none');
    preview.classList.remove('d-none');

    if (file.type === 'application/pdf') {
        previewImg.classList.add('d-none');
        pdfPreview.classList.remove('d-none');
        pdfName.textContent = file.name;
    } else {
        pdfPreview.classList.add('d-none');
        previewImg.classList.remove('d-none');
        const reader = new FileReader();
        reader.onload = e => { previewImg.src = e.target.result; };
        reader.readAsDataURL(file);
    }
}

function clearUpload(e) {
    e.stopPropagation();
    input.value = '';
    placeholder.classList.remove('d-none');
    preview.classList.add('d-none');
    previewImg.src = '';
}

function handleDrop(e) {
    e.preventDefault();
    zone.classList.remove('drag-over');
    const file = e.dataTransfer.files[0];
    if (file) {
        const dt = new DataTransfer();
        dt.items.add(file);
        input.files = dt.files;
        handleFile(file);
    }
}
function handleDragOver(e) { e.preventDefault(); zone.classList.add('drag-over'); }
function handleDragLeave()  { zone.classList.remove('drag-over'); }

// ── Copy to clipboard ──────────────────────────────────────────────────────
function copyText(text) {
    navigator.clipboard.writeText(text).then(() => {
        alert('{{ __('Copied!') }} ' + text);
    });
}

// ── Submit guard ───────────────────────────────────────────────────────────
document.getElementById('renewal-form')?.addEventListener('submit', function () {
    const btn = document.getElementById('submit-btn');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>{{ __('Submitting...') }}';
});
</script>
</body>
</html>
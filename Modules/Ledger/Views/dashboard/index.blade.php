@php
    $title = __('Dashboard');
    $heading = __('Dashboard');
@endphp

@extends($ledgerLayout)
@section('ledger-content')

    @if ($stats['today_transactions'] === 0 && $recentTransactions->isEmpty())
        @include('ledger::partials.empty-state', [
            'icon' => 'bi-journal-plus',
            'title' => __('No transactions yet'),
            'description' => __('Start by adding a customer or recording your first transaction.'),
            'actionLabel' => __('Record Transaction'),
            'actionUrl' => route('ledger.transactions.create'),
        ])
    @else
        {{-- Top stat cards --}}
        <div class="row g-3 mb-4">
            <div class="col-6 col-md-3">
                <div class="ledger-stat-card p-3 h-100">
                    <div class="text-muted small">{{ __("Today's Transactions") }}</div>
                    <div class="h4 mb-0">{{ $stats['today_transactions'] }}</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="ledger-stat-card p-3 h-100">
                    <div class="text-muted small">{{ __("Today's Income") }}</div>
                    <div class="h4 mb-0 text-success">{{ formatCurrency($stats['today_income']) }}</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="ledger-stat-card p-3 h-100">
                    <div class="text-muted small">{{ __("Today's Expense") }}</div>
                    <div class="h4 mb-0 text-danger">{{ formatCurrency($stats['today_expense']) }}</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="ledger-stat-card p-3 h-100">
                    <div class="text-muted small">{{ __('Current Balance') }}</div>
                    <div class="h4 mb-0">{{ formatCurrency($stats['current_balance']) }}</div>
                </div>
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-6 col-md-3">
                <div class="ledger-stat-card p-3 h-100">
                    <div class="text-muted small">{{ __('Total Customers') }}</div>
                    <div class="h5 mb-0">{{ $stats['total_customers'] }}</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="ledger-stat-card p-3 h-100">
                    <div class="text-muted small">{{ __('Total Suppliers') }}</div>
                    <div class="h5 mb-0">{{ $stats['total_suppliers'] }}</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="ledger-stat-card p-3 h-100">
                    <div class="text-muted small">{{ __('Pending Receivables') }}</div>
                    <div class="h5 mb-0 text-success">{{ formatCurrency($stats['pending_receivables']) }}</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="ledger-stat-card p-3 h-100">
                    <div class="text-muted small">{{ __('Pending Payables') }}</div>
                    <div class="h5 mb-0 text-danger">{{ formatCurrency($stats['pending_payables']) }}</div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm mt-4 mb-3">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <strong>{{ __('Recent Transactions') }}</strong>
                <a href="{{ route('ledger.transactions.index') }}" class="small text-decoration-none">{{ __('View all') }}</a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead>
                        <tr>
                            <th>{{ __('Date') }}</th>
                            <th>{{ __('Type') }}</th>
                            <th>{{ __('Party') }}</th>
                            <th>{{ __('Category') }}</th>
                            <th class="text-end">{{ __('Amount') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($recentTransactions as $tx)
                            <tr>
                                <td>{{ formatDate($tx->transaction_date) }}</td>
                                <td>
                                    @php
                                        if ($tx->customer) {
                                            $label = $tx->type === 'debit' ? __('He Paid') : __('He Owe');
                                        } elseif ($tx->supplier) {
                                            $label = $tx->type === 'debit' ? __('You Owe') : __('You Paid');
                                        } else {
                                            $label = '';
                                        }
                                    @endphp

                                    <span class="badge ledger-badge-{{ $tx->type }}">
                                        {{ ucfirst($tx->type) }}
                                        @if($label)
                                            ({{ $label }})
                                        @endif
                                    </span>
                                </td>

                                <td>
                                    {{ $tx->customer?->name ?? $tx->supplier?->name ?? '—' }}

                                    @if($tx->customer)
                                        <span class="badge bg-primary ms-1">
                                            {{ __('Customer') }}
                                        </span>
                                    @elseif($tx->supplier)
                                        <span class="badge bg-success ms-1">
                                            {{ __('Supplier') }}
                                        </span>
                                    @endif
                                </td>
                                <td>{{ $tx->category?->name ?? '—' }}</td>
                                <td class="text-end">{{ formatCurrency($tx->amount) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-12 col-lg-7">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <strong>{{ __('This Month') }}</strong>
                    </div>
                    <div class="card-body">
                        <canvas id="monthlyChart" height="220"></canvas>
                    </div>
                </div>
            </div>

            <div class="col-12 col-lg-5">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-white"><strong>{{ __('Upcoming Reminders') }}</strong></div>
                    <div class="card-body">
                        @forelse ($upcomingReminders as $reminder)
                            <div class="d-flex justify-content-between align-items-center py-2 {{ ! $loop->last ? 'border-bottom' : '' }}">
                                <div>
                                    <div class="fw-semibold small">{{ $reminder->title }}</div>
                                    <div class="text-muted small">
                                        {{ $reminder->customer?->name ?? $reminder->supplier?->name ?? '—' }}
                                    </div>
                                </div>
                                <span class="badge bg-light text-dark border">{{ formatDate($reminder->due_date) }}</span>
                            </div>
                        @empty
                            <p class="text-muted small mb-0">{{ __('No upcoming reminders.') }}</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>


    @endif

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script>
        const ctx = document.getElementById('monthlyChart');
        if (ctx) {
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: {!! $monthlyTotals->pluck('transaction_date')->map(fn($d) => \Carbon\Carbon::parse($d)->format('d')) !!},
                    datasets: [
                        {
                            label: '{{ __('Income') }}',
                            data: {!! $monthlyTotals->pluck('income') !!},
                            borderColor: '#16A34A',
                            backgroundColor: 'rgba(22,163,74,0.08)',
                            tension: 0.3,
                            fill: true,
                        },
                        {
                            label: '{{ __('Expense') }}',
                            data: {!! $monthlyTotals->pluck('expense') !!},
                            borderColor: '#DC2626',
                            backgroundColor: 'rgba(220,38,38,0.08)',
                            tension: 0.3,
                            fill: true,
                        }
                    ]
                },
                options: {
                    responsive: true,
                    plugins: { legend: { position: 'bottom' } },
                    scales: { y: { beginAtZero: true } }
                }
            });
        }
    </script>
    @endpush
@endsection
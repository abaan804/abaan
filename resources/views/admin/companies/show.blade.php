<x-admin-layout>
    @section('title', $company->name)

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="mb-0">{{ $company->name }}</h3>
            <span class="badge bg-{{ $company->status === 'active' ? 'success' : ($company->status === 'pending' ? 'warning' : 'danger') }}">
                {{ ucfirst($company->status) }}
            </span>
        </div>
        <a href="{{ route('admin.companies.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> {{ __('Back to Companies') }}
        </a>
    </div>

    <div class="row g-4">
        <div class="col-12 col-lg-4">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white"><strong>{{ __('Company Profile') }}</strong></div>
                <div class="card-body">
                    <dl class="row mb-0 small">
                        <dt class="col-5 text-muted">{{ __('Email') }}</dt>
                        <dd class="col-7">{{ $company->email ?? '—' }}</dd>
                        <dt class="col-5 text-muted">{{ __('Phone') }}</dt>
                        <dd class="col-7">{{ $company->phone ?? '—' }}</dd>
                        <dt class="col-5 text-muted">{{ __('Slug') }}</dt>
                        <dd class="col-7"><code>{{ $company->slug }}</code></dd>
                        <dt class="col-5 text-muted">{{ __('Trial Ends') }}</dt>
                        <dd class="col-7">{{ formatDate($company->trial_ends_at) }}</dd>
                        <dt class="col-5 text-muted">{{ __('Registered') }}</dt>
                        <dd class="col-7">{{ formatDate($company->created_at) }}</dd>
                    </dl>

                    <hr>

                    @if ($company->status === 'suspended')
                        <form method="POST" action="{{ route('admin.companies.activate', $company) }}">
                            @csrf
                            <button type="submit" class="btn btn-success w-100">{{ __('Activate Company') }}</button>
                        </form>
                    @else
                        <form method="POST" action="{{ route('admin.companies.suspend', $company) }}"
                              onsubmit="return confirm('{{ __('Suspend this company?') }}');">
                            @csrf
                            <button type="submit" class="btn btn-danger w-100">{{ __('Suspend Company') }}</button>
                        </form>
                    @endif
                </div>
            </div>

            <div class="card shadow-sm">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <strong>{{ __('Enabled Modules') }}</strong>
                        <a href="{{ route('admin.companies.modules.edit', $company) }}" class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-sliders"></i> {{ __('Manage') }}
                        </a>
                    </div>                
                    <div class="card-body">
                    @forelse ($company->companyModules as $cm)
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span>{{ $cm->moduleDefinition->translated('name') }}</span>
                            <span class="badge bg-{{ $cm->is_enabled ? 'success' : 'secondary' }}">
                                {{ $cm->is_enabled ? __('Enabled') : __('Disabled') }}
                            </span>
                        </div>
                    @empty
                        <p class="text-muted small mb-0">{{ __('No modules assigned yet.') }}</p>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-8">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white"><strong>{{ __('Users') }}</strong></div>
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead>
                            <tr>
                                <th>{{ __('Name') }}</th>
                                <th>{{ __('Email') }}</th>
                                <th>{{ __('Role') }}</th>
                                <th>{{ __('Joined') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($company->users as $user)
                                <tr>
                                    <td>{{ $user->name }}</td>
                                    <td>{{ $user->email }}</td>
                                    <td>
                                        @foreach ($user->roles as $role)
                                            <span class="badge bg-light text-dark border">{{ $role->name }}</span>
                                        @endforeach
                                    </td>
                                    <td>{{ $user->created_at->format('M d, Y') }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center text-muted py-3">{{ __('No users.') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white"><strong>{{ __('Subscription History') }}</strong></div>
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead>
                            <tr>
                                <th>{{ __('Package') }}</th>
                                <th>{{ __('Status') }}</th>
                                <th>{{ __('Started') }}</th>
                                <th>{{ __('Trial Ends') }}</th>
                                <th>{{ __('Ends') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($company->subscriptions as $sub)
                                <tr>
                                    <td>{{ $sub->package?->translated('name') ?? '—' }}</td>
                                    <td><span class="badge bg-light text-dark border">{{ ucfirst($sub->status) }}</span></td>
                                    <td>{{ formatDate($sub->starts_at) }}</td>
                                    <td>{{ formatDate($sub->trial_ends_at) }}</td>
                                    <td>{{ formatDate($sub->ends_at) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-center text-muted py-3">{{ __('No subscriptions.') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-header bg-white"><strong>{{ __('Recent Transactions') }}</strong></div>
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead>
                            <tr>
                                <th>{{ __('Amount') }}</th>
                                <th>{{ __('Gateway') }}</th>
                                <th>{{ __('Status') }}</th>
                                <th>{{ __('Date') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($company->transactions as $tx)
                                <tr>
                                    <td>{{ formatCurrency($tx->amount) }}</td>
                                    <td>{{ ucfirst($tx->gateway) }}</td>
                                    <td>
                                        <span class="badge bg-{{ $tx->status === 'success' ? 'success' : ($tx->status === 'pending' ? 'warning' : 'danger') }}">
                                            {{ ucfirst($tx->status) }}
                                        </span>
                                    </td>
                                    <td>{{ formatDate($tx->created_at) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center text-muted py-3">{{ __('No transactions yet.') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>
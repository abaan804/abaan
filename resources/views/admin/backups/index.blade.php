<x-admin-layout>
    @section('title', 'Backups')

    <h3 class="mb-4">{{ __('Backup & Restore') }}</h3>

    <div class="alert alert-warning">
        <i class="bi bi-exclamation-triangle"></i>
        {{ __('Restoring a backup will completely overwrite your current database. This cannot be undone. Always download a fresh backup before restoring an older one.') }}
    </div>

    <div class="row g-4 mb-4">
        <div class="col-12 col-lg-6">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white"><strong>{{ __('Create New Backup') }}</strong></div>
                <div class="card-body">
                    <p class="text-muted small">{{ __('Generates a full database backup (.sql) and stores it on the server.') }}</p>
                    <form method="POST" action="{{ route('admin.backups.create') }}">
                        @csrf
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-database-add"></i> {{ __('Create Backup Now') }}
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-6">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white"><strong>{{ __('Upload Backup') }}</strong></div>
                <div class="card-body">
                    <p class="text-muted small">{{ __('Upload a previously downloaded .sql backup file so you can restore it.') }}</p>
                    <form method="POST" action="{{ route('admin.backups.upload') }}" enctype="multipart/form-data">
                        @csrf
                        <div class="input-group">
                            <input type="file" name="backup_file" accept=".sql" class="form-control @error('backup_file') is-invalid @enderror" required>
                            <button type="submit" class="btn btn-outline-primary">{{ __('Upload') }}</button>
                        </div>
                        @error('backup_file') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header bg-white"><strong>{{ __('Available Backups') }}</strong></div>
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead>
                    <tr>
                        <th>{{ __('Filename') }}</th>
                        <th>{{ __('Size') }}</th>
                        <th>{{ __('Created') }}</th>
                        <th class="text-end">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($backups as $backup)
                        <tr>
                            <td><code>{{ $backup['filename'] }}</code></td>
                            <td>{{ number_format($backup['size'] / 1024 / 1024, 2) }} MB</td>
                            <td>{{ \Carbon\Carbon::createFromTimestamp($backup['created_at'])->diffForHumans() }}</td>
                            <td class="text-end">
                                <a href="{{ route('admin.backups.download', $backup['filename']) }}" class="btn btn-sm btn-outline-secondary">
                                    <i class="bi bi-download"></i> {{ __('Download') }}
                                </a>
                                <button type="button" class="btn btn-sm btn-outline-warning"
                                        data-bs-toggle="modal" data-bs-target="#restoreModal-{{ $loop->index }}">
                                    <i class="bi bi-arrow-counterclockwise"></i> {{ __('Restore') }}
                                </button>
                                <form method="POST" action="{{ route('admin.backups.destroy', $backup['filename']) }}" class="d-inline"
                                      onsubmit="return confirm('{{ __('Delete this backup file?') }}');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>

                                {{-- Restore confirmation modal --}}
                                <div class="modal fade" id="restoreModal-{{ $loop->index }}" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <form method="POST" action="{{ route('admin.backups.restore', $backup['filename']) }}">
                                                @csrf
                                                <div class="modal-header">
                                                    <h5 class="modal-title text-danger">{{ __('Confirm Restore') }}</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <p>{{ __('This will permanently replace your current database with the contents of:') }}</p>
                                                    <p><code>{{ $backup['filename'] }}</code></p>
                                                    <p class="text-danger small">{{ __('This action cannot be undone.') }}</p>
                                                    <label class="form-label">{{ __('Type RESTORE to confirm') }}</label>
                                                    <input type="text" name="confirm_text" class="form-control" required>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                                                    <button type="submit" class="btn btn-danger">{{ __('Restore Database') }}</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center text-muted py-4">{{ __('No backups yet.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-admin-layout>
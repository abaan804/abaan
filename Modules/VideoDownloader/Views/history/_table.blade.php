@forelse ($downloads as $dl)
    <tr>
        <td data-label="{{ __('Video') }}" class="vd-cell-title">
            <div class="d-flex align-items-center gap-2">
                @if ($dl->video_thumbnail)
                    <img src="{{ $dl->thumbnail_url }}" class="vd-thumb-sm" alt=""
                         onerror="this.style.display='none'">
                @endif
                <div style="min-width:0;">
                    <div class="small fw-semibold"
                         style="max-width:240px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"
                         title="{{ $dl->video_title ?? $dl->original_url }}">
                        {{ $dl->video_title ?? \Illuminate\Support\Str::limit($dl->original_url, 40) }}
                    </div>
                    <div class="text-muted small">
                        <i class="bi bi-globe"></i> {{ ucfirst($dl->platform ?? '?') }}
                    </div>
                </div>
            </div>
        </td>
        <td data-label="{{ __('Format') }}">
            @if ($dl->selected_quality || $dl->selected_format_ext)
                <span class="badge bg-light text-dark border">
                    {{ $dl->selected_quality ?? '' }}
                    @if ($dl->selected_format_ext) .{{ strtoupper($dl->selected_format_ext) }} @endif
                </span>
            @else
                <span class="text-muted">—</span>
            @endif
        </td>
        <td data-label="{{ __('Status') }}">
            <span class="vd-status-pill vd-status-{{ $dl->status }}">
                <i class="bi {{ $dl->status_icon }}"></i>
                {{ ucfirst($dl->status) }}
            </span>
        </td>
        <td data-label="{{ __('Size') }}" class="small text-muted">
            {{ $dl->formatted_file_size }}
        </td>
        <td data-label="{{ __('Date') }}" class="small text-muted">
            {{ $dl->created_at->format('d M Y') }}
        </td>
        <td class="vd-cell-actions">
            <a href="{{ route('videodownloader.download.show', $dl) }}"
               class="btn btn-sm btn-outline-primary" title="{{ __('View') }}">
                <i class="bi bi-eye"></i>
            </a>
            @if ($dl->is_servable)
                <a href="{{ route('videodownloader.download.serve', $dl) }}"
                   class="btn btn-sm btn-outline-success" title="{{ __('Download') }}">
                    <i class="bi bi-cloud-arrow-down"></i>
                </a>
            @endif
        </td>
    </tr>
@empty
    <tr class="vd-row-empty">
        <td colspan="6">
            <div class="vd-empty">
                <i class="bi bi-clock-history"></i>
                <p>{{ __('No downloads match the filters') }}</p>
                <a href="{{ route('videodownloader.download.create') }}"
                   class="btn btn-primary btn-sm">
                    {{ __('Start a Download') }}
                </a>
            </div>
        </td>
    </tr>
@endforelse

@if ($downloads->hasPages())
    <tr class="vd-row-empty">
        <td colspan="6">
            <div id="h-pagination" class="d-flex justify-content-center py-2">
                {{ $downloads->links() }}
            </div>
        </td>
    </tr>
@endif
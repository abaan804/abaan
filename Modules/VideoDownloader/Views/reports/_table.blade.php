@forelse ($downloads as $dl)
    <tr>
        <td data-label="{{ __('Title') }}" class="vd-cell-title">
            <div class="small fw-semibold"
                 style="max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                {{ $dl->video_title ?? \Illuminate\Support\Str::limit($dl->original_url, 35) }}
            </div>
        </td>
        <td data-label="{{ __('Platform') }}">
            {{ ucfirst($dl->platform ?? '—') }}
        </td>
        <td data-label="{{ __('Quality') }}">
            @if ($dl->selected_quality)
                <span class="badge bg-light text-dark border">
                    {{ $dl->selected_quality }}
                    @if ($dl->selected_format_ext) .{{ strtoupper($dl->selected_format_ext) }} @endif
                </span>
            @else
                <span class="text-muted">—</span>
            @endif
        </td>
        <td data-label="{{ __('Status') }}">
            <span class="vd-status-pill vd-status-{{ $dl->status }}">
                {{ ucfirst($dl->status) }}
            </span>
        </td>
        <td data-label="{{ __('Size') }}" class="small text-muted">
            {{ $dl->formatted_file_size }}
        </td>
        <td data-label="{{ __('User') }}" class="small">
            {{ $dl->user?->name ?? '—' }}
        </td>
        <td data-label="{{ __('Date') }}" class="small text-muted">
            {{ $dl->created_at->format('d M Y') }}
        </td>
    </tr>
@empty
    <tr class="vd-row-empty">
        <td colspan="7">
            <div class="vd-empty">
                <i class="bi bi-bar-chart"></i>
                <p>{{ __('No records match the filters') }}</p>
            </div>
        </td>
    </tr>
@endforelse
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: dejavusans; font-size: 10px; color: #1a252f; margin: 0; }
        .header { border-bottom: 2px solid #1a3a5c; padding-bottom: 10px; margin-bottom: 14px; }
        .report-title { font-size: 15px; font-weight: bold; color: #1a3a5c; margin: 10px 0 4px; }
        .report-meta  { font-size: 9px; color: #7f8c8d; margin-bottom: 12px; }
        table { width: 100%; border-collapse: collapse; }
        th { background: #eaf2f8; padding: 6px 7px; font-size: 9px; text-align: left; border-bottom: 1px solid #aed6f1; }
        td { padding: 5px 7px; font-size: 9px; border-bottom: 1px solid #f2f3f4; }
        .badge-completed { background: #dcfce7; color: #166534; padding: 1px 5px; border-radius: 3px; }
        .badge-failed    { background: #fee2e2; color: #991b1b; padding: 1px 5px; border-radius: 3px; }
        .badge-pending   { background: #f1f5f9; color: #64748b; padding: 1px 5px; border-radius: 3px; }
        .badge-cancelled { background: #f1f5f9; color: #374151; padding: 1px 5px; border-radius: 3px; }
        tfoot td { font-weight: bold; border-top: 2px solid #1a3a5c; }
        .footer { margin-top: 20px; font-size: 8px; color: #aab7b8; text-align: center; }
    </style>
</head>
<body>
    <div class="header">
        <strong style="font-size:16px;color:#1a3a5c;">{{ __('Video Downloader') }}</strong>
    </div>

    <div class="report-title">{{ __('Download History Report') }}</div>
    <div class="report-meta">{{ __('Generated on') }} {{ now()->format('d M Y, h:i A') }}</div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>{{ __('Title') }}</th>
                <th>{{ __('Platform') }}</th>
                <th>{{ __('Quality') }}</th>
                <th>{{ __('Status') }}</th>
                <th>{{ __('Size') }}</th>
                <th>{{ __('Date') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($downloads as $i => $dl)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ \Illuminate\Support\Str::limit($dl->video_title ?? $dl->original_url, 40) }}</td>
                    <td>{{ ucfirst($dl->platform ?? '—') }}</td>
                    <td>{{ $dl->selected_quality ?? '—' }}
                        @if($dl->selected_format_ext) .{{ strtoupper($dl->selected_format_ext) }} @endif
                    </td>
                    <td>
                        <span class="badge-{{ $dl->status }}">{{ ucfirst($dl->status) }}</span>
                    </td>
                    <td>{{ $dl->formatted_file_size }}</td>
                    <td>{{ $dl->created_at->format('d M Y') }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="7">{{ __('Total: :n records', ['n' => $downloads->count()]) }}</td>
            </tr>
        </tfoot>
    </table>

    <div class="footer">{{ __('Video Downloader — Abaan SaaS Platform') }}</div>
</body>
</html>
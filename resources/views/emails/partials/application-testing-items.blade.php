<div style="margin:0 0 22px;">
    <h2 style="margin:0 0 10px;padding:10px 13px;color:{{ $titleColor }};background:{{ $titleBackground }};border-radius:8px;font-size:16px;">
        {{ $title }} ({{ $items->count() }})
    </h2>

    @foreach($items as $item)
        <div style="margin-bottom:9px;padding:14px;border:1px solid #e1e7ef;border-radius:9px;">
            <div style="margin-bottom:7px;color:#25344d;font-size:15px;font-weight:700;">
                <span style="color:#4f78c4;">{{ $item->reference }}</span>
                &nbsp;·&nbsp; {{ $item->test_case }}
            </div>
            <div style="color:#64748b;font-size:12px;line-height:1.6;">
                <strong>{{ __('application_testing_report.module') }}:</strong>
                {{ $item->unit?->name ?? __('application_testing_report.unknown_module') }}
                @if($item->category)
                    &nbsp;·&nbsp;
                    <strong>{{ __('application_testing_report.category') }}:</strong>
                    {{ strtoupper($item->category) }}
                @endif
            </div>
            <div style="margin-top:7px;color:#4b5d73;font-size:13px;line-height:1.55;">
                <strong>{{ __('application_testing_report.comment') }}:</strong>
                {{ $item->comment ?: __('application_testing_report.no_comment') }}
            </div>
        </div>
    @endforeach
</div>

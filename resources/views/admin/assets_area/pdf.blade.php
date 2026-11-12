<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <title>{{ __('ui.assets_report') }}</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #fff;
            color: #333;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        .header {
            text-align: center;
            padding: 20px 0;
            background-color: #6b6a6a;
            color: white;
        }

        .header h2 {
            margin: 0;
            font-size: 28px;
            letter-spacing: 1px;
        }

        .content {
            margin: 30px auto;
            padding: 20px;
            width: 90%;
            max-width: 900px;
            background-color: #fff;
        }

        .content h1 {
            font-size: 24px;
            color: #58585a;
            margin-bottom: 20px;
            border-bottom: 2px solid #ddd;
            padding-bottom: 10px;
        }

        .content table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .content th, .content td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
        }

        .content th {
            background-color: #f2f2f2;
            color: #58585a;
            font-weight: bold;
        }

        .content tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .signature-section {
            margin-top: 40px;
            display: flex;
            justify-content: space-between;
        }

        .signature {
            width: 45%;
        }

        .signature p {
            font-weight: bold;
        }

        .signature-line {
            border-top: 1px solid #000;
            width: 100%;
            margin-top: 5px;
        }

        .footer {
            text-align: center;
            padding: 15px 0;
            background-color: #6b6a6a;
            color: white;
            position: fixed;
            bottom: 0;
            width: 100%;
            font-size: 14px;
        }

        @page {
            margin: 0;
            padding: 0;
        }
    </style>
</head>
<body>

    <div class="header">
        <h2>{{ __('ui.assets_report') }}</h2>
    </div>

    <div class="content">
        <h1>{{ __('ui.asset_details') }}</h1>
        <table>
            @if ($asset->asset_name)
                <tr>
                    <th>{{ __('words.name') }}</th>
                    <td>{{ $asset->asset_name }}</td>
                </tr>
            @endif

            @if ($asset->deviceType && $asset->deviceType->name)
                <tr>
                    <th>{{ __('words.device_type') }}</th>
                    <td>{{ $asset->deviceType->name }}</td>
                </tr>
            @endif

            @if ($asset->brand && $asset->brand->name)
                <tr>
                    <th>{{ __('words.brand') }}</th>
                    <td>{{ $asset->brand->name }}</td>
                </tr>
            @endif

            @if ($asset->company && $asset->company->name)
                <tr>
                    <th>{{ __('words.companyy') }}</th>
                    <td>{{ $asset->company->name }}</td>
                </tr>
            @endif

            @if ($asset->branch && $asset->branch->name)
                <tr>
                    <th>{{ __('words.branch') }}</th>
                    <td>{{ $asset->branch->name }}</td>
                </tr>
            @endif

            @if ($asset->department && $asset->department->name)
                <tr>
                    <th>{{ __('words.department') }}</th>
                    <td>{{ $asset->department->name }}</td>
                </tr>
            @endif

            @if ($asset->workplace && $asset->workplace->name)
                <tr>
                    <th>{{ __('words.workplace') }}</th>
                    <td>{{ $asset->workplace->name }}</td>
                </tr>
            @endif

            @if ($asset->serial_number)
                <tr>
                    <th>{{ __('words.serial_number') }}</th>
                    <td>{{ $asset->serial_number }}</td>
                </tr>
            @endif

            @if ($asset->cpu)
                <tr>
                    <th>CPU</th>
                    <td>{{ $asset->cpu }}</td>
                </tr>
            @endif

            @if ($asset->ram)
                <tr>
                    <th>RAM</th>
                    <td>{{ $asset->ram }}</td>
                </tr>
            @endif

            @if ($asset->storage)
                <tr>
                    <th>{{ __('ui.storage') }}</th>
                    <td>{{ $asset->storage }}</td>
                </tr>
            @endif

            @if ($asset->gpu)
                <tr>
                    <th>GPU</th>
                    <td>{{ $asset->gpu }}</td>
                </tr>
            @endif
        </table>

        <div class="signature-section">
            <div class="signature">
                <p>{{ __('words.authorized_by') }}:</p>
                <p>{{ __('words.name') }}:</p>
                <div class="signature-line"></div>
            </div>
            <div class="signature">
                <p>{{ __('words.signature') }}:</p>
                <div class="signature-line"></div>
            </div>
        </div>
    </div>

    <div class="footer">
        <p>&copy; {{ date('Y') }} Documentation-system. {{ __('ui.rights_reserved') }}</p>
    </div>

</body>
</html>

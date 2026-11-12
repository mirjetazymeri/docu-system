<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">

    <style>
        body {
            font-family: 'Arial', sans-serif;
            margin: 40px;
            font-size: 14px;
            color: #333;
        }
        @page {
            margin: 0;
            padding: 0;
        }


        /* Table Styling */
        .table-container {
            margin-top: 20px;
            background: white;
            padding: 20px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        table {
            width: 100%;
            border-collapse: collapse;
            overflow: hidden;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: left;
        }
        th {
            background-color: #34495e;
            color: white;
            font-size: 15px;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .result {
            font-weight: bold;
            text-align: center;
            padding: 10px;
        }
        .passed {
            background-color: #2ecc71;
            color: white;
        }
        .not-passed {
            background-color: #e74c3c;
            color: white;
        }
        .in-process {
            background-color: #f39c12;
            color: white;
        }

        .signature-section {
            margin-top: 50px;
            text-align: right; /* Aligning the whole section to the right */
        }

        .signature-box {
            display: inline-block; /* Ensuring both the line and label are treated as inline blocks */
            text-align: center;
        }

        .signature-line {
            width: 200px;
            border-bottom: 2px solid black;
            margin-bottom: 5px; /* Small space between line and text */
        }

        .signature-label {
            font-size: 14px;
            color: #555;
            margin-top: 5px; /* Space between the line and the label */
        }

        /* Footer */
        .footer {
            text-align: center;
            font-size: 12px;
            color: #555;
            margin-top: 40px;
        }

        /* Date Section */
        .generated-date {
            text-align: center;
            font-size: 14px;
            margin-top: 20px;
            color: #555;
        }
    </style>
</head>
<body>



    <!-- Table Section -->
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>{{ __('ui.application_name') }}</th>
                    <th>{{ __('ui.category') }}</th>
                    <th>{{ __('ui.module') }}</th>
                    <th>{{ __('ui.feature') }}</th>
                    <th>{{ __('ui.result') }}</th>
                    <th>{{ __('ui.comment') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($softwareDetails as $index => $detail)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $detail->softwareCompany->application->name }}</td>
                        <td>{{ $detail->category ?? 'N/A' }}</td>
                        <td>{{ $detail->module }}</td>
                        <td>{{ $detail->feature }}</td>
                        <td class="result
                            @if($detail->result == 'passed') passed
                            @elseif($detail->result == 'not_passed') not-passed
                            @elseif($detail->result == 'in_process') in-process
                            @endif">
                            {{ ucfirst(str_replace('_', ' ', $detail->result)) }}
                        </td>
                        <td>{{ $detail->comment ?? 'N/A' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Signature Section -->
    <div class="signature-section">
        <div class="signature-box">
            <div class="signature-line"></div>
            <div class="signature-label">{{ __('ui.signature_of_tester') }}</div>
        </div>
    </div>

    <!-- Date Section -->
    <div class="generated-date">
        {{ __('ui.generated_on') }} {{ now()->format('d-m-Y H:i') }}
    </div>
>

</body>
</html>

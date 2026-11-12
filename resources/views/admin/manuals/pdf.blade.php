<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">

<head>
    <meta charset="utf-8">
    <title>{{ __('ui.manual_pdf') }}</title>
    <style>
        html,
        body {
            font-family: 'DejaVu Sans', sans-serif;
            color: #333;
            margin: 0;
            padding: 0;
            height: 100%;
            display: flex;
            flex-direction: column;
        }

        .pdf-emoji {
            font-family: 'Noto Emoji PDF';
            font-weight: normal;
        }

        @page {
            margin: 0;
            padding: 0;
        }

        .header {
            background-color: #2c3e50;
            color: white;
            padding: 20px 0;
            text-align: center;
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 0;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .header img {
            max-height: 30px; /* Adjust this value to your desired logo size */
            margin-right: 15px; /* Space between logo and text */
        }

        .header h1 {
            margin: 0;
            font-size: 28px;
        }

        .header p {
            margin-top: 5px;
            font-size: 14px;
        }

        .content {
            flex-grow: 1;
            padding: 20px;
            background-color: white;
            box-sizing: border-box;
        }

        .content h2 {
            color: #2c3e50;
            font-size: 24px;
            margin-bottom: 15px;
        }

        .content p {
            font-size: 16px;
            line-height: 1.6;
            color: #555;
            margin-bottom: 15px;
        }

        .content strong {
            color: #2c3e50;
            font-weight: bold;
        }

        /* Table styling */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        table,
        th,
        td {
            border: 1px solid #000;
        }

        th,
        td {
            padding: 8px;
            text-align: left;
            word-wrap: break-word;
        }

        td {
            max-width: 150px;
        }

        .footer {
            text-align: center;
            padding: 10px;
            font-size: 12px;
            background-color: #2c3e50;
            color: white;
            margin-top: auto;
            position: absolute;
            width: 100%;
            bottom: 0;
        }

        .footer p {
            margin: 0;
        }

        @media (max-width: 768px) {
            .header {
                padding: 15px;
                font-size: 20px;
            }

            .content {
                padding: 15px;
            }

            .content h2 {
                font-size: 20px;
            }

            .footer {
                padding: 10px;
            }
        }
    </style>
</head>

<body>
    <div class="header">
        <img src="{{ asset('frontend/assets/images/2logo.png') }}" alt="Logo"> <!-- Logo path -->
    </div>
    <div class="content">
        <!-- Dynamically render the content including tables here -->
        <div>{!! $pdfContent ?? $manual->content !!}</div>

        <!-- Generated on and Signature in parallel -->
        <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 50px;">
            <p style="margin: 0;">{{ __('ui.generated_on') }} {{ date('m/d/Y h:i A') }}</p> <br>
            <p style="margin: 0;">{{ __('ui.signature') }} <span style="display: inline-block; border-bottom: 1px solid #000; width: 200px;"></span></p>
        </div>
    </div>
<br>
    <div class="footer">
        <p>&copy; {{ date('Y') }} Documentation-system. {{ __('ui.rights_reserved') }}</p>
    </div>




</body>

</html>

@section('footer_scripts')
    <script src="{{ asset('plugins/summernote/summernote-bs4.min.js') }}"></script>
<script>window.applyUiLocale && window.applyUiLocale();</script>
    <script>
        $(document).ready(function() {
            // Initialize summernote for Audit Description
            $('#content').summernote({
                height: '300px' // Set the height of the editor to 300 pixels
            });
        });
    </script>
@stop

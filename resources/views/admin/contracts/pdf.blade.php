<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('ui.employment_agreement') }}</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            margin: 0;
            padding: 0;
        }
        .contract-container {
            width: 100%;
            margin: 0 auto;
            padding: 20px;
            box-sizing: border-box;
        }
        h1, h2, h3 {
            text-align: center;
            color: #333;
        }
        p {
            line-height: 1.6;
            color: #333;
        }
        .party, .terms, .responsibilities, .signatures {
            margin-bottom: 20px;
        }
        .party h2, .terms h2, .responsibilities h2, .signatures h2 {
            margin-bottom: 10px;
            text-align: left;
        }
        span {
            font-weight: bold;
        }
        ul {
            list-style-type: disc;
            margin: 10px 0;
            padding-left: 20px;
        }
        .signature {
            width: 45%;
            display: inline-block;
            text-align: center;
            margin-top: 50px;
        }
        .signature p {
            margin: 5px 0;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="contract-container">
        <h1>{{ __('ui.employment_agreement') }}</h1>
        <p>{{ __('ui.this_employment_agreement_is_made_on') }} <span class="date">{{ __('ui.placeholder_date') }}</span> {{ __('ui.between') }}</p>

        <div class="party">
            <h2>{{ __('ui.employer') }}</h2>
            <p>{{ __('ui.name') }} <span class="employer-name">{{ __('ui.placeholder_employer_name') }}</span></p>
            <p>{{ __('ui.address') }} <span class="employer-address">{{ __('ui.placeholder_employer_address') }}</span></p>
        </div>

        <div class="party">
            <h2>{{ __('ui.employee') }}</h2>
            <p>{{ __('ui.name') }} <span class="employee-name">{{ __('ui.placeholder_employee_name') }}</span></p>
            <p>{{ __('ui.address') }} <span class="employee-address">{{ __('ui.placeholder_employee_address') }}</span></p>
            <p>{{ __('ui.position_taxi_driver') }}</p>
        </div>

        <div class="terms">
            <h2>{{ __('ui.terms_of_employment') }}</h2>
            <p><strong>{{ __('ui.1_start_date') }}</strong> <span class="commencement-date">{{ __('ui.placeholder_start_date') }}</span></p>
            <p><strong>{{ __('ui.2_duration') }}</strong> <span class="duration">{{ __('ui.placeholder_employment_duration') }}</span></p>
            <p><strong>{{ __('ui.3_salary') }}</strong> <span class="salary">{{ __('ui.placeholder_salary_details') }}</span></p>
            <p><strong>{{ __('ui.4_working_hours') }}</strong> <span class="working-hours">{{ __('ui.placeholder_working_hours') }}</span></p>
            <p><strong>{{ __('ui.5_holidays_and_leave') }}</strong> <span class="leaves">{{ __('ui.placeholder_holiday_and_leave_details') }}</span></p>
            <p><strong>{{ __('ui.6_benefits') }}</strong> <span class="benefits">{{ __('ui.placeholder_benefit_details') }}</span></p>
        </div>

        <div class="responsibilities">
            <h2>{{ __('ui.responsibilities') }}</h2>
            <ul>
                <li>{{ __('ui.transporting_passengers_safely_and_on_time') }}</li>
                <li>{{ __('ui.keeping_the_taxi_clean_and_in_good_condition') }}</li>
                <li>{{ __('ui.complying_with_traffic_laws_and_regulations') }}</li>
                <li>{{ __('ui.providing_excellent_customer_service') }}</li>
                <li>{{ __('ui.reporting_any_incident_or_accident_immediately_to_the_employer') }}</li>
            </ul>
        </div>

        <div class="signatures">
            <h2>{{ __('ui.signatures') }}</h2>
            <div class="signature">
                <p>________________________</p>
                <p>{{ __('ui.employer_signature') }}</p>
                <p>{{ __('ui.date') }} <span class="signature-date">{{ __('ui.placeholder_date') }}</span></p>
            </div>
            <div class="signature">
                <p>________________________</p>
                <p>{{ __('ui.employee_signature') }}</p>
                <p>{{ __('ui.date') }} <span class="signature-date">{{ __('ui.placeholder_date') }}</span></p>
            </div>
        </div>

        <p class="footer">{{ __('ui.this_contract_is_governed_by_the_laws_of_country_state') }}</p>
    </div>
</body>
</html>

<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ApplicationTestingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $applicationId = $this->input('application_id');

        return [
            'application_id' => ['required', 'integer', 'exists:applications,id'],
            'tester_name' => ['required', 'string', 'max:255'],
            'version' => ['required', 'string', 'max:100'],
            'test_date' => ['required', 'date'],
            'testing_details' => ['required', 'array', 'min:1'],
            'testing_details.*.id' => [
                'nullable',
                'integer',
                Rule::exists('application_testing_details', 'id')
                    ->when(
                        $this->route('application_testing'),
                        fn ($rule, $testing) => $rule->where(
                            'application_testing_id',
                            is_object($testing) ? $testing->id : $testing
                        )
                    ),
            ],
            'testing_details.*.unit_id' => [
                'required',
                'integer',
                Rule::exists('units', 'id')
                    ->where(fn ($query) => $query->where(
                        'application_id',
                        $applicationId
                    )),
            ],
            'testing_details.*.test_case' => ['required', 'string', 'max:255'],
            'testing_details.*.result' => [
                'required',
                Rule::in(['passed', 'not_passed', 'in_process']),
            ],
            'testing_details.*.comment' => ['nullable', 'string', 'max:10000'],
            'testing_details.*.category' => [
                'required',
                Rule::in(['UI', 'UX', 'FUNCTIONALITY', 'LIGHTHOUSE']),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'testing_details.required' => __('ui.add_at_least_one_test_case'),
            'testing_details.min' => __('ui.add_at_least_one_test_case'),
            'testing_details.*.unit_id.exists' =>
                __('ui.the_selected_module_does_not_belong_to_the_selected_application'),
        ];
    }
}

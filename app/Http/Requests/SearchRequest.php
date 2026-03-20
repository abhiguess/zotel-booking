<?php

namespace App\Http\Requests;

use Carbon\Carbon;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class SearchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, ValidationRule|Closure|string>>
     */
    public function rules(): array
    {
        return [
            'check_in' => ['required', 'date', 'date_format:Y-m-d', 'after_or_equal:today'],
            'check_out' => [
                'required',
                'date',
                'date_format:Y-m-d',
                'after:check_in',
                function (string $attribute, mixed $value, Closure $fail) {
                    $checkIn = $this->input('check_in');
                    if ($checkIn && $value) {
                        $nights = Carbon::parse($checkIn)->diffInDays(Carbon::parse($value));
                        if ($nights > 30) {
                            $fail('Maximum stay duration is 30 nights.');
                        }
                    }
                },
            ],
            'adults' => ['required', 'integer', 'min:1', 'max:4'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'check_in.required' => 'Check-in date is required.',
            'check_in.date' => 'Check-in date must be a valid date.',
            'check_in.date_format' => 'Check-in date must be in YYYY-MM-DD format.',
            'check_in.after_or_equal' => 'Check-in date must be today or later.',
            'check_out.required' => 'Check-out date is required.',
            'check_out.date' => 'Check-out date must be a valid date.',
            'check_out.date_format' => 'Check-out date must be in YYYY-MM-DD format.',
            'check_out.after' => 'Check-out date must be after the check-in date.',
            'adults.required' => 'Number of adults is required.',
            'adults.integer' => 'Number of adults must be a whole number.',
            'adults.min' => 'At least 1 adult is required.',
            'adults.max' => 'Guest count cannot exceed 4 adults.',
        ];
    }
}

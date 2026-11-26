<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'resume_path' => ['nullable', 'string', 'max:500'],
            'name' => ['required', 'string', 'max:120'],
            'role' => ['sometimes', 'required', 'in:student,graduate,professional'],
            'country' => ['nullable', 'string', 'max:100'],
            'education_level' => ['nullable', 'string', 'max:100'],
            'current_field' => ['nullable', 'string', 'max:150'],
            'degree' => ['nullable', 'string', 'max:150'],
            'field_of_study' => ['nullable', 'string', 'max:150'],
            'graduation_year' => ['nullable', 'integer', 'between:1950,'.(now()->year + 10)],
            'job_title' => ['nullable', 'string', 'max:150'],
            'years_experience' => ['nullable', 'integer', 'between:0,70'],
            'industry' => ['nullable', 'string', 'max:150'],
            'career_goal' => ['nullable', 'string', 'max:1500'],
            'transition_goal' => ['nullable', 'string', 'max:1500'],
            'skills' => ['array', 'max:30'],
            'skills.*.id' => ['required', 'integer', 'exists:skills,id'],
            'skills.*.proficiency' => ['required', 'integer', 'between:1,5'],
            'interest_ids' => ['array', 'max:20'],
            'interest_ids.*' => ['integer', 'distinct', 'exists:interests,id'],
        ];
    }
}

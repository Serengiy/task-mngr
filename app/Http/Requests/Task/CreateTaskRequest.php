<?php

namespace App\Http\Requests\Task;

use App\Enums\StatusEnum;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class CreateTaskRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'required|string|max:1000',
            'responsible_id' => ['required', 'integer', Rule::exists('users', 'id')],
            'due_date' => ['required', 'date', 'after_or_equal:today'],
            'status' => ['required', Rule::enum(StatusEnum::class)],
            'participants' => ['array', 'nullable'],
            'participants.*' => ['integer', Rule::exists('users', 'id')],
        ];
    }
}

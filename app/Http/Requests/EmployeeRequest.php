<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EmployeeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     * @return array
     */
    public function rules()
    {
        $rules = [
            'fullname' => 'required|string|min:2|max:255',
            'phone' => 'required|string|min:19|max:19',
            'email' => 'required|string|email|max:255|unique:users',
            'position_id' => 'required|integer|exists:App\Models\Position,id',
            'salary' => 'required|numeric|between:0,500.000',
            'head_employee_id' => 'required|integer|exists:App\Models\Employee,id',
            'employment_at' => 'required|date_format:d.m.y',
        ];

        switch ($this->method()) {
            case 'POST':
                $rules['photo'] = 'required|image|mimes:jpeg,png,jpg|max:5120|dimensions:min_width=300,min_height=300';
                break;
            case 'PUT':
            case 'PATCH':
                $currentId = $this->route('employee')->id;
                $rules['photo'] = 'sometimes|image|mimes:jpeg,png,jpg|max:5120|dimensions:min_width=300,min_height=300';
                $rules['head_employee_id'] = $rules['head_employee_id'] . '|not_in:' . $currentId;
                break;
        }

        return $rules;
    }

}

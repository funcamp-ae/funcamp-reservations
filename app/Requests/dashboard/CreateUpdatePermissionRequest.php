<?php

namespace App\Requests\dashboard;

use Illuminate\Foundation\Http\FormRequest;

class CreateUpdatePermissionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        $id = $this->route('permission') ?? $this->route('id');

        if ($this->getMethod() === 'PUT' || $this->getMethod() === 'PATCH') {
            // For updates, name cannot be changed as it's used in code
            return [
                'nickname_ar'  => 'required|string|max:191',
                'nickname_en'  => 'required|string|max:191',
                'category_id'  => 'nullable|exists:permission_categories,id',
                'guard_name'   => 'nullable|string|max:191',
            ];
        } else {
            return [
                'name'         => 'required|string|max:191|unique:permissions,name',
                'nickname_ar'  => 'required|string|max:191',
                'nickname_en'  => 'required|string|max:191',
                'category_id'  => 'nullable|exists:permission_categories,id',
                'guard_name'   => 'nullable|string|max:191',
            ];
        }
    }
}

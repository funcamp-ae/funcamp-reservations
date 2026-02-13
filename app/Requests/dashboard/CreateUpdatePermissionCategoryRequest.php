<?php

namespace App\Requests\dashboard;

use Illuminate\Foundation\Http\FormRequest;

class CreateUpdatePermissionCategoryRequest extends FormRequest
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
        $id = $this->route('permission_category') ?? $this->route('id');

        if ($this->getMethod() === 'PUT' || $this->getMethod() === 'PATCH') {
            return [
                'slug'        => 'required|string|max:191|unique:permission_categories,slug,' . $id,
                'name_ar'     => 'required|string|max:191',
                'name_en'     => 'required|string|max:191',
                'icon'        => 'nullable|string|max:191',
                'sort_order'  => 'nullable|integer|min:0',
                'is_active'   => 'nullable|boolean',
            ];
        } else {
            return [
                'slug'        => 'required|string|max:191|unique:permission_categories,slug',
                'name_ar'     => 'required|string|max:191',
                'name_en'     => 'required|string|max:191',
                'icon'        => 'nullable|string|max:191',
                'sort_order'  => 'nullable|integer|min:0',
                'is_active'   => 'nullable|boolean',
            ];
        }
    }
}

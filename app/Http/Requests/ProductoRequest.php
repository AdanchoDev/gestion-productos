<?php

namespace App\Http\Requests;

use App\Models\Producto;
use Illuminate\Foundation\Http\FormRequest;

class ProductoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return Producto::reglasValidacion();
    }

    public function messages(): array
    {
        return Producto::mensajesValidacion();
    }
}

<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EditarsolicitudmatriculaonlineRequest extends FormRequest
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
     * @return array
     */
    public function rules()
    {
         return [            
            'turno'=>'required'
        ];
    }
    public function messages()
    {
        return[        
        'turno.required'=>'Debe seleccionar una sección, por favor intente de nuevo'
        ];
    }
}

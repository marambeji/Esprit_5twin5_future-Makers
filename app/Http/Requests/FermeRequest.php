<?php

namespace App\Http\Requests;

use App\Services\FarmBoundaryGeometry;
use Illuminate\Foundation\Http\FormRequest;
use InvalidArgumentException;

class FermeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'producteur_id' => ['required', 'integer', 'exists:producteurs,id'],
            'nom' => ['required', 'string', 'max:150'],
            'localisation' => ['required', 'string', 'max:255'],
            'latitude' => ['nullable', 'required_with:longitude', 'numeric', 'between:-90,90', 'decimal:0,7'],
            'longitude' => ['nullable', 'required_with:latitude', 'numeric', 'between:-180,180', 'decimal:0,7'],
            'superficie' => ['nullable', 'numeric', 'min:0', 'max:99999999.99', 'decimal:0,2'],
            'description' => ['nullable', 'string', 'max:2000'],
            'boundary_geojson' => ['nullable', 'string', 'max:65536', function ($attribute, $value, $fail) {
                if ($value === null || trim($value) === '') {
                    return;
                }
                try {
                    app(FarmBoundaryGeometry::class)->measure($value);
                } catch (InvalidArgumentException $exception) {
                    $fail($exception->getMessage());
                }
            }],
            'remove_boundary' => ['sometimes', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'producteur_id.required' => 'Le producteur est obligatoire.',
            'producteur_id.exists' => 'Veuillez sélectionner un producteur existant.',
            'nom.required' => 'Le nom de la ferme est obligatoire.',
            'nom.max' => 'Le nom ne peut pas dépasser 150 caractères.',
            'localisation.required' => 'La localisation est obligatoire.',
            'localisation.max' => 'La localisation ne peut pas dépasser 255 caractères.',
            'latitude.required_with' => 'La latitude est obligatoire lorsque la longitude est renseignée.',
            'latitude.between' => 'La latitude doit être comprise entre -90 et 90.',
            'longitude.required_with' => 'La longitude est obligatoire lorsque la latitude est renseignée.',
            'longitude.between' => 'La longitude doit être comprise entre -180 et 180.',
            'superficie.numeric' => 'La superficie doit être un nombre.',
            'superficie.min' => 'La superficie doit être positive ou nulle.',
            'superficie.decimal' => 'La superficie accepte au maximum deux décimales.',
            'description.max' => 'La description ne peut pas dépasser 2000 caractères.',
        ];
    }
}

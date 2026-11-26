<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PublisherRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', Rule::in(['publisher', 'distributor'])],
            'contact_person' => ['nullable', 'string', 'max:100'],
            'email' => ['nullable', 'string', 'email', 'max:100'],
            'phone' => ['nullable', 'string', 'max:50'],
            'region' => ['nullable', 'string', 'max:100'],
            'address' => ['nullable', 'string', 'max:1000'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * Nama atribut dalam Bahasa Indonesia untuk pesan validasi.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => 'nama penerbit / distributor',
            'type' => 'tipe',
            'contact_person' => 'kontak sales / narahubung',
            'email' => 'email',
            'phone' => 'nomor telepon',
            'region' => 'wilayah asal',
            'address' => 'alamat',
            'notes' => 'catatan',
        ];
    }

    /**
     * Pesan kustom validasi.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'type.in' => 'Tipe harus dipilih antara Penerbit atau Distributor.',
        ];
    }
}

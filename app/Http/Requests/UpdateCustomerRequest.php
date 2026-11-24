<?php

namespace App\Http\Requests;

use App\Models\Parameter;
use App\Rules\CheckEditingAtCustomerValidation;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateCustomerRequest extends FormRequest
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
        $parameter = new Parameter();
        $data = $parameter->getcombodata('STATUS', 'STATUS');
        $data = json_decode($data, true);
        foreach ($data as $item) {
            $status[] = $item['id'];
        }
        $rules = [
            'id' => new CheckEditingAtCustomerValidation(),
            'username' => ['required',Rule::unique('customer')->whereNotIn('id', [$this->id])],
            'nama' => ['required',Rule::unique('customer')->whereNotIn('id', [$this->id])],
            'ownernama' => ['required'],
            'ownerid' => ['required'],
            'hargaproductnama' => ['required'],
            'hargaproduct' => ['required'],
            'top' => ['required'],
            'status' => ['required', Rule::in($status)]
        ];
        return $rules; 
    }

    public function attributes()
    {
        return [
            'nama' => 'Nama',
            'nama2' => 'Nama 2',
            'telepon' => 'Telepon',
            'alamat' => 'Alamat',
            'keterangan' => 'Keterangan',
            'status' => 'Status',
            'ownernama' => 'owner',
            'ownerid' => 'owner',
            'hargaproductnama' => 'harga product',
            'hargaproductid' => 'harga product',
        ];
    }
}

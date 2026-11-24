<?php

namespace App\Rules;

use App\Http\Controllers\Api\ErrorController;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\DB;

class ValidationCreatePenjualanPembelian implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    public $keteranganError;
    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $prevcustomer = '';
        $prevnobuktipenjualan = '';
        for ($i = 0; $i < count(request()->pesananfinalheaderid); $i++) {
            $query = DB::table('pesananfinalheader')
                ->where('id', request()->pesananfinalheaderid[$i])
                ->first();

            $query2 = DB::table('pesananfinaldetail')
                ->where('pesananfinalid', request()->pesananfinalheaderid[$i])
                ->first();
            
            $nobuktipembelian = $query2->nobuktipembelian;
            
            
            $customer = $query->customerid;
            $tglpengiriman = $query->tglpengiriman;
            $nobuktipenjualan = $query->nobuktipenjualan;

            // dd($nobuktipenjualan);
            if ($nobuktipenjualan != '') {
                $this->keteranganError = app(ErrorController::class)->geterror('TBGCP')->keterangan;
                return false;
            }

            if ($nobuktipembelian != '') {
                $this->keteranganError = app(ErrorController::class)->geterror('TBGCPL')->keterangan;
                return false;
            }

            
            $prevcustomer = $customer;
            $prevtglpengiriman = $tglpengiriman;
            $prevnobuktipenjualan = $nobuktipenjualan;
        }
        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return  $this->keteranganError;
    }
}

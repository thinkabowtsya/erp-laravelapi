<?php

namespace App\Rules;

use App\Http\Controllers\Api\ErrorController;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\DB;

class ValidationCreatePembelianCombine implements Rule
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
            $query2 = DB::table('pesananfinaldetail')
                ->where('pesananfinalid', request()->pesananfinalheaderid[$i])
                ->first();
            
            $nobuktipembelian = $query2->nobuktipembelian;
            
            
            if ($nobuktipembelian != '') {
                return false;
            }
            
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
        return  app(ErrorController::class)->geterror('TBGCPL')->keterangan;
    }
}

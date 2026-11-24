<?php

namespace App\Http\Controllers\Api;


use App\Http\Controllers\Controller;
use App\Http\Requests\GetIndexRangeRequest;
use App\Models\HPP;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HPPController extends Controller
{
    /**
     * @ClassName 
     * HPPController
     */
    public function index(GetIndexRangeRequest $request)
    {
        $hpp = new HPP();
        return response([
            'data' => $hpp->get(),
            'attributes' => [
                'totalRows' => $hpp->totalRows,
                'totalPages' => $hpp->totalPages
            ]
        ]);
    }

    public function rekalkulasi(Request $request) {
        // dd('data');
        $tgldari = date('Y-m-d', strtotime($request->tgldari));
        $tglsampai = date('Y-m-d', strtotime($request->tglsampai));

        DB::beginTransaction();

        try {

            $hpp = new HPP();
            $data = $hpp->rekalkulasiHppStok($tgldari, $tglsampai);

            // dd($data);
            DB::commit();

            return response([
                'message' => 'Berhasil rekalkulasi hpp & stok',
                'data' => $data
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();

            throw $th;
        }
    }
}

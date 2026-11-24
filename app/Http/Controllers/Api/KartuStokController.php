<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\GetIndexRangeRequest;
use App\Models\KartuStok;
use Illuminate\Http\Request;

class KartuStokController extends Controller
{
    /**
     * @ClassName 
     * KartuStokController
     */
    public function index(GetIndexRangeRequest $request)
    {
        $kartuStok = new KartuStok();
        return response([
            'data' => $kartuStok->get(),
            'attributes' => [
                'totalRows' => $kartuStok->totalRows,
                'totalPages' => $kartuStok->totalPages
            ]
        ]);
    }
}

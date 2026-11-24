<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PesananFinalDetail extends MyModel
{
    use HasFactory;

    protected $table = 'pesananfinaldetail';

    protected $casts = [
        'created_at' => 'date:d-m-Y H:i:s',
        'updated_at' => 'date:d-m-Y H:i:s'
    ];

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    public function get()
    {
        $this->setRequestParameters();
        $query = DB::table($this->table . ' as pesananfinaldetail');

        if (isset(request()->forReport) && request()->forReport) {
            $tempQty = 'tempQty' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
            DB::statement("CREATE TEMPORARY TABLE $tempQty (
                pesananfinalid BIGINT UNSIGNED NULL,
                productid BIGINT UNSIGNED NULL,
                qty VARCHAR(100) NULL
            )");
            $select_tempQty = DB::table('pesananfinaldetail')
                ->select('pesananfinalid', 'productid', 'qty')
                ->where('pesananfinalid', '=', request()->pesananfinalid)
                ->groupBy('pesananfinalid', 'productid', 'qty')
                ->get();

            foreach ($select_tempQty as $row) {
                DB::table($tempQty)->insert([
                    'pesananfinalid' => $row->pesananfinalid,
                    'productid' => $row->productid,
                    'qty' => $row->qty
                ]);
            }
            $query = DB::table("$tempQty as tempQty")
                ->select(
                    "tempQty.pesananfinalid",
                    "tempQty.productid",
                    "tempQty.qty as qty",
                    "product.id as productid",
                    "product.nama as productnama",
                    "product.hargajual as producthargajual",
                    DB::raw('(qty * product.hargajual) AS totalproducthargajual'),
                    "supplier.id as supplierid",
                    "supplier.nama as suppliernama",
                )
                ->leftJoin('product', 'tempQty.productid', 'product.id')
                ->leftJoin('supplier', 'product.supplierid', 'supplier.id');
        } else if (isset(request()->forSuratpengantar) && request()->forSuratpengantar) {
            $query->select(
                "pesananfinaldetail.id",
                "pesananfinalheader.id as pesananfinalid",
                "pesananfinalheader.nobukti as pesananfinalheadernobukti",
                "product.id as productid",
                // "product.nama as productnama",
                DB::raw('CONCAT(UPPER(SUBSTRING(product.nama,1,1)),LOWER(SUBSTRING(product.nama,2))) as productnama'),
                "pesananfinaldetail.nobuktipembelian",
                "pesananfinaldetail.keterangan as keterangandetail",
                DB::raw('IFNULL(pesananfinaldetail.qtybeli, 0) AS qtybeli'),
                DB::raw('IFNULL(pesananfinaldetail.qtyjual, 0) AS qtyjual'),
                DB::raw('IFNULL(pesananfinaldetail.qtyreturjual, 0) AS qtyreturjual'),
                DB::raw('IFNULL(pesananfinaldetail.qtyreturbeli, 0) AS qtyreturbeli'),
                // DB::raw('IFNULL(pesananfinaldetail.qtystok, 0) AS qtystok'),
                "pesananfinaldetail.hargajual",
                "pesananfinaldetail.hargabeli",
                DB::raw('(pesananfinaldetail.qtybeli * pesananfinaldetail.hargabeli) AS totalhargabeli'),
                DB::raw('(pesananfinaldetail.qtyjual * pesananfinaldetail.hargajual) AS totalhargajual'),
                "satuan.id as satuanid",
                "satuan.nama as satuannama",
                "cekpesanan.id as cekpesananid",
                "cekpesanan.memo as cekpesananmemo",
                "modifier.id as modified_by_id",
                "modifier.name as modified_by",
                "pesananfinaldetail.created_at",
                "pesananfinaldetail.updated_at",
            )
                ->leftJoin(DB::raw("pesananfinalheader"), 'pesananfinaldetail.pesananfinalid', 'pesananfinalheader.id')
                ->leftJoin(DB::raw("product"), 'pesananfinaldetail.productid', 'product.id')
                ->leftJoin(DB::raw("satuan"), 'pesananfinaldetail.satuanid', 'satuan.id')
                ->leftJoin(DB::raw("parameter as cekpesanan"), 'pesananfinaldetail.cekpesanandetail', 'cekpesanan.id')
                ->leftJoin(DB::raw("user as modifier"), 'pesananfinaldetail.modifiedby', 'modifier.id');

            $query->orderBy('product.nama', 'asc');
            $query->where("pesananfinaldetail.pesananfinalid", "=", request()->pesananfinalid);
        } else {
            $query->select(
                "pesananfinaldetail.id",
                "pesananfinalheader.id as pesananfinalid",
                "pesananfinalheader.nobukti as pesananfinalheadernobukti",
                "product.id as productid",
                "product.nama as productnama",
                "pesananfinaldetail.nobuktipembelian",
                "pesananfinaldetail.keterangan as keterangandetail",
                DB::raw('IFNULL(pesananfinaldetail.qtybeli, 0) AS qtybeli'),
                DB::raw('IFNULL(pesananfinaldetail.qtyjual, 0) AS qtyjual'),
                DB::raw('IFNULL(pesananfinaldetail.qtyreturjual, 0) AS qtyreturjual'),
                DB::raw('IFNULL(pesananfinaldetail.qtyreturbeli, 0) AS qtyreturbeli'),
                // DB::raw('IFNULL(pesananfinaldetail.qtystok, 0) AS qtystok'),
                "pesananfinaldetail.hargajual",
                "pesananfinaldetail.hargabeli",
                DB::raw('(pesananfinaldetail.qtybeli * pesananfinaldetail.hargabeli) AS totalhargabeli'),
                DB::raw('(pesananfinaldetail.qtyjual * pesananfinaldetail.hargajual) AS totalhargajual'),
                "satuan.id as satuanid",
                "satuan.nama as satuannama",
                "cekpesanan.id as cekpesananid",
                "cekpesanan.memo as cekpesananmemo",
                "modifier.id as modified_by_id",
                "modifier.name as modified_by",
                "pesananfinaldetail.created_at",
                "pesananfinaldetail.updated_at",
            )
                ->leftJoin(DB::raw("pesananfinalheader"), 'pesananfinaldetail.pesananfinalid', 'pesananfinalheader.id')
                ->leftJoin(DB::raw("product"), 'pesananfinaldetail.productid', 'product.id')
                ->leftJoin(DB::raw("satuan"), 'pesananfinaldetail.satuanid', 'satuan.id')
                ->leftJoin(DB::raw("parameter as cekpesanan"), 'pesananfinaldetail.cekpesanandetail', 'cekpesanan.id')
                ->leftJoin(DB::raw("user as modifier"), 'pesananfinaldetail.modifiedby', 'modifier.id');

            $query->where("pesananfinaldetail.pesananfinalid", "=", request()->pesananfinalid);

            $this->totalRows = $query->count();
            $this->totalPages = request()->limit > 0 ? ceil($this->totalRows / request()->limit) : 1;
            $this->totalNominalJual = $query->sum(DB::raw('(pesananfinaldetail.qtyjual * pesananfinaldetail.hargajual)'));
            $this->totalNominalBeli = $query->sum(DB::raw('(pesananfinaldetail.qtybeli * pesananfinaldetail.hargabeli)'));

            if (request()->sortIndex != '') {
                $this->sort($query);
            }
            $this->filter($query);
            if (request()->limit > 0) {
                $this->paginate($query);
            }
        }

        $data = $query->get();
        return $data;
    }

    public function getReportPembelian()
    {
        $karyawan = request()->karyawan;
        $dari = date("Y-m-d", strtotime(request()->dari));

        if (isset(request()->forReportPb) && request()->forReportPb) {
            $tempQty = 'tempQty' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
            DB::statement("CREATE TEMPORARY TABLE $tempQty (
                pesananfinaldetailid BIGINT UNSIGNED NULL,
                productid BIGINT UNSIGNED NULL,
                productnama VARCHAR(250) NULL,
                producthargabeli FLOAT,
                qtybeli VARCHAR(100) NULL,
                qtyretur VARCHAR(100) NULL,
                qtystok VARCHAR(100) NULL,
                qty VARCHAR(1000) NULL,
                totalproductqty VARCHAR(100) NULL,
                supplierid BIGINT UNSIGNED NULL,
                suppliernama VARCHAR(255) NULL,
                suppliertelepon VARCHAR(255) NULL,
                karyawanid BIGINT UNSIGNED NULL,
                karyawannama VARCHAR(255) NULL,
                tglpengiriman DATE NULL
            )");

            $select_tempQty = DB::table('pesananfinaldetail as a')
                ->select(
                    DB::raw("MIN(a.id) as pesananfinaldetailid"),
                    'a.productid',
                    DB::raw("MIN(b.nama) as productnama"),
                    DB::raw("MIN(a.hargabeli) as producthargabeli"),
                    DB::raw("SUM(a.qtybeli) as qtybeli"),
                    DB::raw("MIN(a.qtyreturbeli) as qtyretur"),
                    DB::raw("MIN(COALESCE(e.qtystok, 0)) as qtystok"),
                    DB::raw("REPLACE(GROUP_CONCAT(CONCAT(trim(a.qtybeli)+0, ' + ')), ',', '') as qty"),
                    DB::raw('SUM(a.qtybeli) as totalproductqty'),
                    'b.supplierid',
                    DB::raw("MIN(g.nama) as suppliernama"),
                    DB::raw("MIN(g.telepon) as suppliertelepon"),
                    DB::raw("MIN(g.karyawanid) as karyawanid"),
                    DB::raw("MIN(h.nama) as karyawannama"),
                    'c.tglpengiriman'
                )
                ->leftjoin('product as b', 'a.productid', 'b.id')
                ->leftJoin('pesananfinalheader as c', 'a.pesananfinalid', 'c.id')
                ->leftJoin('pesananpembeliandetail as d', 'a.id', 'd.pesananfinaldetailid')
                ->leftJoin('pembeliandetail as e', 'd.pembeliandetailid', 'e.id')
                ->leftJoin('pembelianheader as f', 'e.pembelianid', 'f.id')
                ->leftjoin('supplier as g', 'b.supplierid', 'g.id')
                ->leftJoin('karyawan as h', 'g.karyawanid', 'h.id')
                ->where('c.status', 1)
                ->where('c.tglpengiriman', '=', $dari)
                ->where('h.nama', '=', $karyawan)
                ->groupBy('a.productid', 'c.tglpengiriman', 'b.supplierid')
                ->get();

            // dd($select_tempQty);

            foreach ($select_tempQty as $row) {
                // dd($row->qty);
                DB::table($tempQty)->insert([
                    'pesananfinaldetailid' => $row->pesananfinaldetailid,
                    'productid' => $row->productid,
                    'productnama' => $row->productnama,
                    'producthargabeli' => $row->producthargabeli,
                    'qtybeli' => $row->qtybeli,
                    'qtyretur' => $row->qtyretur,
                    'qtystok' => $row->qtystok,
                    'qty' => rtrim(trim($row->qty), '+') . '*****',
                    'totalproductqty' => $row->totalproductqty,
                    'supplierid' => $row->supplierid,
                    'suppliernama' => $row->suppliernama,
                    'suppliertelepon' => $row->suppliertelepon,
                    'karyawanid' => $row->karyawanid,
                    'karyawannama' => $row->karyawannama,
                    'tglpengiriman' => $dari
                ]);
            }

            $query = DB::table("$tempQty as tempQty")
                ->select(
                    "tempQty.pesananfinaldetailid",
                    "tempQty.productid",
                    "tempQty.productnama",
                    "tempQty.producthargabeli",
                    "tempQty.qtybeli",
                    "tempQty.qtyretur",
                    "tempQty.qtystok",
                    "tempQty.qty",
                    "tempQty.totalproductqty",
                    "tempQty.tglpengiriman",
                    DB::raw('(tempQty.totalproductqty * tempQty.producthargabeli) AS totalproducthargabeli'),
                    "tempQty.supplierid",
                    "tempQty.suppliernama",
                    "tempQty.suppliertelepon",
                    "tempQty.karyawanid",
                    "tempQty.karyawannama",
                    DB::raw('DATE_FORMAT(NOW(), "%d-%m-%Y %H.%i.%s") AS tglcetak'),
                )
                ->orderBy('tempQty.suppliernama')
                ->orderBy('tempQty.productnama', 'asc');

            // dd($query->get());
        } else if (isset(request()->forReportPbAll) && request()->forReportPbAll) {

            // dd('test');
            $tempQty = 'tempQty' . rand(1, getrandmax()) . str_replace('.', '', microtime(true));
            DB::statement("CREATE TEMPORARY TABLE $tempQty (
                pesananfinaldetailid BIGINT UNSIGNED NULL,
                productid BIGINT UNSIGNED NULL,
                productnama VARCHAR(250) NULL,
                producthargabeli FLOAT,
                qtybeli VARCHAR(100) NULL,
                qtyretur VARCHAR(100) NULL,
                qtystok VARCHAR(100) NULL,
                qty VARCHAR(1000) NULL,
                totalproductqty VARCHAR(100) NULL,
                supplierid BIGINT UNSIGNED NULL,
                suppliernama VARCHAR(255) NULL,
                suppliertelepon VARCHAR(255) NULL,
                karyawanid BIGINT UNSIGNED NULL,
                karyawannama VARCHAR(255) NULL,
                tglpengiriman DATE NULL
            )");

            $select_tempQty = DB::table('pesananfinaldetail as a')
                ->select(
                    DB::raw("MIN(a.id) as pesananfinaldetailid"),
                    'a.productid',
                    DB::raw("MIN(b.nama) as productnama"),
                    DB::raw("MIN(a.hargabeli) as producthargabeli"),
                    DB::raw("SUM(a.qtybeli) as qtybeli"),
                    DB::raw("MIN(a.qtyreturbeli) as qtyretur"),
                    DB::raw("MIN(COALESCE(e.qtystok, 0)) as qtystok"),
                    DB::raw("REPLACE(GROUP_CONCAT(CONCAT(trim(a.qtybeli)+0, ' + ')), ',', '') as qty"),
                    DB::raw('SUM(a.qtybeli) as totalproductqty'),
                    'b.supplierid as supplierid',
                    DB::raw("MIN(g.nama) as suppliernama"),
                    DB::raw("MIN(g.telepon) as suppliertelepon"),
                    DB::raw("MIN(g.karyawanid) as karyawanid"),
                    DB::raw("MIN(h.nama) as karyawannama"),
                    'c.tglpengiriman'
                )
                ->leftjoin('product as b', 'a.productid', 'b.id')
                ->leftJoin('pesananfinalheader as c', 'a.pesananfinalid', 'c.id')
                ->leftJoin('pesananpembeliandetail as d', 'a.id', 'd.pesananfinaldetailid')
                ->leftJoin('pembeliandetail as e', 'd.pembeliandetailid', 'e.id')
                ->leftJoin('pembelianheader as f', 'e.pembelianid', 'f.id')
                ->leftjoin('supplier as g', 'b.supplierid', 'g.id')
                ->leftJoin('karyawan as h', 'g.karyawanid', 'h.id')
                ->where('c.status', 1)
                ->where('c.tglpengiriman', '=', $dari)
                ->groupBy('a.productid', 'c.tglpengiriman', 'b.supplierid')
                ->get();


            foreach ($select_tempQty as $row) {
                DB::table($tempQty)->insert([
                    'pesananfinaldetailid' => $row->pesananfinaldetailid,
                    'productid' => $row->productid,
                    'productnama' => $row->productnama,
                    'producthargabeli' => $row->producthargabeli,
                    'qtybeli' => $row->qtybeli,
                    'qtyretur' => $row->qtyretur,
                    'qtystok' => $row->qtystok,
                    'qty' => rtrim(trim($row->qty), '+') . '*****',
                    'totalproductqty' => $row->totalproductqty,
                    'supplierid' => $row->supplierid,
                    'suppliernama' => $row->suppliernama,
                    'suppliertelepon' => $row->suppliertelepon,
                    'karyawanid' => $row->karyawanid,
                    'karyawannama' => $row->karyawannama,
                    'tglpengiriman' => $dari
                ]);
            }

            // dd(DB::table($tempQty)->get());

            $query = DB::table("$tempQty as tempQty")
                ->select(
                    "tempQty.pesananfinaldetailid",
                    "tempQty.productid",
                    "tempQty.productnama",
                    "tempQty.producthargabeli",
                    "tempQty.qtybeli",
                    "tempQty.qtyretur",
                    "tempQty.qtystok",
                    "tempQty.qty",
                    "tempQty.totalproductqty",
                    "tempQty.tglpengiriman",
                    DB::raw('(tempQty.totalproductqty * tempQty.producthargabeli) AS totalproducthargabeli'),
                    "tempQty.supplierid",
                    "tempQty.suppliernama",
                    "tempQty.suppliertelepon",
                    "tempQty.karyawanid",
                    "tempQty.karyawannama",
                    DB::raw('DATE_FORMAT(NOW(), "%d-%m-%Y %H.%i.%s") AS tglcetak'),
                )
                ->orderBy('tempQty.suppliernama')
                ->orderBy('tempQty.karyawannama', 'asc');
        }

        $data = $query->get();
        if ($data->isEmpty()) {
            throw ValidationException::withMessages(["supplierid" => "data kosong"]);
        }

        (new LogTrail())->processStore([
            'namatabel' => strtoupper('pesananfinaldetail'),
            'postingdari' => strtoupper('REPORT PEMBELIAN PESANAN FINAL DETAIL'),
            'idtrans' => 0,
            'nobuktitrans' => '',
            'aksi' => 'REPORT PEMBELIAN',
            'datajson' => $data,
            'modifiedby' => auth('api')->user()->id
        ]);

        return $data;
    }

    public function getAll($id)
    {
        $query = DB::table('pesananfinaldetail')
            ->select(
                "pesananfinaldetail.id",
                "pesananfinalheader.id as pesananfinalid",
                "pesananfinalheader.nobukti as pesananfinalheadernobukti",
                "product.id as productid",
                "product.nama as productnama",
                "pesananfinaldetail.nobuktipembelian",
                "pesananfinaldetail.keterangan as keterangandetail",
                "pesananfinaldetail.qtyjual",
                "pesananfinaldetail.qtybeli",
                "pesananfinaldetail.qtyreturbeli",
                "pesananfinaldetail.hargajual",
                "pesananfinaldetail.hargabeli",
                DB::raw('(pesananfinaldetail.qtyjual * pesananfinaldetail.hargajual) AS totalhargajual'),
                DB::raw('(pesananfinaldetail.qtybeli * pesananfinaldetail.hargabeli) AS totalhargabeli'),
                "satuan.nama as satuannama",
                "satuan.id as satuanid",
                "modifier.id as modified_by_id",
                "modifier.name as modified_by",
                "pesananfinaldetail.created_at",
                "pesananfinaldetail.updated_at",
            )
            ->leftJoin(DB::raw("pesananfinalheader"), 'pesananfinaldetail.pesananfinalid', 'pesananfinalheader.id')
            ->leftJoin(DB::raw("product"), 'pesananfinaldetail.productid', 'product.id')
            ->leftJoin(DB::raw("satuan"), 'pesananfinaldetail.satuanid', 'satuan.id')
            ->leftJoin(DB::raw("user as modifier"), 'pesananfinaldetail.modifiedby', 'modifier.id')
            ->where('pesananfinalid', '=', $id)
            ->orderby('product.nama', 'asc');

        $data = $query->get();
        return $data;
    }

    public function sort($query)
    {
        if ($this->params['sortIndex'] == 'id') {
            return $query->orderBy('pesananfinaldetail.pesananfinalid', $this->params['sortOrder']);
        } else if ($this->params['sortIndex'] == 'productnama') {
            return $query->orderBy('product.nama', $this->params['sortOrder']);
        } else if ($this->params['sortIndex'] == 'satuannama') {
            return $query->orderBy('satuan.nama', $this->params['sortOrder']);
        } else if ($this->params['sortIndex'] == 'totalhargajual') {
            return $query->orderBy(DB::raw('(pesananfinaldetail.qtyjual * pesananfinaldetail.hargajual)'), $this->params['sortOrder']);
        } else if ($this->params['sortIndex'] == 'keterangandetail') {
            return $query->orderBy(DB::raw('pesananfinaldetail.keterangan'), $this->params['sortOrder']);
        } else if ($this->params['sortIndex'] == 'totalhargabeli') {
            return $query->orderBy(DB::raw('(pesananfinaldetail.qtybeli * pesananfinaldetail.hargabeli)'), $this->params['sortOrder']);
        } else {

            return $query->orderBy($this->table . '.' . $this->params['sortIndex'], $this->params['sortOrder']);
        }
    }

    public function filter($query, $relationFields = [])
    {
        if (count($this->params['filters']) > 0 && @$this->params['filters']['rules'][0]['data'] != '') {
            switch ($this->params['filters']['groupOp']) {
                case "AND":
                    foreach ($this->params['filters']['rules'] as $index => $filters) {
                        if ($filters['field'] == 'type') {
                            $query = $query->where('B.grp', 'like', "%$filters[data]%");
                        } else if ($filters['field'] == 'created_at' || $filters['field'] == 'updated_at') {
                            $query = $query->whereRaw("format(" . $this->table . "." . $filters['field'] . ", 'dd-MM-yyyy HH:mm:ss') LIKE '%$filters[data]%'");
                        } else if ($filters['field'] == 'productnama') {
                            $query = $query->where('product.nama', 'like', "%$filters[data]%");
                        } else if ($filters['field'] == 'totalhargajual') {
                            $query->whereRaw('(pesananfinaldetail.qtyjual * pesananfinaldetail.hargajual) LIKE ?', ["%$filters[data]%"]);
                        } else if ($filters['field'] == 'totalhargabeli') {
                            $query->whereRaw('(pesananfinaldetail.qtybeli * pesananfinaldetail.hargabeli) LIKE ?', ["%$filters[data]%"]);
                        } else if ($filters['field'] == 'satuannama') {
                            $query = $query->where('satuan.nama', 'like', "%$filters[data]%");
                        } else {
                            // $query = $query->where($this->table . '.' . $filters['field'], 'like', "%$filters[data]%");
                            $query = $query->whereRaw($this->table . "." .  $filters['field'] . " LIKE '%" . escapeLike($filters['data']) . "%'");
                        }
                    }

                    break;
                case "OR":
                    foreach ($this->params['filters']['rules'] as $index => $filters) {
                        if ($filters['field'] == 'type') {
                            $query = $query->orWhere('B.grp', 'LIKE', "%$filters[data]%");
                        } else if ($filters['field'] == 'created_at' || $filters['field'] == 'updated_at') {
                            $query = $query->orWhereRaw("format(" . $this->table . "." . $filters['field'] . ", 'dd-MM-yyyy HH:mm:ss') LIKE '%$filters[data]%'");
                        } else if ($filters['field'] == 'productnama') {
                            $query = $query->orWhereRaw('product.nama', 'like', "%$filters[data]%");
                        } else if ($filters['field'] == 'totalhargajual') {
                            $query->orWhereRaw('(pesananfinaldetail.qtyjual * pesananfinaldetail.hargajual) LIKE ?', ["%$filters[data]%"]);
                        } else if ($filters['field'] == 'totalhargabeli') {
                            $query->orWhereRaw('(pesananfinaldetail.qtybeli * pesananfinaldetail.hargabeli) LIKE ?', ["%$filters[data]%"]);
                        } else if ($filters['field'] == 'satuannama') {
                            $query = $query->orWhereRaw('satuan.nama', 'like', "%$filters[data]%");
                        } else if ($filters['field'] == 'keterangandetail') {
                            $query = $query->orWhereRaw('pesananfinaldetail.keterangan', 'like', "%$filters[data]%");
                        } else if ($filters['field'] == 'keterangandetail') {
                            $query = $query->orWhereRaw('penjualandetail.keterangan', 'like', "%$filters[data]%");
                        } else {
                            // $query = $query->orWhere($this->table . '.' . $filters['field'], 'LIKE', "%$filters[data]%");
                            $query = $query->OrwhereRaw($this->table . "." .  $filters['field'] . " LIKE '%" . escapeLike($filters['data']) . "%'");
                        }
                    }

                    break;
                default:

                    break;
            }

            $this->totalRows = $query->count();
            $this->totalPages = $this->params['limit'] > 0 ? ceil($this->totalRows / $this->params['limit']) : 1;
        }

        return $query;
    }

    public function paginate($query)
    {
        return $query->skip($this->params['offset'])->take($this->params['limit']);
    }


    public function processStore(PesananFinalHeader $pesananFinalHeader, array $data): PesananFinalDetail
    {

        //STORE PESANAN FINAL DETAIL
        $pesananFinalDetail = new PesananFinalDetail();
        $pesananFinalDetail->pesananfinalid = $data['pesananfinalid'];
        $pesananFinalDetail->productid = $data['productid'];
        $pesananFinalDetail->nobuktipembelian = $data['nobuktipembelian'] ?? '';
        $pesananFinalDetail->qtyjual = $data['qtyjual'];
        $pesananFinalDetail->qtybeli = $data['qtybeli'];
        $pesananFinalDetail->qtyreturjual = $data['qtyreturjual'];
        $pesananFinalDetail->qtyreturbeli = $data['qtyreturbeli'];
        // $pesananFinalDetail->qtystok = $data['qtystok'];
        $pesananFinalDetail->hargajual = $data['hargajual'];
        $pesananFinalDetail->hargabeli = $data['hargabeli'];
        $pesananFinalDetail->satuanid = $data['satuanid'];
        $pesananFinalDetail->keterangan = $data['keterangan'];
        $pesananFinalDetail->cekpesanandetail = $data['cekpesanandetail'];
        $pesananFinalDetail->modifiedby = $data['modifiedby'];
        $pesananFinalDetail->save();

        if (!$pesananFinalDetail->save()) {
            throw new \Exception("Error storing Pesanan Final Detail.");
        }

        // STORE CEK PESANAN
        // $data = (new CekPesanan())->processStore([
        //     'pesananfinalid' => $pesananFinalDetail->pesananfinalid,
        //     'pesananfinaldetailid' => $pesananFinalDetail->id,
        //     'customerid' => $pesananFinalHeader->customerid,
        //     'productid' => $pesananFinalDetail->productid,
        //     'qty' => $pesananFinalDetail->qtybeli,
        //     'satuanid' => $pesananFinalDetail->satuanid,
        //     'modifiedby' => auth('api')->user()->id,
        // ]);

        return $pesananFinalDetail;
    }
}

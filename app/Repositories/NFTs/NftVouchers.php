<?php


namespace App\Repositories\NFTs;

use App\Models\NftVoucher;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class NftVouchers extends NftBase
{

    private $model;

    public function __construct()
    {
        $this->model = new NftVoucher();
    }

    public function create($r)
    {
        $vouchers = $r->input('vouchers');
        if ($r->filled('vouchers') &&  sizeof($vouchers) > 0) {

            foreach ($vouchers as $voucher) {

                try {

                    $model = new $this->model;
                    $model->token_id = $voucher['tokenId'];
                    $model->uri = $voucher['uri'];
                    $model->price  = json_encode($voucher['minPrice']);
                    $model->signature = $voucher['signature'];
                    $model->save();
                } catch (Exception $e) {
                    $exception = $e->getMessage();
                    return response()->json($exception, 201);
                }
            }
            return response()->json(['message' => 'Vouchers are stored', 'status' => true], 200);
        }
    }
    public function getVouchersToMint($r)
    {
        $quantity = $r->query('num');


        $query = $this->model->whereNotIn('is_minted', ["1"])->get();
        if (count($query) > $quantity) {
            $query =  $query->random($quantity);
        }
        $query->makeHidden(['updated_at', 'created_at']);
        return response($query, 200);
    }
    public function changeStatus($r, $id)
    {
     
        if ($r->filled('status') && isset($id) && !empty($id) && $id != 'undefined') {
            try {
                $voucher = NftVoucher::findOrFail($id);
                $voucher->update(['is_minted' =>'1']);
                return response()->json(['message' => 'Voucher is updated', 'status' => true], 200);
            } catch (Exception $excep) {
                return response()->json(['message' => 'failed to update', 'status' => false], 201);
            }
        }
    }
}

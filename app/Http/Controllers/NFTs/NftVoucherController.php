<?php

namespace App\Http\Controllers\NFTs;

use App\Http\Controllers\Controller;
use App\Models\NftVoucher;
use App\Repositories\NFTs\NftVouchers;
use Illuminate\Http\Request;

class NftVoucherController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    private $repo;
    public function __construct()
    {
        $this->repo = new NftVouchers();        
    }
    public function index()
    {
        //
    }
    public function voucherToMint(Request $r){
      return   $this->repo->getVouchersToMint($r);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $r)
    {
        
       return  $this->repo->create($r);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\NftVoucher  $nftVoucher
     * @return \Illuminate\Http\Response
     */
    public function show(NftVoucher $nftVoucher)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\NftVoucher  $nftVoucher
     * @return \Illuminate\Http\Response
     */
    public function edit(NftVoucher $nftVoucher)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\NftVoucher  $nftVoucher
     * @return \Illuminate\Http\Response
     */
    public function update(Request $r,$id)
    {
        return $this->repo->changeStatus($r,$id);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\NftVoucher  $nftVoucher
     * @return \Illuminate\Http\Response
     */
    public function destroy(NftVoucher $nftVoucher)
    {
        //
    }
}

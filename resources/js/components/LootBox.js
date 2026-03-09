import React, { useEffect, useState, useRef } from "react";

import { use } from "@maticnetwork/maticjs";
import { Web3ClientPlugin } from "@maticnetwork/maticjs-web3";
import useLazyNft from "../hooks/useLazyNft";
import { useWallet } from "../contexts/wallet";
import useVouchers from "../hooks/useVouchers";
import axios from "axios";
import { circularProgressClasses } from "@mui/material";
use(Web3ClientPlugin);

const LootBox = () => {
  const classes = {
    success: "#11bd11b8",
    failed: "#890d0e",
  };
  const { createVouchers, vouchersToMint, voucher,updateVoucher} = useVouchers();
  const { address } = useWallet();
  const { getName, redeem, name, redeemer, redeemNew } = useLazyNft();
  const [response, setResponse] = useState({
    response: false,
    error: false,
    message: "empty message",
  });

  const mintQuantity = useRef(null);
  const BTvalue = useRef(null);

  const getcontractName = async () => {
    getName();
  };

  const mintToken = async () => {
    //  let Vouchers =  await vouchersToMint(mintQuantity.current.value)
    let Vouchers = await vouchersToMint(1);
    console.log('Vouchers :>> ', Vouchers);
    if(Vouchers.length > 0){
      let mintable = Vouchers[0];
    let voucher = {
      tokenId: mintable.token_id,
      minPrice: mintable.price,
      uri: mintable.uri,
      signature: mintable.signature,
    };
    console.log("Voucher :>> ", voucher);
     let receipt = await  redeemNew(voucher);
     if(receipt.status === 1){
      updateVoucher(mintable.id)
     }
    }else{
      alert('Vouchers not available')
    }

    //batch**************
    //  const mintableVouchers = Vouchers.map((voucher)=>{
    //   return {
    //     tokenId : voucher.token_id,
    //     minPrice: voucher.price,
    //     uri: voucher.uri,
    //     signature: voucher.signature
    //   }
    //  });
    //  redeemer(mintableVouchers)

    // single old**************
    //  let mintable = Vouchers[0];
    //  let voucher = {
    //   tokenId : mintable.token_id,
    //   minPrice: mintable.price,
    //   uri: mintable.uri,
    //   signature: mintable.signature
    //  }
    //  await redeem(voucher);
  };
  const getMessage = (key, status) => {
    console.log("status is >>>>>>>>>>>>>", status);

    return messageList[key][status];
  };
  use(Web3ClientPlugin);
  const showLoader = () => {
    $(".large-loader").show();
  };
  const hideLoader = () => {
    $(".large-loader").hide();
  };

  return (
    <div>
      <div className="dashboard-content-wrapper">
        <div className="container g-2">
          <div className="row mt-3 py-3 g-0">
            <div className="col-12">
              <div className="dashboard-title ">
                <i className="fa fa-th-list me-2"></i> <b>LootBox Screen</b>
              </div>
            </div>
          </div>
          {/* response div here */}
        </div>
      </div>

      <div className="container mt-3 py-3 g-2">
        <div className="row  pt-2 px-2 g-2">
          <button
            className=" bg-primary btn text-white rounded-0"
            onClick={createVouchers}
            type="button"
            id="button-addon2"
            style={{ color: "#fff" }}
          >
            Creater Vouchers
          </button>
        </div>
        <div className="row  pt-2 px-2 g-2">
          <button
            className=" bg-primary btn text-white rounded-0"
            onClick={mintToken}
            type="button"
            id="button-addon2"
            style={{ color: "#fff" }}
          >
            MINT NFT
          </button>
        </div>
      </div>
      <div className="container mt-3 py-3 g-2" hidden>
        <div className="row bg-primary pt-2 px-2 g-2">
          <div className="col-md-10">
            <div className="input-group mb-3">
              <input
                type="text"
                ref={mintQuantity}
                className="form-control rounded-0"
                placeholder="Enter tokens to mint"
                aria-describedby="button-addon2"
              />
            </div>
          </div>
          <div className="col-md-2 ">
            <button
              className=" btn text-white rounded-0"
              onClick={mintToken}
              type="button"
              id="button-addon2"
              style={{ color: "#fff", background: "#11bd11b8" }}
            >
              OK
            </button>
          </div>
        </div>
      </div>
    </div>
  );
};
export default LootBox;

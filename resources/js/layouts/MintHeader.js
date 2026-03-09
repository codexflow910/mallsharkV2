import React, { useEffect, useState } from "react";

import { use } from "@maticnetwork/maticjs";
import { Web3ClientPlugin } from "@maticnetwork/maticjs-web3";

import { useWallet } from "../contexts/wallet";
use(Web3ClientPlugin);

const ICO_header = (props) => {
  const { isAdmin } = props;
  const {
    address,
    disconnect,
    network
  } = useWallet();


  return (
    <div>
      <div className="header-wrapper">
        <div className="container g-2">
          <div className="row py-3 align-items-center">
            <div className="col-md-3">
              <div className="header-title">
                <h6 className="fw-bolder" style={{ marginBottom: "0rem" }}>
                  Welcome in ICO
                </h6>
              </div>
            </div>
            <div className="col-md-3 text-center">
              <div className="header-title">
                <h6 className="fw-bolder text-capitalize" style={{ marginBottom: "0rem" }}>
                 Network: {network.name}
                </h6>
              </div>
            </div>

            <div className="col-md-6">
              <div className="login-id text-end">
                <span>{address}</span>
                <a
                  href="#"
                  onClick={disconnect}
                  className="btn btn-small rounded-0 btn-danger btn-sm ms-4"
                >
                  Disconnecttt
                </a>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div className="border-bottom border-1">
        <div className="container g-2">
          <div className="row pt-3 g-0">
            <div className="col-md-3">
              <div className="ico-rate">
                <p>ICO Rate: 1 Matic</p>
              </div>
            </div>
            <div className="col-md-3 text-center">
              <div className="wallet">
                <span>Your ICO Balance</span>
              </div>
            </div>
            <div className="col-md-6 text-center">
              <div className="token">
                <span>Token</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
};
export default ICO_header;

import React, { useEffect, useState } from "react";
import ReactDOM from "react-dom";
import Web3 from "web3";
import Navbar from "../components/Navbar";
import WalletConnectProvider from "@maticnetwork/walletconnect-provider";
const config = require("../components/config");
import MetaMaskOnboarding from "@metamask/onboarding";
// const MaticPoSClient = require("@maticnetwork/maticjs").MaticPOSClient;
const { POSClient, MaticPoSClient } = require("@maticnetwork/maticjs");
const Network = require("@maticnetwork/meta/network");
const Matic = require("@maticnetwork/maticjs");
import { use } from "@maticnetwork/maticjs";
import { Web3ClientPlugin } from "@maticnetwork/maticjs-web3";
import { ABIManager } from "@maticnetwork/maticjs";
import axios from "axios";
import { WalletProvider } from "../../js/contexts/wallet";
import { useWallet } from "../contexts/wallet";
import { useContracts } from "../contexts/contracts";
import useIco from "../hooks/useIco";
use(Web3ClientPlugin);

const ICO_header = (props) => {
  const { isAdmin } = props;
  const [balance,setBalance] =useState(null) 
  const {
    startConnecting: startConnectingWallet,
    address,
    connectMetamask,
    switchNetwork,
    isConnecting,
    network,
    disconnect,
  } = useWallet();
  const {
    icoStatus,
    getIcoRate,
    getOpeningTime,
    rate,
    status,
    openingTime,
    closingTime,
    depositWallet,
    icoToken,
    weiRaised,
    checkBalance,
  } = useIco();
  const { icoContract , IcoTokenSymbol, IcoTokenAddress,IcoTokenName} = useContracts();
  useEffect(async () => {
    if (icoContract) {
      // console.log('use Effect running')
      // icoStatus()
      // getIcoRate()
      await getOpeningTime();
      if(address){

          checkBalance({benificiary:address},(resp)=>{
            (resp?.getCall && resp?.message) ? setBalance(resp.message) : setBalance('N/A')
          });
      }
      
    } else {
      console.log("icoContract is not loaded", icoContract);
    }
  }, [address, network]);
  return (
    <div>
      <div className="header-wrapper">
        {/* <input  class="primary-btn btn btn-primary"   disabled={isDisabled} onClick={onClick}  type="button" name="submit" value={buttonText}
          hidden={isDisabled}
          ></input> */}
        <div className="container g-2">
          <div className="row py-3 align-items-center">
            <div className="col-md-6">
              <div className="header-title">
                <h6 className="fw-bolder" style={{ marginBottom: "0rem" }}>
                  Welcome in ICO
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
                  Disconnect
                </a>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div className="" style={{ background: "#cfcfcf57" }}>
        <div className="container g-2">
          <div className="row pt-3 g-0">
            <div className={!isAdmin ? "col-md-6" : "col-md-3"}>
              <div className="status">
                <p className="" style={{ color: "#11bd11b8" }}>
                  Current Status: {status}
                </p>
              </div>
            </div>

            <div className="col-md-6">
              <div className="row">
                <div className="col-md-6">
                  <p>ICO Opening Time: {openingTime}</p>
                </div>
                <div className="col-md-6">
                  <p>ICO Closing Time: {closingTime}</p>
                </div>
              </div>
            </div>
            <div className="col-md-3" hidden={!isAdmin}>
              <div className="raised text-end">
                <h6>Matic Raised: {weiRaised}</h6>
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
                <p>ICO Rate: 1 Matic = {rate} {IcoTokenSymbol}</p>
              </div>
            </div>
            <div className="col-md-3 text-center " hidden={isAdmin}>
              <div className="wallet">
                <span>Your ICO Balance: {balance} {IcoTokenSymbol}</span>
              </div>
            </div>
            <div className="col-md-6 text-center">
              <div className="token">
                <span>{IcoTokenName}({IcoTokenSymbol}):</span> <span>{icoToken}</span>
              </div>
            </div>
            
            <div className="col-md-3 text-end " hidden={!isAdmin}>
              <div className="wallet">
                <span>Wallet:</span> <span>{depositWallet}</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
};
export default ICO_header;
// if (document.getElementById('ico')) {
//     ReactDOM.render(<ICO />, document.getElementById('ico'));
// }

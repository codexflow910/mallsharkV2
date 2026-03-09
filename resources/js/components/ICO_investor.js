import React, { useEffect, useState, useRef } from "react";
import ReactDOM from "react-dom";
import Web3 from "web3";
import Navbar from "./Navbar";
import WalletConnectProvider from "@maticnetwork/walletconnect-provider";
const config = require("./config.js");
import MetaMaskOnboarding from "@metamask/onboarding";
// const MaticPoSClient = require("@maticnetwork/maticjs").MaticPOSClient;
const { POSClient, MaticPoSClient } = require("@maticnetwork/maticjs");
const Network = require("@maticnetwork/meta/network");
const Matic = require("@maticnetwork/maticjs");
import { use } from "@maticnetwork/maticjs";
import { Web3ClientPlugin } from "@maticnetwork/maticjs-web3";
import { ABIManager } from "@maticnetwork/maticjs";
import axios from "axios";
import useIco from "../hooks/useIco";
import { useWallet } from "../contexts/wallet";
import { useContracts } from "../contexts/contracts";
use(Web3ClientPlugin);

const InvestorICO = () => {
  const classes = {
    success: "#11bd11b8",
    failed: "#890d0e",
  };
  const {
    startConnecting: startConnectingWallet,
    address,
    connectMetamask,
    switchNetwork,
    isConnecting,
    network,
  } = useWallet();
  const {
    addWhitelisted,
    buyTokens,
    receipt,
    setCurrentCall,
    currentCall,
    checkBalance,
    removeWhiteListed,
    addWhiteListAdmin,
    removeFromAdminList,
    isWhitelistAdmin,
    isWhitelisted,
    isHalt,
    haltStatus,
    halt,
    resume,
    withdrawTokens
  } = useIco();
  const { IcoTokenSymbol} = useContracts();
  const [response, setResponse] = useState({
    response: false,
    error: false,
    message: "empty message",
  });
  useEffect(() => {
    let isMounted = true;
    if (receipt?.code && receipt.code == 4001) {
      let params = {
        response: true,
        error: true,
        message: receipt.message,
      };
      if (isMounted)   if (isMounted)setResponse(params);
      window.scrollTo(0, 0);
      return;
    }
    if (receipt?.code && receipt.code == 4000) {
      let params = {
        response: true,
        error: true,
        message: getMessage(currentCall, "invalidAddress"),
      };
       if (isMounted)setResponse(params);
      window.scrollTo(0, 0);
      return;
    }

    if (receipt?.status) {
      console.log("status is true", receipt);
      let a = receipt;
      let params = {
        response: true,
        message: getMessage(currentCall, "success"),
      };

       if (isMounted)setResponse(params);
    } else if (!receipt?.status) {
      if (receipt?.error?.message) {
        console.log("error captured >>>");

        let params = {
          response: true,
          error: true,
          message: receipt.error.data.message,
        };
         if (isMounted)setResponse(params);
      } else if (receipt?.getCall && receipt?.message){
        console.log('get Call with message executed =>>',receipt)
        let params = {
            response : true,
            error: false,
            message: getMessage(currentCall,receipt?.message ? 'success':'failed')
        }
         if (isMounted)setResponse(params)
    }else if (receipt?.getCall){
        console.log('get Call with status executed =>>',receipt)
        let params = {
            response : true,
            error: false,
            message: getMessage(currentCall,receipt?.message ? 'success':'failed')
        }
         if (isMounted)setResponse(params)
    }
      console.log("status is false");
    }
    // debugger
    window.scrollTo(0, 0);
    console.log("receipt changed", receipt);
  }, [receipt]);
  useEffect(() => {
    let isMounted = true;
    if (isMounted) isHalt();
  }, [address]);
  const WLbenifiacry = useRef("");
  const BTbenificiary = useRef(null);
  const BTvalue = useRef(null);
  const CBbenificiary = useRef(null);
  const WLAbenificiary = useRef(null);
  const RWbenificiary = useRef(null);
  const RAbenificiary = useRef(null);
  const CAbenificiary = useRef(null);
  const CWbenificiary = useRef(null);

 

  const purchaseTokens = async () => {
    setCurrentCall("buyTokens");

    buyTokens(
      {
        BTbenificiary: address,
        BTvalue: BTvalue.current.value,
      },
      (resp) => {
        console.log("called callback", resp);
      }
    );
  };
  const getBalance = async () => {
    setCurrentCall("checkBalance");

    checkBalance(
      {
        benificiary: address,
      },
      (resp) => {
        console.log("called callback", resp);
      }
    );
  };
  const checkWhitelistStatus = async () => {
    setCurrentCall("isWhitelisted");

    isWhitelisted(
      {
        benificiary: address,
      },
      (resp) => {
        console.log("called callback", resp);
      }
    );
  };
  const getTokens = async () => {
    setCurrentCall("withdrawTokens");
    withdrawTokens(address, (resp) => {
       
    });
};
const messageList = {
    addWhiteList: {
        error: "Failed to add in whitelist",
        success: "Added in whiteList",
        invalidAddress: "Address is invalid",

    },
    removeWhitelisted: {
        error: "Failed to remove from whitlist",
        success: "Removed  from  whiteList",
        invalidAddress: "Address is invalid",

    },
    withdrawTokens: {
        error: "Failed to withdraw tokens",
        success: "Token withdrawn successfully!",
        invalidAddress: "Address is invalid",

    },
    removeFromAdminList: {
        error: "Failed to remove from Admin list",
        success: "Removed  from  admin list",
        invalidAddress: "Address is invalid",

    },
    addWhiteListAdmin: {
        error: "Faled to add in Admin list",
        success: "Added in whiteList as Admin",
        invalidAddress: "Address is invalid",

    },
    buyTokens: {
        error: "Failed to buy tokens",
        success: "Token Purchased",
        invalidAddress: "Address is invalid",
    },
    checkBalance  :{
        success :  `Balance is ${receipt?.message} ${IcoTokenSymbol}`,
        failed : "Failed to load balance",
        invalidAddress: "Address is invalid",
    },
    isWhitelistAdmin  :{
        success :  `Is Admin`,
        failed : 'Not Admin',
        invalidAddress: "Address is invalid",
    },
    isWhitelisted  :{
        success :  `Is Whitelisted`,
        failed : 'Not whitelisted',
        invalidAddress: "Address is invalid",
    },
    halt  :{
        success :  `ICO Halted successfully`,
        failed : 'Unable to Halt'
    },
    resume  :{
        success :  `ICO Started successfully`,
        failed : 'Unable to resume'
    },
};
  const getMessage = (key, status) => {
    console.log("status is >>>>>>>>>>>>>", status);
    //  let code  = Object.keys(messageList);
    return messageList[key][status];
  };
  use(Web3ClientPlugin);

  let content;
  const [Networkid, setNetworkid] = useState(0);
  const [account, setAccount] = useState("");
  const [loading, setLoading] = useState(true);
  const [inputValue, setInputValue] = useState("");
  const [amount, setInputAmount] = useState("");
  const [burnHash, setBurnHash] = useState("");
  const [polygonProvider, setPolygonProvider] = useState();
  const [maticProvider, setMaticProvider] = useState();
  const [ethereumprovider, setEthereumProvider] = useState();
  const [networkk, setNetwork] = useState("");
  const [metamaskStatus, setMetamaskStatus] = useState();
  const [maticBalance, setMaticBalance] = useState(0);
  const [USDCbalance, setUSDCbalance] = useState(0);
  const [posClient, setPostClient] = useState();

  const ONBOARD_TEXT = "Click here to install MetaMask!";
  const CONNECT_TEXT = "Connect MetaMask";
  const CONNECTED_TEXT = "Connected";

  const [buttonText, setButtonText] = React.useState(ONBOARD_TEXT);
  const [isDisabled, setDisabled] = React.useState(false);
  const [accounts, setAccounts] = React.useState([]);

  const onboarding = React.useRef();

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
                <i className="fa fa-th-list me-2"></i> <b>ICO Dashboard</b>
              </div>
            </div>
            {response.response ? <div
              className=" py-3 rounded-0 mt-3 p-4"
              style={{
                background: `${response.error ? classes.failed : classes.success
                    }`,
            }}
            >
              <div className="col-12">
                <div className="success-title " style={{ color: "#fff" }}>
                  <i className="fa fa-check me-2"></i>{" "}
                  <span>{response.response ? response.message : ""}</span>
                </div>
              </div>
            </div> : '' }
          </div>
        </div>
      </div>

      <div className="container mt-2 g-2">
        <label className="form-label fw-bolder">Buy Tokens</label>
        <div
          className="row bg-primary pt-3 px-3 g-2"
          style={{ background: "#d3d3d3!important" }}
        >
          <div className="col-md-12 ">
            <div className="input-group mb-3">
              <input
                type="text"
                ref={BTvalue}
                className="form-control rounded-0"
                placeholder="Value"
                aria-label="Recipient's username"
                aria-describedby="button-addon2"
              />
              <button
                className=" btn text-white rounded-0"
                type="button"
                onClick={purchaseTokens}
                id="button-addon2"
                style={{ color: "#fff", background: "#11bd11b8" }}
              >
                OK
              </button>
            </div>
          </div>
        </div>
        <div className="row my-3 g-2">
          <label className="form-label fw-bolder">More Actions</label>
          <div className="col-md-4">
            <div className="input-group mb-3 border-1 border py-3 px-2">
              <button
                className="btn btn-primary rounded-0"
                type="button"
                onClick={getBalance}
                id="button-addon2"
                style={{ color: "#fff", width: "100%" }}
              >
                Check Balance
              </button>
            </div>
          </div>
          <div className="col-md-4">
            <div className="input-group mb-3 border-1 border py-3 px-2">
              <button
                className="btn btn-primary rounded-0"
                type="button"
                onClick={getTokens}
                id="button-addon2"
                style={{ color: "#fff", width: "100%" }}
              >
                Withdraw Tokens
              </button>
            </div>
          </div>
          <div className="col-md-4  ">
            <div className="input-group mb-3 border-1 border py-3 px-2">
              <button
                className="btn btn-primary rounded-0"
                onClick={checkWhitelistStatus}
                type="button"
                id="button-addon2"
                style={{ color: "#fff", width: "100%" }}
              >
                Check Whitelist Status
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
};
export default InvestorICO;
// if (document.getElementById('ico')) {
//     ReactDOM.render(<AdminICO />, document.getElementById('ico'));
// }

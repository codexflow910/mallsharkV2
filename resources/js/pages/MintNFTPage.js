import React, { useEffect, useState, StrictMode } from "react";
import ReactDOM from "react-dom";
import Web3 from "web3";
import Navbar from "../components/Navbar";
import WalletConnectProvider from "@maticnetwork/walletconnect-provider";
const config = require("../components/config.js");
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

import IcoHeader from "../layouts/IcoHeader";
import MintHeader from "../layouts/MintHeader";
import IcoPage from "../pages/IcoPage";
import IcoAdmin from "../components/ICO_admin";
import LootBox from "../components/LootBox";
import ICO_investor from "../components/ICO_investor";
import { ContractsProvider } from "../contexts/contracts";
import { NotificationsProvider } from "../contexts/notifications";
import Notification from "../layouts/Notification";
import { SnackbarProvider } from "notistack";
import { makeStyles } from "@material-ui/core/styles";
import { useWallet } from "../contexts/wallet";
import useIco from "../hooks/useIco";
import { IS_DEV , MATIC_CHAIN_ID, MAITCMUM_CHAIN_ID} from '../utills/config'
const MintNFTPage = () => {
  const [isAdmin, setIsAdmin] = useState(false);
  const CHAIN_ID = IS_DEV ? MAITCMUM_CHAIN_ID : MATIC_CHAIN_ID; 
  const { address, network,networkId, startConnecting, disconnect, ConnectButtonText , switchNetwork,connectMetamask } =
    useWallet();
  return (
    <>
      <button
        className=" btn text-white rounded-0"
        onClick={switchNetwork}
        type="button"
        id="button-addon2"
        hidden={(networkId != CHAIN_ID && address) ? false : true}
        style={{ color: "#fff", background: "#11bd11b8" }}
      >
      Switch Network
      </button>
      <button
        className=" btn text-white rounded-0"
        onClick={connectMetamask}
        type="button"
        id="button-addon2"
        hidden={address}
        style={{ color: "#fff", background: "#11bd11b8" }}
      >
      Connect Metamask
      </button>
      <a href="https://metamask.app.link/dapp/google.com">
      Google in Metamask
      </a>
      {(address && networkId ==CHAIN_ID )? (
        <>
          <StrictMode>
            <MintHeader/>

           
                <LootBox />
            
          </StrictMode>
        </>
      ) : (
        <></>
      )}
    </>
  );
};
export default MintNFTPage;
// if (document.getElementById('ico')) {
//     ReactDOM.render(<ICO />, document.getElementById('ico'));
// }

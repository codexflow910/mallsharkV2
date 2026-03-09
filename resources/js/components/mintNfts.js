import React, { useEffect, useState  , StrictMode } from "react";
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
import { WalletProvider } from "../../js/contexts/wallet";

import IcoHeader from "../layouts/IcoHeader";
import IcoPage from "../pages/IcoPage";
import MintNFTPage from "../pages/MintNFTPage";
import IcoAdmin from "./ICO_admin";
import {ContractsProvider} from '../contexts/contracts'
import { NotificationsProvider } from '../contexts/notifications';
import Notification from '../layouts/Notification';
import { SnackbarProvider } from 'notistack';
import {makeStyles } from '@material-ui/core/styles';

use(Web3ClientPlugin);
const useStyles = makeStyles((theme) => ({
    snackbar: {
      top: 70,
    },
  }));
const MINT_NFTS = () => {
    const classes = useStyles();
  return (
    <WalletProvider>
    <ContractsProvider>
    <SnackbarProvider
                classes={{ root: classes.snackbar }}
                maxSnack={4}
                anchorOrigin={{
                  vertical: 'top',
                  horizontal: 'right',
                }}
                content={(key, data) => (
                  <div>
                    <Notification id={key} notification={data} />
                  </div>
                )}
              >
    <NotificationsProvider>
      <MintNFTPage/>
        </NotificationsProvider>
        </SnackbarProvider>
      </ContractsProvider>
    </WalletProvider>
  );
};
export default MINT_NFTS;
if (document.getElementById("mint_nfts")) {
  ReactDOM.render(<MINT_NFTS />, document.getElementById("mint_nfts"));
}

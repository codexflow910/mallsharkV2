import React, { useEffect, useState } from "react";
import ReactDOM from "react-dom";
import Web3 from "web3";
// WalletConnect imports
import {
  EthereumClient,
  modalConnectors,
  walletConnectProvider,
} from "@web3modal/ethereum";
import { Web3Modal } from "@web3modal/react";

import { configureChains, createClient, WagmiConfig } from "wagmi";

import { polygon,mainnet } from "wagmi/chains";

import HomePage from './Home';
// *********************************
const chains = [polygon];
import { Biconomy } from "@biconomy/mexa";
// Wagmi client
const { provider } = configureChains(chains, [
  walletConnectProvider({ projectId: "c02abb68bb4075aebcbd6a2ded56d08d" }),
]);
// const rpc_key_mumbai = "https://polygon-mainnet.g.alchemy.com/v2/g2eh_HAncn98Bd--DuOBQ_QxdAQLr0Fe" //polygon
// const biconomy_key_mumbai = "QMvL6ucUW.f288b185-c168-428a-b279-98430413db2b" //mallshark gateway apikey
// let maticProvider = new Web3.providers.HttpProvider(rpc_key_mumbai);
// const biconomy = new Biconomy(maticProvider, {
//   apiKey: biconomy_key_mumbai,
//   debug: true,
// })
// console.log('bicooooo',biconomy);
const wagmiClient = createClient({
  autoConnect: false,
  logger: { 
    warn: (message) => console.log('Wagmi Clientttt',message),
  },
  connectors: modalConnectors({
    projectId: "c02abb68bb4075aebcbd6a2ded56d08d",
    version: "2", // or "2"
    appName: "web3Modal",
    chains,
  }),
  provider,
});
// provider: biconomy.ethersProvider,

// Web3Modal Ethereum Client
// Web3Modal Ethereum Client
const ethereumClient = new EthereumClient(wagmiClient, chains);



// ****************************************



import { ethers } from "ethers";

const config = require("./config.js");
import MetaMaskOnboarding from "@metamask/onboarding";
const { POSClient } = require("@maticnetwork/maticjs");
import { use } from "@maticnetwork/maticjs";
import { Web3ClientPlugin } from "@maticnetwork/maticjs-web3";
import axios from "axios";
// biconomy
import { abi_mainnnet } from "./abi_mainnet";

const BigNumber = require('bignumber.js');
BigNumber.config({ DECIMAL_PLACES: 18 })

use(Web3ClientPlugin);

const App = () => {
  use(Web3ClientPlugin);

  const ONBOARD_TEXT = 'Click here to install MetaMask!';
  const CONNECT_TEXT = 'Connect MetaMask';
  const CONNECTED_TEXT = 'Connected';

  const [buttonText, setButtonText] = React.useState(ONBOARD_TEXT);
  const [isDisabled, setDisabled] = React.useState(false);
  const [USDCbalance, setUSDCbalance] = useState(0);
  const [accounts, setAccounts] = React.useState([]);
  const [error, setError] = useState({
    error: false,
    message: ''
  })
  const onboarding = React.useRef();


  // const [biconomy, setBiconomy] = useState(null)
  // const [web3, setWeb3] = useState(null)
  // const [loading, setLoading] = useState(false)
  //   const [error, setError] = useState(null)

  // const rpc_key_mumbai = "https://polygon-mainnet.g.alchemy.com/v2/g2eh_HAncn98Bd--DuOBQ_QxdAQLr0Fe" //polygon
  // const biconomy_key_mumbai = "QMvL6ucUW.f288b185-c168-428a-b279-98430413db2b" //mallshark gateway apikey

  // const contract_address = "0x2791Bca1f2de4661ED88A30C99A7a9449Aa84174" //polygon
  // const chainId = 137 //polygons


  // let maticProvider = new Web3.providers.HttpProvider(rpc_key_mumbai);
  // const walletWeb3 = new Web3(window.ethereum)
  return (
    <>
      <WagmiConfig client={wagmiClient}>
        <HomePage />
      </WagmiConfig>

      <Web3Modal
        projectId="c02abb68bb4075aebcbd6a2ded56d08d"
        ethereumClient={ethereumClient}
        defaultChain={polygon}

      />
    </>
  );
  React.useEffect(() => {
    if (!onboarding.current) {
      onboarding.current = new MetaMaskOnboarding();
    }
  }, []);

  React.useEffect(() => {
    if (MetaMaskOnboarding.isMetaMaskInstalled()) {
      if (accounts.length > 0) {
        setButtonText(CONNECTED_TEXT);
        setDisabled(true);
        getUSDCBalance()
        onboarding.current.stopOnboarding();
      } else {
        setButtonText(CONNECT_TEXT);
        setDisabled(false);
      }
    }
  }, [accounts]);
  React.useEffect(() => {
    function handleNewAccounts(newAccounts) {
      setAccounts(newAccounts);
      getUSDCBalance()
    }
    if (MetaMaskOnboarding.isMetaMaskInstalled()) {
      window.ethereum
        .request({ method: 'eth_requestAccounts' })
        .then(handleNewAccounts);
      window.ethereum.on('accountsChanged', handleNewAccounts);
      return () => {
        window.ethereum.off('accountsChanged', handleNewAccounts);
      };
    }
  }, []);
  const onClick = () => {
    if (MetaMaskOnboarding.isMetaMaskInstalled()) {
      window.ethereum
        .request({ method: 'eth_requestAccounts' })
        .then((newAccounts) => setAccounts(newAccounts));
    } else {
      onboarding.current.startOnboarding();
    }
  };
  React.useEffect(() => {
    setLoading(true)
    const biconomy = new Biconomy(maticProvider, {
      apiKey: biconomy_key_mumbai,
      debug: true,
    })
    const biconomyWeb3 = new Web3(biconomy)
    setWeb3(biconomyWeb3)
    setBiconomy(biconomy)

    biconomy
      .onEvent(biconomy.READY, () => {
        console.log('Biconomy is ready')
        setLoading(false)
      })
      .onEvent(biconomy.ERROR, (error, message) => {
        console.log(message)
        setError(error)
      })
  }, [])

  const getSignatureParameters = (signature) => {
    if (!web3.utils.isHexStrict(signature)) {
      throw new Error(
        'Given value "'.concat(signature, '" is not a valid hex string.')
      )
    }
    var r = signature.slice(0, 66)
    var s = '0x'.concat(signature.slice(66, 130))
    var v = '0x'.concat(signature.slice(130, 132))
    v = web3.utils.hexToNumber(v)
    if (![27, 28].includes(v)) v += 27
    return {
      r: r,
      s: s,
      v: v,
    }
  }

  const callMetaTxMethod = async (
    contractAddress,
    contractAbi,
    userAddress,
    methodName,
    attr
  ) => {
    // Initialize constants
    const domainType = [
      { name: 'name', type: 'string' },
      { name: 'version', type: 'string' },
      { name: 'verifyingContract', type: 'address' },
      { name: 'salt', type: 'bytes32' },
    ]
    const metaTransactionType = [
      { name: 'nonce', type: 'uint256' },
      { name: 'from', type: 'address' },
      { name: 'functionSignature', type: 'bytes' },
    ]

    const contract = new web3.eth.Contract(contractAbi, contractAddress)
    const name = await contract.methods.name().call()
    console.log('name', name)
    let domainData = {
      name: name,
      version: '1',
      verifyingContract: contractAddress,
      // converts Number to bytes32. pass your chainId instead of 42 if network is not Kovan
      salt: '0x' + (chainId).toString(16).padStart(64, '0'),
    }

    let nonce = await contract.methods.nonces(userAddress).call()
    console.log('nonce', nonce)
    // // Create your target method signature.. here we are calling setQuote() method of our contract
    let functionSignature = contract.methods[methodName](...attr).encodeABI()
    console.log('functionSignature', functionSignature)
    let message = {}
    message.nonce = parseInt(nonce)
    message.from = userAddress
    message.functionSignature = functionSignature
    console.log('message', message)

    const dataToSign = JSON.stringify({
      types: {
        EIP712Domain: domainType,
        MetaTransaction: metaTransactionType,
      },
      domain: domainData,
      primaryType: 'MetaTransaction',
      message: message,
    })
    showLoader()
    const receipt = await new Promise((resolve, reject) => {

      walletWeb3.currentProvider.send(
        {
          jsonrpc: '2.0',
          id: 999999999999,
          method: 'eth_signTypedData_v4',
          params: [userAddress, dataToSign],
        },
        (error, response) => {
          let { r, s, v } = getSignatureParameters(response.result)
          let tx = contract.methods
            .executeMetaTransaction(userAddress, functionSignature, r, s, v)
            .send({ from: userAddress })

          // console.log("error >>",error)
          resolve(tx)

        }
      ).then((response) => {
        console.log('response', response)
      }, (error) => {
        // console.error('error',error)
        console.error(error)
        setError({
          error: true,
          message: 'Transaction is cancelled'
        })
        hideLoader();
      }).catch((cat) => {
        console.log('catch', cat)
      }).finally(() => {

      })
    })



    return receipt
  }
  const getCalculatedAmount = (price) => {

    const decimals = 6;

    let priceDec = Web3.utils.toWei(price.toString(), 'mwei');

    return {
      priceDec
    }
  }
  const execute = async () => {
    let price = document.getElementById('usdcprice').getAttribute('price')
    const response = getCalculatedAmount(price)
    for (let [key, value] of Object.entries(response)) {
      console.log(key, value)
    }


    let tx = await callMetaTxMethod(contract_address, abi_mainnnet, accounts[0], 'transfer', ["0x3d1E2feaC6E1c2d8ffe5d4FAEc98E4751E7608B7", response.priceDec]).then(response => {
      debugger
      // response.hasOwnProperty('status') && response.status ?
      // createOrder() : null;
      response.hasOwnProperty('status') && response.status ?
        null : null;
      setError({
        error: false,
        message: ''
      })
    }, error => {

      error.hasOwnProperty('code') && error.code == 417 ?
        setError({
          error: true,
          message: 'Your USDC balance is insufficient for this transaction'
        })
        :
        setError({
          error: true,
          message: 'Transaction is failed'
        })
        ;

    }).finally(() => {
      console.log('hide loader here')
      hideLoader()
    });



  }

  const getUSDCBalance = async () => {

    const posClient = new POSClient();

    await posClient.init({
      network: config.NETWORK,
      version: config.VERSION,// 'mumbai' or 'v1'
      parent: {
        provider: walletWeb3,
        defaultConfig: {
          from: accounts[0]
        }
      },
      child: {
        provider: walletWeb3,
        defaultConfig: {
          from: accounts[0]
        }
      }
    });


    try {
      showLoader()
      const erc20RootToken = posClient.erc20("0x2791Bca1f2de4661ED88A30C99A7a9449Aa84174"); //USD Coin
      const balance = await erc20RootToken.getBalance(accounts[0]);
      console.log('balance', balance)
      let bn = web3.utils.fromWei(balance, 'mwei')

      setUSDCbalance(bn);

    } catch (e) {

      hideLoader()
    }
  }

  const showLoader = () => {

    $(".large-loader").show();

  }
  const hideLoader = () => {

    $(".large-loader").hide();

  }
  const createOrder = async () => {

    let shipping_data = jQuery('#shipping_data').val();
    let orderData = jQuery('#orderData').val();
    console.log("orderData", orderData);


    axios.post(`/meta-mask`, { shipping_data, orderData })
      .then(res => {


        $('.readypayment-contianer').removeClass('show-section');
        $('.shipping-contianer').removeClass('hide-section');
        $('.shipping_amount').show()


      })
  }
  return (
    <div>
      <input
        className="primary-btn btn btn-primary"
        price="{{$orderData['total_price']}}"
        onClick={execute}
        type="button"
        name="submit"
        value="Pay Via MetaMask"
        hidden={
          isDisabled ? false : true
        }
      ></input>
      <input
        className="primary-btn btn btn-primary"
        disabled={isDisabled}
        onClick={onClick}
        type="button"
        name="submit"
        value={buttonText}
        hidden={isDisabled}
      ></input>




      {error.error && error.message ?
        (
          <h4 style={{ margin: '30px' }}> {error.message}</h4>
        ) : null}
      <div style={{ margin: '30px' }}>
        USDC Balance
        <input
          className="primary-btn btn btn-primary"
          disabled
          onClick={onClick}
          type="button"
          name="submit"
          value={USDCbalance}
          hidden={!isDisabled}
        ></input>
      </div>
      Account
      <input
        className="primary-btn btn btn-primary"
        disabled
        onClick={onClick}
        type="button"
        name="submit"
        value={accounts[0]}
        hidden={!isDisabled}
      ></input>

    </div>
  );
};
const Wallet = () => { };
export default App;
if (document.getElementById("polygon")) {
  ReactDOM.render(<App />, document.getElementById("polygon"));
}

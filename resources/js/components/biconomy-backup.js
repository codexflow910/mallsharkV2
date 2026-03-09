import React, { useEffect, useState } from "react";
import ReactDOM from "react-dom";
import Web3 from "web3";

import { AlphaRouter } from '@uniswap/smart-order-router'
import { Token, CurrencyAmount } from '@uniswap/sdk-core'
import { JSBI, Percent } from "@uniswap/sdk";
import { ethers } from "ethers";
import * as fs from 'fs';
import WalletConnectProvider from "@maticnetwork/walletconnect-provider";
const config = require("./config.js");
import MetaMaskOnboarding from "@metamask/onboarding";
import HDWalletProvider from "@truffle/hdwallet-provider"
const { POSClient, MaticPoSClient } = require("@maticnetwork/maticjs");

import { toBigNumber, formatUnits, toFixed } from "../utills/big-number";
import { use } from "@maticnetwork/maticjs";
import { Web3ClientPlugin } from "@maticnetwork/maticjs-web3";
import { ABIManager } from "@maticnetwork/maticjs";
import axios from "axios";
// biconomy
import { abi_mainnnet } from "./abi_mainnet";
import pool_abi from "../abis/pool_abi.json"
import { Biconomy } from "@biconomy/mexa";
const BigNumber = require('bignumber.js');
BigNumber.config({ DECIMAL_PLACES: 18 })

use(Web3ClientPlugin);

const App = () => {
  use(Web3ClientPlugin);


  const V3_SWAP_ROUTER_ADDRESS = "0x68b3465833fb72A70ecDF485E0e4C7bD8665Fc45";

  const TokenInput = "0xC02aaA39b223FE8D0A0e5C4F27eAD9083C756Cc2";

  const TokenOutput = "0xA0b86991c6218b36c1d19D4a2e9Eb0cE3606eB48";


  const web3ProviderPol = new ethers.providers.JsonRpcProvider("https://polygon-mainnet.g.alchemy.com/v2/g2eh_HAncn98Bd--DuOBQ_QxdAQLr0Fe");
  const web3Provider = new ethers.providers.JsonRpcProvider("https://eth-mainnet.g.alchemy.com/v2/X_PAkJMoRVEXB_SiKLcYA0T-kJTNsslw");
  const newweb3 = new ethers.providers.JsonRpcProvider(window.ethereum)
//set private key if turning on auto transactions with personal sign after order
  const privateKey = "";
  // if setting  private key uncomment below code as well
  // const wallet = new ethers.Wallet(privateKey, newweb3);
  // const address = wallet.address;
// const ESCROWADDRESS = '0x3d1E2feaC6E1c2d8ffe5d4FAEc98E4751E7608B7' 
const ESCROWADDRESS = '0xc7a544819cc207F1c4F3Af566B4535B76C8a58dc' 










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


  const [biconomy, setBiconomy] = useState(null)
  const [web3, setWeb3] = useState(null)
  const [loading, setLoading] = useState(false)
  //   const [error, setError] = useState(null)

  const rpc_key_mumbai = "https://polygon-mainnet.g.alchemy.com/v2/g2eh_HAncn98Bd--DuOBQ_QxdAQLr0Fe" //polygon
  const biconomy_key_mumbai = "QMvL6ucUW.f288b185-c168-428a-b279-98430413db2b" //mallshark gateway apikey
  // const rpc_key_mumbai ="https://polygon-mumbai.g.alchemy.com/v2/WjeC7u7rHoS6hM1ub7FpZdhU7DMf-iup" //mumbai
  // const biconomy_key_mumbai = "LoGSK5e40.a7686bbd-31af-40cd-92cb-aa41a2e20b98" //mumbai
  // const biconomy_key_mumbai = "21SXepX7W.d00c1f1c-1c08-4e58-a312-c03c64eab1fb" //polygon
  const contract_address = "0x2791Bca1f2de4661ED88A30C99A7a9449Aa84174" //polygon
  //  const contract_address = "0xe6b8a5cf854791412c1f6efc7caf629f5df1c747" //mumbai
  const chainId = 137 //polygons
  //  const chainId = 80001 //mumbai

  let maticProvider = new Web3.providers.HttpProvider(rpc_key_mumbai);
  //   const userAddress = '0x6a8a201de420fDb0305fd67285aa8477F28dd396';
  const walletWeb3 = new Web3(window.ethereum)
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
      // salt: '0x' + (137).toString(16).padStart(64, '0'),
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
    const receipt = await new Promise((resolve,reject) => {

      //   let {signature} = web3.eth.accounts.sign("0x" + dataToSign.toString("hex"), 'c8029614aad3020c85a9efffbca2964402f1c9836d530fe9e6e732d06f5a3c93');
      //   let { r, s, v } = getSignatureParameters(signature)
      //   let tx = contract.methods
      //   .executeMetaTransaction(userAddress, functionSignature, r, s, v)
      //   .send({ from: userAddress })


      // resolve(tx)


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
      ).then((response)=>{
        console.log('response',response)
      },(error)=>{
        // console.error('error',error)
        console.error(error)
        setError({
          error : true,
          message:'Transaction is cancelled'
        })
        hideLoader();
      }).catch((cat)=>{
        console.log('catch',cat)
      }).finally(()=>{
        
      })
    })



    return receipt
  }
  const getCalculatedAmount = (price) => {
    // price = 1.045;
    // const decimals = 6;
    // console.log('price :=> ', price);
    // let txnFee = (price / 100) * 3;
    // let txnFeeDec = (txnFee) * 10 ** decimals;
    // let priceDec = price * 10 ** decimals;
    // let charge = priceDec + txnFeeDec;
    const decimals = 6;
    // console.log('price :=> ', price);
    // let txnFee = (price / 100) * 3;
    // let txnFeeDec = Web3.utils.toWei(txnFee.toString(), 'mwei');
    let priceDec =  Web3.utils.toWei(price.toString(), 'mwei');
    // let charge = priceDec + txnFeeDec;
    // debugger
    // const {price1_main,price2_main,price1_reward,price2_reward} = getTokenPrice(config.poolAddressMAIN,config.poolAddressREWARD);
    
    return {
        priceDec
    }
    // console.log('tokens amount is 1=> ', formatUnits(charge,18,18))
    // console.log('tokens amount is 1=> ', formatUnits(txnFeeDec,18,18))
    // let  tokens= web3.utils.toBN("0x"+(price*10**d).toString(16));
    // console.log('tokens amount is => ',formatUnits(a,18,18))
  }
  const execute = async () => {
    let price = document.getElementById('usdcprice').getAttribute('price')
    const response = getCalculatedAmount(price)
    for (let [key, value] of Object.entries(response)) {
      console.log(key, value)
    }
       
    // console.log('Payable amount is => ', response.charge)
    // return;
    let tx = await callMetaTxMethod(contract_address, abi_mainnnet, accounts[0], 'transfer', [ESCROWADDRESS, response.priceDec]).then(response=>{
      
      response.hasOwnProperty('status') && response.status ?
      createOrder() : null;
      setError({
        error:false,
        message:'' 
      })
    },error=>{
      
      error.hasOwnProperty('code') && error.code == 417 ? 
      setError({
        error:true,
        message:'Your USDC balance is insufficient for this transaction'
      })
      :
      setError({
        error:true,
        message:'Transaction is failed'
      })
      ;
      
    }).finally(()=>{
      console.log('hide loader here')
      hideLoader()
    });
    
    // take all below this code on above transaction completed
    // if (tx.status) {

    //   const payedValue = tx.events?.Transfer?.returnValues?.value ?? 0;
    //   if (payedValue && (Number(payedValue) > 0)) {
    //     //send tokens to buyer and seller
    //     const posClient = new POSClient();

    //     await posClient.init({
    //       network: config.NETWORK,
    //       version: config.VERSION,// 'mumbai' or 'v1'
    //       parent: {
    //         provider: walletWeb3,
    //         defaultConfig: {
    //           from: accounts[0]
    //         }
    //       },
    //       child: {
    //         // provider: maticProvider, //mumbai
    //         // provider: polygonProvider,
    //         provider: new HDWalletProvider('c8029614aad3020c85a9efffbca2964402f1c9836d530fe9e6e732d06f5a3c93', rpc_key_mumbai),
    //         defaultConfig: {
    //           from: accounts[0]
    //         }
    //       }
    //     });
        
    //     // const zitToken = posClient.erc20(config.REWARD_TOKEN); //ZIT Coin (reward token)
    //     // zitToken.transfer('10000000000000000000', "0x01A901da6c490FF7FeD67f2f5Dedc5A74088FB51", {
    //     //   maxPriorityFeePerGas: 40000000000,

    //     // }).then(async (txn) => {
    //     //   const txnReciept = await txn.getReceipt();
    //     //   console.log('txnReciept', txnReciept)
    //     //   zitToken.transfer('10000000000000000000', "0x01A901da6c490FF7FeD67f2f5Dedc5A74088FB51", {
    //     //     maxPriorityFeePerGas: 40000000000,
    //     //   }).then(async (txn) => {

    //     //     const txnReciept = await txn.getReceipt();
    //     //     if (txnReciept.status) {
    //     //       createOrder()
    //     //     } else {
    //     //       console.log('last reward transaction faild');
    //     //     }
    //     //     console.log('txnReciept2', txnReciept)
    //     //   }, error => {
    //     //     console.log('transfer2 error', error)
    //     //   })

    //     // }, error => {
    //     //   console.log('transfer error', error)
    //     // })

    //   }

    // } else {
    //   //failed

    // }
    
  }
  const getTokenPrice = async (poolAddressMAIN,poolAddressREWARD) => {
    const pool_contract_main = new web3.eth.Contract(pool_abi,poolAddressMAIN)
    const pool_contract_reward = new web3.eth.Contract(pool_abi,poolAddressREWARD)
   
    const slot0_main = await pool_contract_main.methods.slot0().call()
    const slot0_reward = await pool_contract_reward.methods.slot0().call()    
    const sqrtPriceX96_main = await slot0_main.sqrtPriceX96;
    const sqrtPriceX96_reward = await slot0_reward.sqrtPriceX96;
    const paramsTZIT = {"SqrtX96" : sqrtPriceX96_main, "Pair":"MATIC/USDC", "T0d":18, "T1d":6}
    const paramsZIT = {"SqrtX96" : sqrtPriceX96_reward, "Pair":"ZIT/MATIC", "T0d":18, "T1d":18}
    const { price:price1_main,price2:price2_main} =  GetPrice(paramsTZIT);
    const { price:price1_reward,price2:price2_reward} =  GetPrice(paramsZIT);
    console.log('MATIC/USDC => ',price1_main) 
    console.log('USDC/MATIC => ',price2_main)
    console.log('MATIC/ZIT => ',price1_reward) 
    console.log('ZIT/MATIC => ',price2_reward)
    return {
      price1_main,
      price2_main,
      price1_reward,
      price2_reward
    }
  }
  function GetPrice(testz){
    let SqrtX96 = testz.SqrtX96;
    let Token0Decimals = testz.T0d;
    let Token1Decimals = testz.T1d;

    const multok0 = new BigNumber(10**Token0Decimals)
    const multok1 = new BigNumber(10**Token1Decimals)
    const to18 = new BigNumber(10**18)
    const X96 = new BigNumber(2).pow(new BigNumber(96))
    const X192 = X96.pow(new BigNumber(2))

    let sqrtPriceA = new BigNumber(SqrtX96);
    let sqrtPrice = sqrtPriceA.multipliedBy(sqrtPriceA)

    ///////////////////
    let Numerator2 = multok0.multipliedBy(sqrtPrice)
    let Denominator2 = multok1.multipliedBy(X192)

    let price = Denominator2.div(Numerator2)
    // console.log(price.toString()) //token1 price in relation to token0 (ie buy 1 token of token1 with token0 (not including LQ available))

    let price2 = Numerator2.div(Denominator2)
    // console.log(price2.toString()) //token0 price in relation to token1 (ie buy 1 token of token0 with token1 (not including LQ available))
    // console.log("")
    return {
      price : price.toString(),
      price2 : price2.toString()
    }
}

    

  const incentivesTransaction = async () => {

    const router = new AlphaRouter({ chainId: 137, provider: web3ProviderPol });
    const WETH = new Token(
      router.chainId,
      '0x0000000000000000000000000000000000001010',
      18,
      'MATIC',
      'Matic token'
    );

    const USDC = new Token(
      router.chainId,
      '0x42CB4E7c94b93F1CB37ac4001A854a46c6a4a3EB',
      18,
      'SZZZT',
      'Shopzit'
    );

    const typedValueParsed = '10000000000';
    const wethAmount = CurrencyAmount.fromRawAmount(WETH, JSBI.BigInt(typedValueParsed));

    const IO = "Exact_Input"
    const TradeType = IO == "Exact_Input" ? 0 : 1;


    router.route(
      wethAmount,
      USDC,
      TradeType,
      {
        recipient: wallet.address,
        slippageTolerance: new Percent(5, 100),
        deadline: Math.floor(Date.now() / 1000 + 1800)
      }
    ).then(route => {
      console.log('route', route)
      console.log(`Quote Exact In: ${route.quote.toFixed(wethAmount.currency === WETH ? USDC.decimals : WETH.decimals)}`);
      console.log(`Gas Adjusted Quote In: ${route.quoteGasAdjusted.toFixed(wethAmount.currency === WETH ? USDC.decimals : WETH.decimals)}`);
    }, err => {
      console.log('err', err)
    })

    // var Ebal = await web3.getBalance(wallet.address);
    // console.log("Wallet Balance : " + Ebal.toString());

    // var tbal = await TokBal(TokenOutput);
    // console.log("Token Out Balance : " + tbal.toString());

    // await Deposit("1000000000000000000");
    // await Approve(TokenInput, "1000000000000000000");
    // var tbalW = await TokBal(TokenInput);
    // console.log("Token In Balance : " + tbalW.toString());

    // console.log(`Quote Exact In: ${route.quote.toFixed(wethAmount.currency === WETH ? USDC.decimals : WETH.decimals)}`);
    // console.log(`Gas Adjusted Quote In: ${route.quoteGasAdjusted.toFixed(wethAmount.currency === WETH ? USDC.decimals : WETH.decimals)}`);

  }
  const sendSignedTransaction = async () => {
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
        // provider: maticProvider, //mumbai
        // provider: polygonProvider,
        provider: new HDWalletProvider('c8029614aad3020c85a9efffbca2964402f1c9836d530fe9e6e732d06f5a3c93', rpc_key_mumbai),
        defaultConfig: {
          from: accounts[0]
        }
      }
    });
    const zitToken = posClient.erc20(config.REWARD_TOKEN); //ZIT Coin (reward token)
    const balance = await zitToken.getBalance(accounts[0]);
    console.log('balance', balance)
    const transresult = await zitToken.transfer('1000000000000000000', "0x01A901da6c490FF7FeD67f2f5Dedc5A74088FB51", {
      maxPriorityFeePerGas: 40000000000,
      // maxPriorityFee: 400000000000,
      // // "gasFee": 49.575259405,
      // maxFeePerGas: 400000000000,
      // "maxPriorityFeePerGas": 49.575259405,
      // gasPrice: 200000000000
      // gasPrice: 40000000000
    })
    console.log('transresult', transresult)
    const transtxReceipt = await transresult.getReceipt();
    console.log('transtxReceipt', transtxReceipt)
    const balancee = await zitToken.getBalance(accounts[0]);
    console.log('balance', balancee)
    if (transtxReceipt.status) {

      console.log('creted order nnow')
      //  createOrder()
    }
    hideLoader()
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
        // provider: maticProvider, //mumbai
        // provider: polygonProvider,
        provider: walletWeb3,
        defaultConfig: {
          from: accounts[0]
        }
      }
    });

    // const erc20RootToken = maticPoSClient.erc20("0x2791Bca1f2de4661ED88A30C99A7a9449Aa84174"); //USD Coin
    // const erc20RootToken = posClient.erc20("0xe11A86849d99F524cAC3E7A0Ec1241828e332C62",true); //usdc test coin
    // const erc20RootToken = posClient.erc20("0x0000000000000000000000000000000000001010"); //MATIC Coin
    // const erc20RootToken = posClient.erc20("0xA0b86991c6218b36c1d19D4a2e9Eb0cE3606eB48"); //USD Coin

    try {
      showLoader()
      const erc20RootToken = posClient.erc20("0x2791Bca1f2de4661ED88A30C99A7a9449Aa84174"); //USD Coin
      const balance = await erc20RootToken.getBalance(accounts[0]);
      console.log('balance', balance)
      let bn = web3.utils.fromWei(balance, 'mwei')

      setUSDCbalance(bn);
      //   hideLoader()
    } catch (e) {
      // debugger
      // setError({
      //   error : true,
      //   message : e.message
      // })
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
    console.log("orderData",orderData);


    axios.post(`/meta-mask`, { shipping_data, orderData })
      .then(res => {


        $('.readypayment-contianer').removeClass('show-section');
        $('.shipping-contianer').removeClass('hide-section');
        $('.shipping_amount').show()
        // window.location = '/'


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
          // Networkid == 80001 ? false : true
          //   Networkid == 137 && isDisabled ? false : true
          isDisabled ? false : true
        }
      ></input>
      {/* <input
        className="primary-btn btn btn-primary"
        price="{{$orderData['total_price']}}"
        onClick={sendSignedTransaction}
        type="button"
        name="submit"
        value="sendSignedTransaction"
        hidden={
          // Networkid == 80001 ? false : true
          //   Networkid == 137 && isDisabled ? false : true
          isDisabled ? false : true
        }
      ></input>
      <input
        className="primary-btn btn btn-primary"
        price="{{$orderData['total_price']}}"
        onClick={getTokenPrice}
        type="button"
        name="submit"
        value="getTokenPrice"
        hidden={
          // Networkid == 80001 ? false : true
          //   Networkid == 137 && isDisabled ? false : true
          isDisabled ? false : true
        }
      ></input> */}

      {/* <input
        className="primary-btn btn btn-primary"
        onClick={switchNetwork}
        type="button"
        name="submit"
        value={Networkid == 137 ? "Polygon" : "GOTO Polygon"}
        hidden={!isDisabled || Networkid == 137 ? true : false}
      ></input> */}
      <input
        className="primary-btn btn btn-primary"
        disabled={isDisabled}
        onClick={onClick}
        type="button"
        name="submit"
        value={buttonText}
        hidden={isDisabled}
      ></input>
      
      
      
      
      { error.error && error.message ? 
      (
      <h4 style={{ margin: '30px' }}> {error.message}</h4>
      ): null }
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

import React, { useEffect, useState } from "react";
import ReactDOM from 'react-dom';
import Web3 from "web3";
import Navbar from "./Navbar";
import WalletConnectProvider from "@maticnetwork/walletconnect-provider";
const config = require("./config.js");
import MetaMaskOnboarding from '@metamask/onboarding';
// const MaticPoSClient = require("@maticnetwork/maticjs").MaticPOSClient;
const {POSClient,MaticPoSClient} = require("@maticnetwork/maticjs");
const Network = require("@maticnetwork/meta/network");
const Matic = require("@maticnetwork/maticjs");
import { use } from '@maticnetwork/maticjs'
import { Web3ClientPlugin } from '@maticnetwork/maticjs-web3'
import { ABIManager } from '@maticnetwork/maticjs'
import axios from 'axios';
use(Web3ClientPlugin)

const App = () => {
 
  use(Web3ClientPlugin)

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
  const [network,setNetwork] = useState('')
  const [metamaskStatus,setMetamaskStatus] = useState();
  const [maticBalance,setMaticBalance] = useState(0);
  const [USDCbalance,setUSDCbalance] = useState(0);
  const [posClient,setPostClient] = useState();
  const [error,setError] = useState({
    error : false,
    message : ''
  })
  const ONBOARD_TEXT = 'Click here to install MetaMask!';
  const CONNECT_TEXT = 'Connect MetaMask';
  const CONNECTED_TEXT = 'Connected';
 
  const [buttonText, setButtonText] = React.useState(ONBOARD_TEXT);
  const [isDisabled, setDisabled] = React.useState(false);
  const [accounts, setAccounts] = React.useState([]);
  const onboarding = React.useRef();


  useEffect(()=>{

    
  })
  useEffect(() => {
    loadWeb3();
    loadBlockchainData();
    
  }, [Networkid]);
  
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
  const loadWeb3 = async () => {
    if (window.ethereum) {
      window.web3 = new Web3(window.ethereum);
      
      await window.ethereum.enable();
      setMetamaskStatus(true)
    } else if (window.web3) {
   
      window.web3 = new Web3(window.web3.currentProvider);
      setMetamaskStatus(true)
    } else {
     
      setMetamaskStatus(false)
      window.alert(
        "Non-Ethereum browser detected. You should consider trying MetaMask!"
      );
    }
  };
  if(metamaskStatus){

    window.ethereum.on('networkChanged', function(networkId){
      setNetworkid(networkId)
     
    });
  }
  const loadBlockchainData = async () => {
    setLoading(true);
    const maticProvider = new WalletConnectProvider({
      host: config.MATIC_RPC,
      callbacks: {
        onConnect: console.log("matic connected"),
        onDisconnect: console.log("matic disconnected!"),
      },
    });
    const polygonProvider = new WalletConnectProvider({
      host: config.POLYGON_RPC,
      callbacks: {
        onConnect: console.log("polygon connected"),
        onDisconnect: console.log("polygon disconnected!"),
      },
    });
    
    
    const ethereumProvider = new WalletConnectProvider({
      host: config.ETHEREUM_RPC,
      callbacks: {
        onConnect: console.log("mainchain connected"),
        onDisconnect: console.log("mainchain disconnected"),
      },
    });

    setPolygonProvider(polygonProvider)
    setMaticProvider(maticProvider);
    setEthereumProvider(ethereumProvider);
    // const web3 = window.web3;

    // const accounts = await web3.eth.getAccounts();


    
    // setAccount(accounts[0]);
    const networkId = await web3.eth.net.getId();

    setNetworkid(networkId);
 
    if (networkId === config.ETHEREUM_CHAINID) {
      // window.alert(" switch to  Matic or Ethereum networkk");
      // switchNetwork()
    } else if (networkId === config.MATIC_CHAINID) {
      setLoading(false);
      // switchNetwork()
    } else {
      // switchNetwork()
      // window.alert(" switch to  Matic or Ethereum networkkk");
    }
    
  };
  const settAccounts = async()=>{
    const web3 = window.web3;

    const accounts = await web3.eth.getAccounts();


    
    setAccount(accounts[0]);
  }
  const switchNetwork = async () => {
    
    try {
      await ethereum.request({
        method: 'wallet_switchEthereumChain',
        params: [{ chainId: '0x89'}], //polygon
        // params: [{ chainId: '0x13881' }], /mumbai

      });
      const networkId = await web3.eth.net.getId();

    setNetworkid(networkId);
      setNetwork('mumbai');
      
    } catch (switchError) {
   
      console.log('switchError',switchError.code)
      // This error code indicates that the chain has not been added to MetaMask.
      if (switchError.code === 4902) {
        try {
     
          await ethereum.request({
            method: 'wallet_addEthereumChain',
            params: [
              {
                chainId: '0x89', //polygon
                // chainId: '0x13881', //mumbai
                // chainName: 'Mumbai',//mumbai
                chainName: 'Polygon',//Polygon
                rpcUrls: [config.POLYGON_RPC] /* ... */,
                nativeCurrency: {
                  name: 'MATIC',
                  symbol: 'MATIC', // 2-6 characters long
                  decimals: 18,
                },
                blockExplorerUrls: ['https://polygonscan.com/']
                // rpcUrls: ['https://rpc-mumbai.maticvigil.com'] /* ... */,
              },
            ],
          });
        } catch (addError) {
          console.log('addNetworkError',addError)
        }
      }
      // handle other "switch" errors
    }
  }
 

 





  

  
  const showLoader = ()=>{

    $(".large-loader").show();
 
  }
  const hideLoader = ()=>{

    $(".large-loader").hide();
 
  }
  const getUSDCBalance = async () => {
    const posClient = new POSClient();

await posClient.init({
  network: config.NETWORK,
  version: config.VERSION,// 'mumbai' or 'v1'
    parent: {
      provider : window.web3,
      defaultConfig : {
            from: accounts[0]
      }
    },
    child: {
      // provider: maticProvider, //mumbai
      // provider: polygonProvider,
      provider: window.web3,
      defaultConfig: {
            from: accounts[0]
      }
    }
});

    // const erc20RootToken = maticPoSClient.erc20("0x2791Bca1f2de4661ED88A30C99A7a9449Aa84174"); //USD Coin
    // const erc20RootToken = posClient.erc20("0xe11A86849d99F524cAC3E7A0Ec1241828e332C62",true); //usdc test coin
    // const erc20RootToken = posClient.erc20("0x0000000000000000000000000000000000001010"); //MATIC Coin
    // const erc20RootToken = posClient.erc20("0xA0b86991c6218b36c1d19D4a2e9Eb0cE3606eB48"); //USD Coin

    try{
       showLoader()
      const erc20RootToken = posClient.erc20("0x2791Bca1f2de4661ED88A30C99A7a9449Aa84174"); //USD Coin
      const balance = await erc20RootToken.getBalance(accounts[0]);
      let bn = web3.utils.fromWei(balance,'mwei')
      
      setUSDCbalance(bn);
      hideLoader()
    } catch (e){
      // setError({
      //   error : true,
      //   message : e.message
      // })
      hideLoader()
    }
  }
  const transferUSDC = async()=>{
    let USDC_price =   document.getElementById('usdcprice').getAttribute('price') ;
    //  const x =  0.10 * 1000000000000000000; // 18 decimals
     const x = USDC_price * 1000000; // 18 decimals
    const x1 = x.toString();

const posClient = new POSClient();

await posClient.init({
  network: config.NETWORK,
  version: config.VERSION,// 'mumbai' or 'v1'
    parent: {
      provider : window.web3,
      defaultConfig : {
            from: accounts[0]
      }
    },
    child: {
      // provider: maticProvider, //mumbai
      // provider: polygonProvider,
      provider: window.web3,
      defaultConfig: {
            from: accounts[0]
      }
    }
});

    // const erc20RootToken = maticPoSClient.erc20("0x2791Bca1f2de4661ED88A30C99A7a9449Aa84174"); //USD Coin
    // const erc20RootToken = posClient.erc20("0xe11A86849d99F524cAC3E7A0Ec1241828e332C62",true); //usdc test coin
    // const erc20RootToken = posClient.erc20("0x0000000000000000000000000000000000001010"); //MATIC Coin
    // const erc20RootToken = posClient.erc20("0xA0b86991c6218b36c1d19D4a2e9Eb0cE3606eB48"); //USD Coin

    try{
       showLoader()
      const erc20RootToken = posClient.erc20("0x2791Bca1f2de4661ED88A30C99A7a9449Aa84174"); //USD Coin
      const balance = await erc20RootToken.getBalance(accounts[0]);
      console.log('balance',balance)
      if(balance > x1){
  
        // showLoader()
        
  
          const transresult = await erc20RootToken.transfer(x1,"0x01A901da6c490FF7FeD67f2f5Dedc5A74088FB51",{
            // maxPriorityFee: 400000000000,
            // // "gasFee": 49.575259405,
            // maxFeePerGas: 400000000000,
            // "maxPriorityFeePerGas": 49.575259405,
            // gasPrice: 200000000000
            maxPriorityFeePerGas: 40000000000,
            // gasPrice: 40000000000
          })
          console.log('transresult',transresult)
          const transtxReceipt = await transresult.getReceipt();
          console.log('transtxReceipt',transtxReceipt.status)
          if(transtxReceipt.status){
            hideLoader()
            console.log('creted order nnow')
             createOrder()
          }
        
      }else{
        setError({
          error : true,
          message : 'USDC balance is low'
        })
        hideLoader()
      }
    } catch (e){
      setError({
        error : true,
        message : e.message
      })
      hideLoader()
    }
  
  }

  const createOrder = async ()=>{

    let shipping_data =  jQuery('#shipping_data').val();
   

    axios.post(`/meta-mask`,{shipping_data})
      .then(res => {
        

        $('.readypayment-contianer').removeClass('show-section');
        $('.shipping-contianer').removeClass('hide-section');
        $('.shipping_amount').show()
        // window.location = '/'


      })
  }



  const depositERC20 = async () => {
    
    
 
  
    // const maticPoSClient = posClientParent();
    let ercprice =   document.getElementById('ercprice').getAttribute('price') ;
 
    // setInputValue("5");
   
   
    const x = ercprice * 1000000000000000000; // 18 decimals
    const x1 = x.toString();
   
    // return;
    const manager = new ABIManager('testnet','mumbai');
await manager.init();

const posClient = new POSClient();

await posClient.init({
  network: config.NETWORK,
  version: config.VERSION,// 'mumbai' or 'v1'
    parent: {
      provider : window.web3,
      defaultConfig : {
            from: account
      }
    },
    child: {
      provider: maticProvider,
      defaultConfig: {
            from: account
      }
    }
});
    // await maticPoSClient.approveERC20ForDeposit(config.posRootERC20, x1, {
    //   from: account,
    // });
    const erc20RootToken = posClient.erc20(config.posRootERC20,true);
    const balance = await erc20RootToken.getBalance("0x01A901da6c490FF7FeD67f2f5Dedc5A74088FB51");
    // debugger
    // approve 100 amount
const approveResult = await erc20RootToken.approve(x1);
debugger
const resultTxHash = await approveResult.getTransactionHash();
const resultTtxReceipt = await approveResult.getReceipt();

debugger
  
const result = await erc20RootToken.deposit(x1, "0x6a8a201de420fDb0305fd67285aa8477F28dd396"); // 1 //0.1
// const result = await erc20RootToken.deposit(x1, "0x01A901da6c490FF7FeD67f2f5Dedc5A74088FB51"); // 0.32 //0.42
const txHash = await result.getTransactionHash();

const txReceipt = await result.getReceipt();


// const transerc20Token = posClient.erc20(config.posRootERC20);
// debugger
const transresult = await erc20RootToken.transfer(x1,"0x6a8a201de420fDb0305fd67285aa8477F28dd396"); //chrome account
// const transresult = await erc20RootToken.transfer(x1,"0x01A901da6c490FF7FeD67f2f5Dedc5A74088FB51");

const transtxHash = await transresult.getTransactionHash();

const transtxReceipt = await transresult.getReceipt();
$('.readypayment-contianer').removeClass('show-section');
$('.shipping-contianer').removeClass('hide-section');
$('.shipping_amount').show()

    // await maticPoSClient.depositERC20ForUser(config.posRootERC20, account, x1, {
    //   from: account,
    // });
  };



  





  return (
    <div>
      <input  class="primary-btn btn btn-primary"  price="{{$orderData['total_price']}}" onClick={transferUSDC}  type="button" name="submit" value="Pay Via MetaMask"
       hidden={
        // Networkid == 80001 ? false : true
        Networkid == 137 && isDisabled ? false : true 
       }></input>
      {/* <input  class="primary-btn btn btn-primary"    type="button" name="submit" value="Metamask is required!"
      hidden={metamaskStatus} ></input> */}
      <input  class="primary-btn btn btn-primary"   onClick={switchNetwork}  type="button" name="submit" value={Networkid == 137 ? 'Polygon' : 'GOTO Polygon'}
      hidden = {!isDisabled || Networkid == 137 ? true:false}
      ></input>
      
      <input  class="primary-btn btn btn-primary"   disabled={isDisabled} onClick={onClick}  type="button" name="submit" value={buttonText}
      hidden={isDisabled}
      ></input>
     
      <h4> {error.message }</h4>
     <div>
      USDC Balance
     <input  class="primary-btn btn btn-primary"   disabled onClick={onClick}  type="button" name="submit"
       value= {USDCbalance}
      hidden={!isDisabled}
      ></input>
     </div>
      Account
      <input  class="primary-btn btn btn-primary"   disabled onClick={onClick}  type="button" name="submit"
       value= {accounts[0]}
      hidden={!isDisabled}
      ></input>
      
      
      {/* <select>
    {accounts.map((item)=>(
      <option key={item} >
        {item}
      </option>
    ))}
  </select> */}
    </div>
    
  );
  
};
const Wallet = () => {

}
export default App;
if (document.getElementById('polygon')) {
    ReactDOM.render(<App />, document.getElementById('polygon'));
}
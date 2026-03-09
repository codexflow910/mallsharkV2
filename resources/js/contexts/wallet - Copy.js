import {
    FC,
    useState,
    useEffect,
    useContext,
    createContext,
    ReactNode,
    useCallback,
    useRef
  } from 'react';
  import MetaMaskOnboarding from "@metamask/onboarding";
  import { ethers } from 'ethers';
  import { POLYGON_RPC  } from '../utills/config';
//   import { 'wallet', 'rinkeby' } from 'config';
//   import cache from 'utils/cache';
  
  const WalletContext = createContext();
  
  export const WalletProvider = ({children}) => {
    const onboarding = useRef();
    const [isConnecting, setIsConnecting] = useState(false);
    const [network, setNetwork] = useState(null);
    const [signer, setSigner] = useState(null);
    const [privateSigner, setPrivateSigner] = useState(null);
    const [nonce, setNonce] = useState(null);
    const [networkProvider,setNetworkProvider] = useState(null);
    const [address, setAddress] = useState(null);
  
    const startConnecting = useCallback(() => setIsConnecting(true), [
      setIsConnecting,
    ]);
    const stopConnecting = useCallback(() => setIsConnecting(false), [
      setIsConnecting,
    ]);
    
    // added from
    useEffect(() => {
      if (!onboarding.current) {
        onboarding.current = new MetaMaskOnboarding();
      }
    }, []);
    useEffect(() => {
      async function handleNewAccounts(newAccounts) {
        // setAccounts(newAccounts);
        await setProvider(window.ethereum);
        // await loadIncentives();
        // await loadReward();
        // const signer = provider.getSigner();
        // setSigner(signer);
        // getUSDCBalance()
      }
      if (MetaMaskOnboarding.isMetaMaskInstalled()) {
        window.ethereum
          .request({ method: "eth_requestAccounts" })
          .then(handleNewAccounts);
        window.ethereum.on("accountsChanged", handleNewAccounts);
        return () => {
          // window.ethereum.off("accountsChanged", handleNewAccounts);
        };
      }
    }, []);
    // added till here


    const setProvider = useCallback(
      async (web3Provider) => {
        web3Provider.on('accountsChanged', () => {
          window.location.reload();
        });
        web3Provider.on('chainChanged', () => {
          window.location.reload();
        });
        // web3Provider.on('disconnect', () => {
        //   disconnect();
        // });
        const provider = new ethers.providers.Web3Provider(
        
        const { name: network } = await provider.getNetwork();
        setNetwork(~['homestead'].indexOf(network) ? 'matic' : network);
  
        const signer = provider.getSigner();
        setSigner(signer);
        setAddress(await signer.getAddress());
        console.log('provider : ', provider.getSigner())
        const privateS  = new ethers.Wallet('c8029614aad3020c85a9efffbca2964402f1c9836d530fe9e6e732d06f5a3c93',provider)
        setPrivateSigner(privateS)
        const getNonce = await provider.getTransactionCount('0x6a8a201de420fDb0305fd67285aa8477F28dd396')
        console.log('nonce :',getNonce)
        setNonce(getNonce)
        console.log('private signer : ', privateS)
        console.log('Network provider : ', provider)
        setNetworkProvider(provider);
        stopConnecting();
      },
      [stopConnecting]
    );
    
    const switchNetwork = useCallback(async () => {
      if (!window.ethereum) return;
      try {
        await ethereum.request({
          method: 'wallet_switchEthereumChain',
          params: [{ chainId: '0x89'}], //polygon
          // params: [{ chainId: '0x13881' }], /mumbai
  
        });
        await setProvider(window.ethereum);
      }catch(switchError){
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
                rpcUrls:[POLYGON_RPC] /* ... */,
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
      }
    //   cache('wallet', 'metamask');
      
    }, [setProvider]);


    const connectMetamask = useCallback(async () => {
      if (!window.ethereum) return;
      await window.ethereum.enable();
    //   cache('wallet', 'metamask');
      await setProvider(window.ethereum);
    }, [setProvider]);
  
    async function disconnect() {
    //   cache('wallet', null);
      setSigner(null);
      setAddress(null);
      setNetwork(null);
    }
  
    useEffect(() => {
      const load = async () => {
        if (address) return;
  
        const cachedWallet = 'wallet'
        if (cachedWallet) {
          const c = {
            metamask: connectMetamask,
          };
          c[cachedWallet]?.();
        }
      };
  
      load();
    }, [address, connectMetamask]);
  
    return (
      <WalletContext.Provider
        value={{
          network,
  
          signer,
          address,
  
          isConnecting,
          startConnecting,
          stopConnecting,
          disconnect,
  
          connectMetamask,
          networkProvider,
          nonce,
          privateSigner,
          switchNetwork
        }}
      >
        {children}
      </WalletContext.Provider>
    );
  };
  
  export function useWallet() {
    const context = useContext(WalletContext);
    if (!context) {
      throw new Error('Missing Wallet context');
    }
    const {
      network,
  
      signer,
      address,
      nonce,
      isConnecting,
      startConnecting,
      stopConnecting,
      disconnect,
      switchNetwork,
      connectMetamask,
      privateSigner,
      networkProvider,
    } = context;
  
    return {
      network,
  
      signer,
      address,
      nonce,  
      isConnecting,
      startConnecting,
      stopConnecting,
      disconnect,
      switchNetwork,
      networkProvider,
      privateSigner,
      connectMetamask,
    };
  }
  
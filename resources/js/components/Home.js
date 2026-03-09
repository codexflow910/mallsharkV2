import { Web3Button, Web3NetworkSwitch } from '@web3modal/react'
import Web3 from "web3";
// import CustomButton from './CustomButton'
import { Row } from 'react-bootstrap';
import {
    useAccount,
    useConnect,
    useDisconnect,
    useEnsAvatar,
    useEnsName,
    useBalance,
    useNetwork,
    useSwitchNetwork,
    useContract,
    useSigner,
    useSendTransaction,
    usePrepareContractWrite,
    usePrepareSendTransaction,
    useWaitForTransaction,
    useContractWrite
} from 'wagmi'
import { BigNumber } from "ethers";
import { useDebounce } from 'use-debounce'
import { Web3ClientPlugin } from "@maticnetwork/maticjs-web3";
import { useState, useEffect } from 'react';
import { abi_mainnnet } from "./abi_mainnet";
import { ESCROW_CONTRACT_ADDRESS } from '../utills/config';
import { Biconomy } from "@biconomy/mexa";
// use(Web3ClientPlugin);
export default function HomePage() {
    const [bicoProvider, setBicoProvider] = useState(null)
    const contract_address = "0x2791Bca1f2de4661ED88A30C99A7a9449Aa84174" //polygon
    const [data, setData] = useState(null)
    const [amount, setAmount] = useState(useState < BigNumber > (BigNumber.from(0)))
    const [enableHook, setEnableHook] = useState(false);
    const [tigger, setTrigger] = useState(false);
    const debounceAmount = useDebounce(amount, 500)
    const [debounceData] = useDebounce(data, 500)
    const rpc_key_mumbai = "https://polygon-mainnet.g.alchemy.com/v2/g2eh_HAncn98Bd--DuOBQ_QxdAQLr0Fe" //polygon
    const biconomy_key_mumbai = "QMvL6ucUW.f288b185-c168-428a-b279-98430413db2b" //mallshark gateway apikey
    let maticProvider = new Web3.providers.HttpProvider(rpc_key_mumbai);
    console.log('rendered>>>>>>>>>>>>>>>>>>', amount)
    useEffect(() => {
        // const biconomy = new Biconomy(maticProvider, {
        //     apiKey: biconomy_key_mumbai,
        //     debug: true,
        //   })

        //   setBicoProvider(new Web3(biconomy))
        //   console.log("bicoo",biconomy)

        // biconomy
        // .onEvent(biconomy.READY, () => {
        //   console.log('Biconomy is readyyyyyyyyyyyyyyyyyy')

        // })
        // .onEvent(biconomy.ERROR, (error, message) => {
        //   console.log('Biconomy not redyyyyy',message)

        // })
    }, [])

    // Prepare write contract

    const { config: configTransferFun, isLoading: isLoadingTransferFun, isSuccess: isSuccessTransferFun, error: errorTransferFun, isError: isErrorTransferFun, refetch: refetchPrepareTransferFun, remove } = usePrepareContractWrite({
        address: contract_address,
        abi: [
            {
                inputs: [
                    {
                        "internalType": "address",
                        "name": "recipient",
                        "type": "address"
                    },
                    {
                        "internalType": "uint256",
                        "name": "amount",
                        "type": "uint256"
                    }
                ],
                name: "transfer",
                outputs: [
                    {
                        "internalType": "bool",
                        "name": "",
                        "type": "bool"
                    }
                ],
                stateMutability: "nonpayable",
                type: "function"
            },
        ],
        functionName: 'transfer',
        args: [ESCROW_CONTRACT_ADDRESS, amount],
        onSuccess(data) {
            console.log("DEBUG usePrepareContractWrite", "onSuccess", "data", data);
        },
        onError(err) {

            console.log("DEBUG usePrepareContractWrite", "onError", err.message);
        },
        onSettled(data) {
            console.log("DEBUG usePrepareContractWrite", "onSettled", data);
        },
        onRefetch(data) {
            console.log("DEBUG usePrepareContractWrite", "onRefetch", data);
        },
        // args: [parseInt(debouncedTokenId)],
        enabled: enableHook,
    })

    const { data: dataTranfer, write: transfer, error: errorContractWrite, isError: isErrorContractWrite, isSuccess: isSuccessContractWrite, isLoading: isLoadingContractWrite, reset: resetContractWrite } = useContractWrite({
        ...configTransferFun,
        onSuccess(data) {
            console.log("DEBUG  useContractWrite", "onSuccess", "data", data);
        },
        onError(err) {
            console.log("DEBUG useContractWrite", "onError", err.message);
            $('#loading').hide();
            setEnableHook(false)
            // refetchPrepareTransferFun().then((res)=>{
            //     console.log("DEBUG","refetchPrepareTransferFun","response",res)
            // }).catch(e=>{
            //     console.log("DEBUG","refetchPrepareTransferFun","catch",e)

            // })
        },
    })
    // onsuccess of prepare set a boolean start true 
    // on refetched first set boolean start false and on success of refetched again set it to true
    // use start in useEffect to trigger transfer method
    // use 

    const { data: txDataTransfer, isLoading: isLoadingTxTransfer, isSuccess: isSuccessTxTransfer, error: errorTxTransfer, isError: isErrorTxTransfer } = useWaitForTransaction({
        hash: dataTranfer?.hash,
    })
    const resetWrite = () => {
        resetContractWrite().then(res => {
            console.log('response ', res)
        })
    }
    const refetchPrepare = () => {
        refetchPrepareTransferFun()
    }
    const removePrepare = () => {
        remove()
    }

    useEffect(() => {
        console.log('DEBUG ***************************Prepare')
        console.log('DEBUG errorTransferFun', errorTransferFun)
        console.log('DEBUG isSuccessTransferFun', isSuccessTransferFun)
        console.log('DEBUG isLoadingTransferFun', isLoadingTransferFun)


    }, [errorTransferFun, isSuccessTransferFun, isLoadingTransferFun])
    useEffect(() => {
        console.log('DEBUG ***************************ContractWrite')
        console.log('DEBUG isErrorContractWrite', isErrorContractWrite)
        console.log('DEBUG isSuccessContractWrite', isSuccessContractWrite)
        console.log('DEBUG isLoadingContractWrite', isLoadingContractWrite)


    }, [isErrorContractWrite, isSuccessContractWrite, isLoadingContractWrite])
    useEffect(() => {
        console.log('DEBUG ***************************waitTXN')
        console.log('DEBUG errorTxTransfer', errorTxTransfer)
        console.log('DEBUG isSuccessTxTransfer', isSuccessTxTransfer)
        console.log('DEBUG isLoadingTxTransfer', isLoadingTxTransfer)


    }, [errorTxTransfer, isSuccessTxTransfer, isLoadingTxTransfer])


    const { config } = usePrepareSendTransaction({
        request: debounceData ? debounceData : null,
    })
    const { dataa, sendTransaction } = useSendTransaction(config)
    const { isLoadingg, isSuccesss } = useWaitForTransaction({
        hash: dataa?.hash,
    })

    const { address, connector: activeConnector, isConnected } = useAccount()
    const { data: ensName } = useEnsName({ address })
    const { connect, connectors, error, isLoading, pendingConnector } =
        useConnect({
            onMutate(connector) {
                console.log('Before Connect', connector)
            },
            onSettled(data, error) {
                console.log('Settled', { data, error })
            },
            onSuccess(data) {
                console.log('Connect', data)
            },
            onError(error) {
                console.log('Errorr', error)
            },
        })
    const { data: balance, isError, isLoadingBalance } = useBalance({
        address,
        token: '0x2791Bca1f2de4661ED88A30C99A7a9449Aa84174',
        onError(error) {
            console.log('Error', error)
        },
        onSettled(data, error) {
            console.log('Settledd', { data, error })
        },
        onSuccess(data) {
            console.log('Success', data)
        },
    })
    const { chain } = useNetwork()
    const { chains, switchNetworkError, switchNetworkIsLoading, switchNetworkPendingChainId, switchNetwork } =
        useSwitchNetwork({
            onError(error) {
                console.log('Error', error)
                
              },
            onMutate(args) {
                console.log('Mutate', args)
              },
              onSettled(data, error) {
                console.log('Settled', { data, error })
              },
              onSuccess(data) {
                console.log('Success', data)
              },
        })
    const { data: signer, isErrorRR, isLoadingH } = useSigner()
    const contractt = useContract({
        address: contract_address,
        abi: abi_mainnnet,
        signerOrProvider: signer
    })
    const getName = async () => {

        try {

            const name = await contractt.name()
            console.log(`NAME: ${name}`)
            const nonce = await contractt.nonces(address)
            console.log(`Nonce:${nonce}`)
        } catch (e) {
            console.log("catch =>", e)

        }
    }
    const execute = async () => {

        // let priceDec = Web3.utils.toWei((1).toString(), 'mwei');
        //  const name =  callMetaTxMethod(contract_address,abi_mainnnet,address,'transfer', ["0xc7a544819cc207F1c4F3Af566B4535B76C8a58dc", priceDec]).then((res)=>{
        //     console.log('response 2',res)
        //  })
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
        console.log('bicoProviderrrrr', bicoProvider)
        const contract = new bicoProvider.eth.Contract(contractAbi, contractAddress)
        console.log('contractttttttttt', contract)
        const name = await contract.methods.name().call()
        console.log('response', name)

        let domainData = {
            name: name,
            version: '1',
            verifyingContract: contractAddress,
            // converts Number to bytes32. pass your chainId instead of 42 if network is not Kovan
            salt: '0x' + (137).toString(16).padStart(64, '0'),
        }

        let nonce = await contract.methods.nonces(userAddress).call()
        console.log('nonce', nonce)
        // Create your target method signature.. here we are calling setQuote() method of our contract
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
        setData(dataToSign)
        console.log('sendTransactionnn', sendTransaction)
        sendTransaction?.()

        console.log('dataaa', dataa)
        console.log('dataaaa to sign', dataToSign)
        // showLoader()
        // const contracttt = useContract({
        //     address: contract_address,
        //     abi: abi_mainnnet,
        //     signerOrProvider : signer
        // })


        // const receipt = await new Promise((resolve, reject) => {

        //   walletWeb3.currentProvider.send(
        //     {
        //       jsonrpc: '2.0',
        //       id: 999999999999,
        //       method: 'eth_signTypedData_v4',
        //       params: [userAddress, dataToSign],
        //     },
        //     (error, response) => {
        //       let { r, s, v } = getSignatureParameters(response.result)
        //       let tx = contractt
        //         .executeMetaTransaction(userAddress, functionSignature, r, s, v)
        //         .send({ from: userAddress })

        //       // console.log("error >>",error)
        //       resolve(tx)

        //     }
        //   ).then((response) => {
        //     console.log('response', response)
        //   }, (error) => {
        //     // console.error('error',error)
        //     console.error(error)
        //     // setError({
        //     //   error: true,
        //     //   message: 'Transaction is cancelled'
        //     // })
        //     hideLoader();
        //   }).catch((cat) => {
        //     console.log('catch', cat)
        //   }).finally(() => {

        //   })
        // })



        // return receipt
    }
    function consoleAll() {
        console.log('DEBUG ***************************Prepare')
        console.log('DEBUG errorTransferFun', errorTransferFun)
        console.log('DEBUG isSuccessTransferFun', isSuccessTransferFun)
        console.log('DEBUG isLoadingTransferFun', isLoadingTransferFun)

        console.log('DEBUG ***************************ContractWrite')
        console.log('DEBUG isErrorContractWrite', isErrorContractWrite)
        console.log('DEBUG isSuccessContractWrite', isSuccessContractWrite)
        console.log('DEBUG isLoadingContractWrite', isLoadingContractWrite)

        console.log('DEBUG ***************************waitTXN')
        console.log('DEBUG errorTxTransfer', errorTxTransfer)
        console.log('DEBUG isSuccessTxTransfer', isSuccessTxTransfer)
        console.log('DEBUG isLoadingTxTransfer', isLoadingTxTransfer)
    }
    useEffect(() => {
        if (enableHook && isSuccessTransferFun && transfer) {
            try {
                transfer?.()
            }
            catch (e) {
                console.log("Rejection Error", e)
            }
        }
    }, [isSuccessTransferFun])
    useEffect(() => {
        if (txDataTransfer?.status) {
            createOrder();
        }
    }, [txDataTransfer])
    const errorPopup = (el,msg)=>{
        swal({
            title: msg,
            type: "warning",
            allowOutsideClick:false,
           
            showCancelButton: false,
            confirmButtonColor: "#DD6B55",
            confirmButtonText: "OK",
            closeOnConfirm: true,
            closeOnCancel: false,
            cancelButtonText: "No, cancel plx!",
          },
          function(isConfirm){
            if (isConfirm) {
                $('html, body').animate({
                    scrollTop: $(el).offset().top - 60
                }, 200);
            } else {
              // swal("Cancelled", "Your imaginary file is safe :)", "error");
            }
          });
    }
    const makePayment = async (event) => {
        event.preventDefault();
        
        var firstname = $('#inputFirstNameShipping').val();
        var lastname = $('#inputLastNameShipping').val();
        var fullName = firstname + ' ' + lastname;
        var UserEmail = $('#useremail').val();
        var country = $('#s_country').val();
        var states = $('#states_countries').val();
        var passcode = $('#passcode').val();
        var street = $('#street_address').val();
        var city = $('#city_name').val();
        var zipcode = $('#zipcode').val();
        if (!country || !states ||  !fullName || !UserEmail) {
            
            errorPopup('#inputFirstNameShipping','Please Fill Your Shipping Form First');
            // swal("Error!", "Please Fill Your Shipping Form", "error");
        }else if (!country) {
            errorPopup('#s_country','Please Fill Your country');
        } else if (!zipcode) {
            errorPopup('#zipcode','Please Fill Your Zip Code');
        }
        else if (!street) {
            errorPopup('#street_address','Please Fill Your Street Address');
        }
        else if (!passcode && user_restriction.passcodeCheck) {
            errorPopup('#passcode','Please Fill Your passcode');
        }
        else if (!city) {
            errorPopup('#city_name','Please Fill Your City');
        } 
        else if (!fullName) {
            errorPopup('#inputFirstNameShipping','Please enter Fullname');
        } else if (!states ) {
            errorPopup('#states_countries','Please Fill Your States');
        }
         else {
        try {
            setEnableHook(true)
            let price = document.getElementById('usdcprice').getAttribute('price')
            console.log('price=>',price);
            const response = getCalculatedAmount(price)
            setAmount(response.priceDec)
            if (enableHook && isSuccessTransferFun && transfer) {
                $('#loading').show();
                try {
                    transfer?.()
                }
                catch (e) {
                    
                    console.log("Rejection Error", e)
                }
            }
            // for (let [key, value] of Object.entries(response)) {
            //     console.log(key, value)
            // }

        } catch (e) {
            
            console.log('Error ', e)
        }
    }
    }
    const createOrder = async () => {

        let shipping_data = jQuery('#shipping_data').val();
        let orderData = jQuery('#orderData').val();
        let buyer_address = address;
        let email_address = jQuery('#useremail').val();
        console.log("orderData", orderData);


        axios.post(`/meta-mask`, { shipping_data, orderData,buyer_address , email_address})
            .then(res => {
                $('.readypayment-contianer').removeClass('show-section');
                $('.shipping-contianer').removeClass('hide-section');
                 // $('.shipping_amount').show();
                 window.location.href = '/order-confirmation'; 
                $('#checkout-page').hide();
                // setTimeout(function() {
                //     window.location.href = '/';
                // }, 5000);
            })
    }
    const getCalculatedAmount = (price) => {

        const decimals = 6;

        let priceDec = Web3.utils.toWei(price.toString(), 'mwei');

        return {
            priceDec
        }
    }
    return (
        <>
            {/* Predefined button  */}
            <Row>
            <div className="col d-flex" style={{ justifyContent: 'center' }}>

            <Web3Button icon="show" label="Connect Wallet" balance="show" />
            <Web3NetworkSwitch />
            </div>
        
            {/* Network Switcher Button */}
            </Row>

            {/* <div>{ensName ? `${ensName} (${address})` : address}</div> */}
            {activeConnector && chain ? (
                <div>Connected to {chain.name} via {activeConnector.name}</div>
            ) : null}
            <br />
            {isLoadingBalance ? (<div>Fetching balance…</div>) : (balance?.formatted && balance?.symbol) && (<div> Balance: {balance?.formatted} {balance?.symbol} </div>)}
            <b />
          

            {/* {chain && <div>Connected to {chain.name}</div>} */}

            {/* {chains.map((x) => (
                <button
                    hidden={!switchNetwork || x.id === chain?.id}
                    key={x.id}
                    type="button"
                    onClick={() => switchNetwork?.(x.id)}
                >
                    {x.name}
                    {isLoading && pendingChainId === x.id && ' (switching)'}
                </button>
            ))} */}
            {chains.map((x) => (
                  <input
                  className="primary-btn btn btn-primary"
                  hidden={!switchNetwork || x.id === chain?.id}
                  key={x.id}
                  type="button"
                  onClick={() => switchNetwork?.(x.id)}
                  value="Switch to Polygon"
            ></input>
            ))}

            {/* <button hidden={!switchNetwork || chain?.id != 137} disabled={!chain?.id == 137 || isLoadingContractWrite} type='button' onClick={makePayment} > Pay  </button> */}
            <input
            className="primary-btn btn btn-primary peywithmetamask"
            onClick={makePayment}
            type="button"
            value="Pay"
            hidden={!switchNetwork || chain?.id != 137}
            disabled={!chain?.id == 137 || isLoadingContractWrite}
      ></input>
            {(isErrorContractWrite) && (
                <div style={{ margin: '30px' }}>Failed: {errorContractWrite?.message}</div>
            )}
            {(isErrorTransferFun) && (
                <div style={{ margin: '30px' }}>Failed: Your USDC balance is insufficient for this transaction </div>
            )}

            {/* <div>{error && error.message}</div>
            {(isErrorTransferFun) && (
                <div>Error TransferFun: {errorTransferFun?.message}</div>
            )} */}
            
            {/* {(isErrorTxTransfer) && (
                <div>Error Contract write: {errorTxTransfer?.message}</div>
            )} */}

            {/* Custom button */}
            {/* <CustomButton /> */}
           
        </>
    )
}

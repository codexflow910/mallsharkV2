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
import { Row } from 'react-bootstrap';
use(Web3ClientPlugin);

const App = () => {
    use(Web3ClientPlugin);




    const web3ProviderPol = new ethers.providers.JsonRpcProvider("https://polygon-mainnet.g.alchemy.com/v2/g2eh_HAncn98Bd--DuOBQ_QxdAQLr0Fe");
    const web3Provider = new ethers.providers.JsonRpcProvider("https://eth-mainnet.g.alchemy.com/v2/X_PAkJMoRVEXB_SiKLcYA0T-kJTNsslw");
    const newweb3 = new ethers.providers.JsonRpcProvider(window.ethereum)
    //set private key if turning on auto transactions with personal sign after order
    const privateKey = "";
    // if setting  private key uncomment below code as well
    // const wallet = new ethers.Wallet(privateKey, newweb3);
    // const address = wallet.address;










    const ONBOARD_TEXT = 'Click here to install MetaMask!';
    const CONNECT_TEXT = 'Connect MetaMask';
    const CONNECTED_TEXT = 'Connected';

    const [buttonText, setButtonText] = React.useState(ONBOARD_TEXT);
    const [isDisabled, setDisabled] = React.useState(false);
    const [USDCbalance, setUSDCbalance] = useState(null);
    const [packages, setPackages] = useState(null)
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
        if (!packages) {

            axios.get(`/fetchPackages`)
                .then(res => {
                    let status = res.data.status;
                    if (status) {
                        let packages = res.data.packages;
                        setPackages(packages)
                    }

                })
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
            // window.ethereum
            //     .request({ method: 'eth_requestAccounts' })
            //     .then(handleNewAccounts);
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
    const execute = async (event) => {
        // let price = document.getElementById('usdcprice').getAttribute('price')
        let id = event.target.getAttribute('pkgid');
        let pkg = packages.find(item => item.id == id)
        let product_id = document.getElementById("feature_product_id").value
        let store_id = document.getElementById("store_id").value
        let user_id = document.getElementById("user_id").value
        let data = {};
        data['product_id'] = product_id;
        data['package_id'] = pkg.id;
        data['store_id'] = store_id;
        data['user_id'] = user_id;
        data["_token"] = "{{ csrf_token() }}";

        const response = getCalculatedAmount(pkg.price)
        for (let [key, value] of Object.entries(response)) {
            console.log(key, value)
        }

        let tx = await callMetaTxMethod(contract_address, abi_mainnnet, accounts[0], 'transfer', ["0x3d1E2feaC6E1c2d8ffe5d4FAEc98E4751E7608B7", response.priceDec]).then(response => {
            response.hasOwnProperty('status') && response.status ?
                storeData(data) : null;
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
                // provider: maticProvider, //mumbai
                // provider: polygonProvider,
                provider: walletWeb3,
                defaultConfig: {
                    from: accounts[0]
                }
            }
        });


        try {
            // showLoader()
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
            // hideLoader()
        }
    }

    const showLoader = () => {

        $(".large-loader").show();

    }
    const hideLoader = () => {

        $(".large-loader").hide();

    }
    const storeData = async (data) => {

        try {

            axios.post('/make-feature-product', { data }).then((res) => {
            window.location.reload()
            })
        } catch (e) {
            console.log("Feature payemnt Error", e)
            window.location.reload()
        }
    }
    return (
        <div>
            {(packages) && (
                <>
                    <Row>
                        {packages.map((item, index) => (
                            <>
                                <div className="col-md-4">
                                    <div className="card" style={{ width: '18rem' }}>
                                        <div className="card-body text-center">
                                            <h4 className="card-title">{item.package_name}</h4>
                                            <p className="card-text">{item.description}</p>
                                            <h5 className="card-text"> <strong> {item.days}  Days </strong> </h5>
                                            <h5 className="card-text"><strong>{item.price} USDC</strong></h5>
                                            <button pkgid={item.id} type="button"
                                                className="buypkg btn btn-primary text-white" onClick={execute} disabled={isDisabled ? false : true} >buy</button>
                                        </div>
                                    </div>
                                </div>
                            </>
                        ))}
                    </Row>
                    <div style={{ textAlign: 'center' }}>

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
                        {(USDCbalance && isDisabled) && (
                            <>
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
                            </>
                        )}


                    </div>
                </>
            )}


        </div>
    );
};
const Wallet = () => { };
export default App;
if (document.getElementById("feature_payment")) {
    ReactDOM.render(<App />, document.getElementById("feature_payment"));
}

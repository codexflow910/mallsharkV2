
const { Biconomy } = require("@biconomy/mexa");
const Web3 = require("web3");
const sigUtil = require("eth-sig-util");
import axios from "axios";
import {REFUND_NODE_URL} from '../utills/config'
import {REFUND_NODE_URL_LOCAL} from '../utills/config'
const biconomy_key_mainnet = "XQmBTt7b7.a8531cf6-7009-457e-8c5f-8fad8a16aac8";
const rpc_key_mainnet =
  "https://polygon-mainnet.g.alchemy.com/v2/g2eh_HAncn98Bd--DuOBQ_QxdAQLr0Fe";
let maticProvider = new Web3.providers.HttpProvider(rpc_key_mainnet);
const mainnet_contract_address = "0x3d1E2feaC6E1c2d8ffe5d4FAEc98E4751E7608B7";
const privateKey ="642a0fc8e42814676b834a010bc521ed7141df71849b03c3e4ef350a32f43949";
  let userAddress = "0x01A901da6c490FF7FeD67f2f5Dedc5A74088FB51";
  let profitWallet ="0x6a8a201de420fDb0305fd67285aa8477F28dd396"
const biconomy = new Biconomy(maticProvider, {
  apiKey: biconomy_key_mainnet,
  debug: true,
});
const web3 = new Web3(biconomy);
let contract;
const escrow_abi = require("../abis/escrow_abi.json");
const domainType = [
  { name: "name", type: "string" },
  { name: "version", type: "string" },
  { name: "verifyingContract", type: "address" },
  { name: "salt", type: "bytes32" },
];
const metaTransactionType = [
  { name: "nonce", type: "uint256" },
  { name: "from", type: "address" },
  { name: "functionSignature", type: "bytes" },
];
let domainData = {
  name: "MallShark Payment",
  version: "1",
  verifyingContract: mainnet_contract_address,
  salt: "0x" + (137).toString(16).padStart(64, "0"),
};
biconomy
  .onEvent(biconomy.READY, async () => {
    console.log("biconomy is ready");

    contract = new web3.eth.Contract(escrow_abi, mainnet_contract_address);

  })
  .onEvent(biconomy.ERROR, (error, message) => {
    console.log("error", error);
    console.log("message", message);
  });

window.refundPaymentNode = async function (refundAmount,refundee) {
//  alert(refundAmount + " :  " + refundee)
const axiosNode = axios.create({
  baseURL : REFUND_NODE_URL,
  headers: {
    'Content-Type': 'application/json',
    'Access-Control-Allow-Origin': '*'
  },
})
 let receipt = await axiosNode.post('/refund_payment',{refundee,refundAmount}).then(res=>{
  // console.log(res)
  return res;
}).catch(err=>{
  return err;
})
return receipt;

};
// below code is for app side refund but we are using server side refund method
window.refundPayment = async function (refundAmount,refundee) {

  try {

          let nonces = await contract.methods.getNonce(userAddress).call();
          // let amount = item.payable_amount * 10 ** 6;
          let amount = Web3.utils.toWei((refundAmount).toString(), 'mwei');
          let functionSignature = await contract.methods
            .releaseTo(refundee, amount)
            .encodeABI();
          let message = {};
          message.nonce = parseInt(nonces);
          message.from = userAddress;
          message.functionSignature = functionSignature;
          domainType.name = "Escrow";
          const dataToSign = {
            types: {
              EIP712Domain: domainType,
              MetaTransaction: metaTransactionType,
            },
            domain: domainData,
            primaryType: "MetaTransaction",
            message: message,
          };

          const signature = sigUtil.signTypedMessage(
            new Buffer.from(privateKey, "hex"),
            { data: dataToSign },
            "V3"
          );
          let { r, s, v } = getSignatureParameters(signature);
          let executeMetaTransactionData = contract.methods
            .executeMetaTransaction(userAddress, functionSignature, r, s, v)
            .encodeABI();
          let txParams = {
            from: userAddress,
            to: mainnet_contract_address,
            value: "0x0",
            gas: "100000",
            data: executeMetaTransactionData,
          };
          const signedTx = await web3.eth.accounts.signTransaction(
            txParams,
            `0x${privateKey}`
          );
          let receipt = await web3.eth.sendSignedTransaction(
            signedTx.rawTransaction,
            (error, txHash) => {
              if (error) {
                // StoreEscrow.update(
                //   { status: "failed" },
                //   {
                //     where: {
                //       id: item.id,
                //     },
                //   }
                // );
                return error;
              } else {
                // StoreEscrow.update(
                //   { status: "completed" },
                //   {
                //     where: {
                //       id: item.id,
                //     },
                //   }
                // );

               
                return txHash;
              }
              console.log("calling resolve...");
              // resolve();
            }
          );
          return receipt;
     
    
    console.log("Vendor payments completed")
  } catch (err) {
    console.log(err);
    return err;
    
  }
};
const getSignatureParameters = (signature) => {
  if (!web3.utils.isHexStrict(signature)) {
    throw new Error(
      'Given value "'.concat(signature, '" is not a valid hex string.')
    );
  }
  var r = signature.slice(0, 66);
  var s = "0x".concat(signature.slice(66, 130));
  var v = "0x".concat(signature.slice(130, 132));
  v = web3.utils.hexToNumber(v);
  if (![27, 28].includes(v)) v += 27;
  return {
    r: r,
    s: s,
    v: v,
  };
};

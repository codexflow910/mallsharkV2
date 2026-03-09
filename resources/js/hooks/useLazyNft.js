import { useCallback, useState } from "react";

import { useWallet } from "../contexts/wallet";
import { useContracts } from "../contexts/contracts";
import { useNotifications } from "../contexts/notifications";

import web3 from "web3";
import { formatTimestamp } from "../utills/big-number";
import { circularProgressClasses } from "@mui/material";

const useLazyNft = (tokenId) => {
  const { nftTx } = useNotifications();
  const { address, nonce } = useWallet();

  const [receipt, setReceipt] = useState(null);
  const [currentCall, setCurrentCall] = useState(null);
  const { LazyNftContract } = useContracts();
  const [name, setName] = useState(null);
  const [isWorking, setIsWorking] = useState(null);
  const [isClaiming, setIsClaiming] = useState(null);

  const getName = useCallback(
    async (next) => {
      if (!(LazyNftContract && address)) return;
      console.log("ico contract", LazyNftContract);
      try {
        await LazyNftContract.name().then((resp) => {
          setName(resp.toString());
        });
      } catch (e) {
        console.warn(e);
      } finally {
      }
    },
    [LazyNftContract]
  );

  const redeem = useCallback(
    async (voucher, next) => {
      if (!(LazyNftContract && address)) return;
      console.log("VOUCHER", voucher);
      try {
        return await LazyNftContract.redeem(address, voucher, {
          value: "2000000000000000000",
        }).then((resp) => {
          console.log("redeem :>", resp);
          return resp;
        });
      } catch (e) {
        console.warn(e);
      } finally {
      }
    },
    [LazyNftContract]
  );
  const redeemNew = useCallback(
    async (voucher, next) => {
      if (!(LazyNftContract && address)) return;
      
      return new Promise(async (resolve,reject)=>{
        try{
  
          const txn = await LazyNftContract.redeem(address, voucher, {
            value: "2000000000000000000",
          }).then(resp=>{
            console.log('inner resp :>> ', resp);
            return resp
          });
          let receipt = await txn.wait().then(txnReceipt=>{
            console.log('txnReceipt :>> ', txnReceipt);
            return txnReceipt;
          });
          resolve(receipt)
        }
        catch(e){
          console.log('rejected',e)
          reject(e)
        }
        
        
  
      })
    },
    [LazyNftContract]
  );
  const asyncRedeem = (voucher) => {
    // return LazyNftContract.redeem(address, voucher, {
    //   value: "2000000000000000000",
    // });
    return new Promise(async (resolve,reject)=>{
      try{

        const txn = await LazyNftContract.redeem(address, voucher, {
          value: "2000000000000000000",
        }).then(resp=>{
          console.log('inner resp :>> ', resp);
          return resp
        });
        let receipt = await txn.wait().then(txnReceipt=>{
          console.log('txnReceipt :>> ', txnReceipt);
          return txnReceipt;
        });
        resolve(receipt)
      }
      catch(e){
        console.log('rejected',e)
        reject(e)
      }
      
      

    })
  };
  const redeemer = async (_voucherList) => {
    let promises = [];
    _voucherList.forEach((voucher) => {
      console.log('new promisses addded')
      promises.push(asyncRedeem(voucher));
    });
    Promise.all(promises)
      .then((results) => {
        console.log("All done", results);
        
      })
      .catch((e) => {
        console.warn('Error',e)
      });
  };
  const showLoader = () => {
    $(".large-loader").show();
  };
  const hideLoader = () => {
    $(".large-loader").hide();
  };

  const EthToWei = (n) => {
    return new web3.utils.BN(web3.utils.toWei(n.toString(), "ether"));
  };
  const WeiToEth = (n) => {
    return web3.utils.fromWei(n.toString(), "ether");
  };
  const isAddress = (address) => {
    return web3.utils.isAddress(address);
  };

  return {
    isAddress,
    getName,
    redeem,
    redeemer,
    redeemNew,
    name,
  };
};

export default useLazyNft;

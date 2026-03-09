import { useCallback, useState } from "react";

import { useWallet } from "../contexts/wallet";
import { useContracts } from "../contexts/contracts";
import { useNotifications } from "../contexts/notifications";

import web3 from "web3";
// import { useData } from '../contexts/data';
import { formatTimestamp } from "../utills/big-number";

const useIco = (tokenId) => {
  const { icoTx } = useNotifications();
  const { address, nonce } = useWallet();
  const [status, setStatus] = useState(null);
  const [haltStatus, setHaltStatus] = useState(null);
  const [rate, setRate] = useState(0);
  const [openingTime, setOpeningTime] = useState(null);
  const [closingTime, setClosingTime] = useState(null);
  const [weiRaised, setWeiRaised] = useState(null);
  const [depositWallet, setDepositWallet] = useState(null);
  const [icoToken, setIcoToken] = useState(null);

  const [receipt, setReceipt] = useState(null);
  const [currentCall, setCurrentCall] = useState(null);
  const { icoContract } = useContracts();
  //   const { currentIncentive } = useData();

  const [isWorking, setIsWorking] = useState(null);
  const [isClaiming, setIsClaiming] = useState(null);
  // const [inProcess,setInProcess] = useState(false)
  const showLoader = () => {
    $(".large-loader").show();
  };
  const hideLoader = () => {
    $(".large-loader").hide();
  };
  // const updateInProcess = (status)=>{
  //   setInProcess(status);
  // }
  const isHalt = useCallback(
    async (next) => {
      if (!(icoContract && address)) return;
      console.log("ico contract", icoContract);
      try {
        setIsWorking("Withdrawing...");

        await icoContract.isHalt().then((resp) => {
          console.log("halt response", resp);

          let isHalt = resp ? "Halted" : "Active";
          setHaltStatus(isHalt);
        });
      } catch (e) {
        console.warn(e);
      } finally {
        // setIsWorking(null);
      }
    },
    [icoContract]
  );
  const getIcoRate = useCallback(
    async (next) => {
      if (!(icoContract && address)) return;
      console.log("ico contract", icoContract);
      try {
        setIsWorking("Withdrawing...");

        await icoContract.rate().then((resp) => {
          setRate(resp.toString());
        });
      } catch (e) {
        console.warn(e);
      } finally {
        setIsWorking(null);
      }
    },
    [icoContract]
  );
  const getOpeningTime = useCallback(
    async (next) => {
      if (!(icoContract && address)) return;
      console.log("ico contract", icoContract);
      try {
        setIsWorking("Withdrawing...");
        const [
          openingTime,
          closingTime,
          rate,
          isOpen,
          wallet,
          weiRaised,
          token,
        ] = await Promise.all([
          icoContract.openingTime(),
          icoContract.closingTime(),
          icoContract.rate(),
          icoContract.isOpen(),
          icoContract.wallet(),
          icoContract.weiRaised(),
          icoContract.token(),
        ]);
        setOpeningTime(formatTimestamp(openingTime));
        setClosingTime(formatTimestamp(closingTime));
        setRate(rate.toString());
        setDepositWallet(wallet);
        // setWeiRaised(weiRaised.toString());
        setWeiRaised(WeiToEth(weiRaised));
        setIcoToken(token);
        setStatus(isOpen ? "Open" : "Closed");

        // //  await icoContract.openingTime().then((resp)=>{

        // //     setOpeningTime(formatTimestamp(resp))

        // })
      } catch (e) {
        console.warn(e);
      } finally {
        setIsWorking(null);
      }
    },
    [icoContract]
  );
  const getClosingTime = useCallback(
    async (next) => {
      if (!(icoContract && address)) return;
      console.log("ico contract", icoContract);
      try {
        setIsWorking("Withdrawing...");

        await icoContract.closingTime().then((resp) => {
          console.log("openingTime", resp);
          setClosingTime(formatTimestamp(resp));
        });
      } catch (e) {
        console.warn(e);
      } finally {
        setIsWorking(null);
      }
    },
    [icoContract]
  );
  const addWhitelisted = useCallback(
    async (investor, next) => {
      if (!(icoContract && address)) return;

      if (!isAddress(investor)) {
        setReceipt({ code: 4000, message: "Invalid Address" });
        next({ code: 4000, message: "Invalid Address" });
        return;
      }
      // showLoader()
      try {
        // console.log("investor >>>>", investor);
        setIsWorking("Approving...");
        let resp = await icoTx("Whitelisting...", "Whitelisted!", () =>
          icoContract.addWhitelisted(
            investor
            // {
            //  gasPrice:200000000000,
            //  gasPrice:200000000000,
            //  nonce
            // }
          )
        );

        // hideLoader()
        setReceipt(resp);
        next(resp);
      } catch (e) {
        next(e);
        // console.log('error>.......', e)
        setReceipt(e);
        next(e);
        // console.warn(e);
      } finally {
        //  next(receipt)
      }
    },
    [icoContract, address, icoTx]
  );
  const addWhiteListAdmin = useCallback(
    async (benificiary, next) => {
      if (!(icoContract && address)) return;

      if (!isAddress(benificiary)) {
        setReceipt({ code: 4000, message: "Invalid Address" });
        next({ code: 4000, message: "Invalid Address" });
        return;
      }
      // showLoader()
      try {
        // console.log("benificiary >>>>", benificiary);
        setIsWorking("Approving...");
        let resp = await icoTx(
          "adding WhiteListAdmin...",
          "Added in Admin List!",
          () =>
            icoContract.addWhitelistAdmin(
              benificiary
              // {
              //  gasPrice:200000000000,
              //  gasPrice:200000000000,
              //  nonce
              // }
            )
        );

        // hideLoader()
        setReceipt(resp);
        next(resp);
      } catch (e) {
        next(e);
        // console.log('error>.......', e)
        setReceipt(e);
        next(e);
        // console.warn(e);
      } finally {
        //  next(receipt)
      }
    },
    [icoContract, address, icoTx]
  );
  const removeWhiteListed = useCallback(
    async (benificiary, next) => {
      if (!(icoContract && address)) return;

      if (!isAddress(benificiary)) {
        setReceipt({ code: 4000, message: "Invalid Address" });
        next({ code: 4000, message: "Invalid Address" });
        return;
      }
      // showLoader()
      try {
        // console.log("benificiary >>>>", benificiary);
        // setIsWorking("Approving...");
        let resp = await icoTx(
          "removing from Whitelist...",
          "removed from whiteList!",
          () =>
            icoContract.removeWhitelisted(
              benificiary
              // {
              //  gasPrice:200000000000,
              //  gasPrice:200000000000,
              //  nonce
              // }
            )
        );

        // hideLoader()
        setReceipt(resp);
        next(resp);
      } catch (e) {
        next(e);
        // console.log('error>.......', e)
        setReceipt(e);
        next(e);
        // console.warn(e);
      } finally {
        //  next(receipt)
      }
    },
    [icoContract, address, icoTx]
  );
  const removeFromAdminList = useCallback(
    async (benificiary, next) => {
      if (!(icoContract && address)) return;

      if (!isAddress(benificiary)) {
        setReceipt({ code: 4000, message: "Invalid Address" });
        next({ code: 4000, message: "Invalid Address" });
        return;
      }
      // showLoader()
      try {
        // console.log("benificiary >>>>", benificiary);
        // setIsWorking("Approving...");
        let resp = await icoTx(
          "removing from Admin list...",
          "removed from admin list!",
          () =>
            icoContract.removeFromAdminList(
              benificiary
              // {
              //  gasPrice:200000000000,
              //  gasPrice:200000000000,
              //  nonce
              // }
            )
        );

        // hideLoader()
        setReceipt(resp);
        next(resp);
      } catch (e) {
        next(e);
        // console.log('error>.......', e)
        setReceipt(e);
        next(e);
        // console.warn(e);
      } finally {
        //  next(receipt)
      }
    },
    [icoContract, address, icoTx]
  );
  const withdrawTokens = useCallback(
    async (benificiary, next) => {
      if (!(icoContract && address)) return;

      if (!isAddress(benificiary)) {
        setReceipt({ code: 4000, message: "Invalid Address" });
        next({ code: 4000, message: "Invalid Address" });
        return;
      }
      // showLoader()
      try {
        // console.log("benificiary >>>>", benificiary);
        // setIsWorking("Approving...");
        let resp = await icoTx(
          "Withdrawing tokens...",
          "Tokens Withdrawn!",
          () =>
            icoContract.withdrawTokens(
              benificiary
              // {
              //  gasPrice:200000000000,
              //  gasPrice:200000000000,
              //  nonce
              // }
            )
        );

        // hideLoader()
        setReceipt(resp);
        next(resp);
      } catch (e) {
        next(e);
        // console.log('error>.......', e)
        setReceipt(e);
        next(e);
        // console.warn(e);
      } finally {
        //  next(receipt)
      }
    },
    [icoContract, address, icoTx]
  );
  const halt = useCallback(
    async (next) => {
      if (!(icoContract && address)) return;

      try {
        let resp = await icoTx("Halting...", "ICO Halted!", () =>
          icoContract.halt()
        );
          await icoContract.isHalt().then((response) => {
          console.log("halt response", resp);

          let isHalt = response ? "Halted" : "Active";
          setHaltStatus(isHalt);
        });
        setReceipt(resp);
        next(resp);
      } catch (e) {
        
        setReceipt(e);
        next(e);
        console.warn(e);
      } finally {
        //  next(receipt)
      }
    },
    [icoContract, address, icoTx]
  );
  const resume = useCallback(
    async (next) => {
      if (!(icoContract && address)) return;

      try {
        let resp = await icoTx("Resuming...", "ICO Started!", () =>
          icoContract.resume()
        );
          await icoContract.isHalt().then((response) => {
          console.log("resume response", resp);

          let isHalt = response ? "Halted" : "Active";
          setHaltStatus(isHalt);
        });
        setReceipt(resp);
        next(resp);
      } catch (e) {
        
        setReceipt(e);
        next(e);
        console.warn(e);
      } finally {
        //  next(receipt)
      }
    },
    [icoContract, address, icoTx]
  );
  const EthToWei = (n) => {
    return new web3.utils.BN(web3.utils.toWei(n.toString(), "ether"));
  };
  const WeiToEth = (n) => {
    return web3.utils.fromWei(n.toString(), "ether");
  };
  const isAddress = (address) => {
    return web3.utils.isAddress(address);
  };
  const buyTokens = useCallback(
    async (params, next) => {
      if (!(icoContract && address)) return;
      try {
        let { BTbenificiary: investor, BTvalue } = params;
        if (!isAddress(investor)) {
          setReceipt({ code: 4000, message: "Invalid Address" });
          next({ code: 4000, message: "Invalid Address" });
          return;
        }

        let resp = await icoTx("Buying Tokens...", "Purchased!", () =>
          icoContract.buyTokens(investor, {
            value: EthToWei(BTvalue).toString(),
          })
        );
        setReceipt(resp);
        next(resp);
      } catch (e) {
        setReceipt(e);
        next(e);
        console.warn(e);
      } finally {
        // setIsWorking(null);
      }
    },
    [icoContract, address, icoTx]
  );
  const checkBalance = useCallback(
    async (params, next) => {
      if (!(icoContract && address)) return;
      try {
        let { benificiary } = params;
        if (!isAddress(benificiary)) {
          setReceipt({ code: 4000, message: "Invalid Address" });
          next({ code: 4000, message: "Invalid Address" });
          return;
        }
        // let resp = await icoContract.balanceOf(benificiary);
        // let resp = await icoTx("Buying Tokens...", "Purchased!", () =>
        //   icoContract.balanceOf(
        //     benificiary
        //   )
        // );
        let resp = null;
         await icoContract.balanceOf(benificiary).then((rest)=>{
           resp = WeiToEth(rest)
         
        });
        setReceipt({ getCall: true, message: resp });
        next({ getCall: true, message: resp });
      } catch (e) {
        setReceipt(e);
        next(e);
        console.warn(e);
      } finally {
        // setIsWorking(null);
      }
    },
    [icoContract, address, icoTx]
  );
  const isWhitelistAdmin = useCallback(
    async (params, next) => {
      if (!(icoContract && address)) return;
      try {
        let { benificiary } = params;
        if (!isAddress(benificiary)) {
          setReceipt({ code: 4000, message: "Invalid Address" });
          next({ code: 4000, message: "Invalid Address" });
          return;
        }
        let resp = await icoContract.isWhitelistAdmin(benificiary);
        // let resp = await icoTx("Buying Tokens...", "Purchased!", () =>
        //   icoContract.balanceOf(
        //     benificiary
        //   )
        // );
        setReceipt({ getCall: true, status: resp });
        next({ getCall: true, status: resp });
      } catch (e) {
        setReceipt(e);
        next(e);
        console.warn(e);
      } finally {
        // setIsWorking(null);
      }
    },
    [icoContract, address, icoTx]
  );
  const isWhitelisted = useCallback(
    async (params, next) => {
      if (!(icoContract && address)) return;
      try {
        let { benificiary } = params;
        if (!isAddress(benificiary)) {
          setReceipt({ code: 4000, message: "Invalid Address" });
          next({ code: 4000, message: "Invalid Address" });
          return;
        }
        let resp = await icoContract.isWhitelisted(benificiary);
        // let resp = await icoTx("Buying Tokens...", "Purchased!", () =>
        //   icoContract.balanceOf(
        //     benificiary
        //   )
        // );
        setReceipt({ getCall: true, status: resp });
        next({ getCall: true, status: resp });
      } catch (e) {
        setReceipt(e);
        next(e);
        console.warn(e);
      } finally {
        // setIsWorking(null);
      }
    },
    [icoContract, address, icoTx]
  );
  return {
    halt,
    resume,
    checkBalance,
    isWhitelistAdmin,
    isWhitelisted,
    getOpeningTime,
    addWhitelisted,
    removeWhiteListed,
    addWhiteListAdmin,
    removeFromAdminList,
    buyTokens,
    setCurrentCall,
    isHalt,
    haltStatus,
    rate,
    status,
    openingTime,
    closingTime,
    depositWallet,
    icoToken,
    weiRaised,
    receipt,
    currentCall,
    isAddress,
    withdrawTokens
  };
};

export default useIco;

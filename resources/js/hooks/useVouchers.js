import { useCallback, useState } from "react";
import { ethers } from "ethers";
import { useWallet } from "../contexts/wallet";
import { useContracts } from "../contexts/contracts";
import { useNotifications } from "../contexts/notifications";
import LazyMinter from "../lib/LazyMinter";
import web3 from "web3";
import { formatTimestamp } from "../utills/big-number";
import axios from "axios";
const useVouchers = (tokenId) => {
  const { LazyNftContract } = useContracts();
  const { nftTx } = useNotifications();
  const { address, nonce, signer, privateSigner } = useWallet();

  const [receipt, setReceipt] = useState(null);
  const [currentCall, setCurrentCall] = useState(null);
  const [voucher, setVoucher] = useState();
  var DL = console.log.bind(console, "VOUCHERS:");
  const createVouchers = async () => {
    try {
      const lazyMinter = new LazyMinter({
        contract: LazyNftContract,
        signer: privateSigner,
      });
      const minPrice = ethers.constants.WeiPerEther.mul(2);
      const ids = [];
      for (let id of Array(11).keys()) {
        if (id != 0) ids.push(id);
      }
      generateVouchers(lazyMinter, ids, minPrice);
    } catch (err) {
      console.log("error", err);
    }
  };
  const generateVouchers = async (lazyMinter, ids, minPrice) => {
    await Promise.all(
      ids.map((id) => {
        return lazyMinter
          .createVoucher(
            id,
            `QmatPCGYnH7x6fgw3cpHx42ieQVCz5XuZn4q9k78g9QAYQ/${id}.json`,
            minPrice
          )
          .then(async (voucher) => {
            return voucher;
          });
      })
    )
      .then((voucherList) => {
        storeVouchers(voucherList);
        console.log("Voucher List", voucherList);
      })
      .catch((e) => {
        console.log("error", e);
      });
    // const toSet = resp.slice(0, 5);
    // setVoucher(resp);
  };

  const updateVoucher = (id) => {
    axios
      .post("/nftvouchers/" + id, {
        status: 1,

        _method: "patch",
      })
      .then((response) => {
        console.log("response :>> ", response);
      })
      .catch((err) => {
        console.log("error :>> ", err);
      });
  };
  const storeVouchers = (_voucherList) => {
    axios
      .post("/nftvouchers", { vouchers: _voucherList })
      .then((response) => {
        console.log("response :>> ", response);
      })
      .catch((err) => {
        console.log("error :>> ", err);
      });
  };
  const vouchersToMint = async (num) => {
    return await axios
      .get("vouchers-to-mint", {
        params: {
          num,
        },
      })
      .then(
        (response) => {
          return response.data;
        },
        (err) => {
          console.log("error", err);
        }
      );
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
    createVouchers,
    vouchersToMint,
    voucher,
    updateVoucher,
  };
};

export default useVouchers;

import { useState, useMemo, useEffect } from 'react';
import * as ethers from 'ethers';
import ERC20_CONTRACT_ABI from '../abis/erc20.json';
import { useWallet } from '../contexts/wallet';
import { sleep } from '../utills/promise';
import { toBigNumber } from '../utills/big-number';

const useTokenInfo = (tokenAddress) => {
  const [balance, setBalance] = useState(toBigNumber('0'));
  const [decimals, setDecimals] = useState(null);
  const [symbol, setSymbol] = useState(null);
  const [name, setName] = useState(null);
  const { address, signer } = useWallet();

  const contract = useMemo(
    () =>
      signer &&
      tokenAddress &&
      new ethers.Contract(tokenAddress, ERC20_CONTRACT_ABI, signer),
    [tokenAddress, signer]
  );

  useEffect(() => {
    if (!(contract && address)) return;

    const onBalanceChange = async (from, to) => {
      if (from === address || to === address) {
        await sleep(500);
        setBalance(toBigNumber(await contract.balanceOf(address)));
      }
    };

    const load = async () => {
      if (!(contract && address)) return;
      const [decimals, symbol, balance,name] = await Promise.all([
        contract.decimals(),
        contract.symbol(),
        contract.balanceOf(address),
        contract.name()
      ]);
      setDecimals(decimals);
      setSymbol(symbol);
      setBalance(toBigNumber(balance));
      setName(name)
    };

    const subscribe = () => {
      if (!contract) return () => {};
      const transferEvent = contract.filters.Transfer();
      contract.on(transferEvent, onBalanceChange);
      return () => {
        contract.off(transferEvent, onBalanceChange);
      };
    };

    load();
    return subscribe();
  }, [contract, address]);

  return {
    symbol,
    decimals,
    balance,
    name
  };
};

export default useTokenInfo;

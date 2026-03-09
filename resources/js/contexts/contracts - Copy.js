import { FC, useContext, useMemo, createContext, ReactNode } from 'react';
import { ethers } from 'ethers';

import {
  TOKEN_0_ADDRESS,
  TOKEN_1_ADDRESS,
  NFT_POSITIONS_MANAGER_ADDRESS,
  STAKING_REWARDS_ADDRESS,
  REWARD_TOKEN_ADDRESS,
  ICO_CONTRACT_ADDRESS
} from '../utills/config';
import { useWallet } from '../contexts/wallet';
import useTokenInfo from '../hooks/useTokenInfo';
import NFT_POSITIONS_MANAGER_ABI from '../abis/nft_positions_manager.json';
import STAKING_REWARDS_ABI from '../abis/staking_rewards.json';
import ICO_ABI from '../abis/ico_abi.json';

const ContractsContext = createContext(null);

export const ContractsProvider = ({
  children,
}) => {
  const { network,  signer , networkProvider } = useWallet();

  const token0Address = !network ? null : TOKEN_0_ADDRESS[network];
  const token1Address = !network ? null : TOKEN_1_ADDRESS[network];
  const rewardTokenAddress = !network ? null : REWARD_TOKEN_ADDRESS[network];

  const { decimals: token0Decimals, symbol: token0Symbol } = useTokenInfo(
    token0Address
  );
  const { decimals: token1Decimals, symbol: token1Symbol } = useTokenInfo(
    token1Address
  );
  const { decimals: rewardTokenDecimals, symbol: rewardTokenSymbol } = useTokenInfo(
    rewardTokenAddress
  );

  const nftManagerPositionsAddress = !network
    ? null
    : NFT_POSITIONS_MANAGER_ADDRESS[network];
  const stakingRewardsAddress = !network
    ? null
    : STAKING_REWARDS_ADDRESS[network];
const IcoContractAddress =  !network
? null
: ICO_CONTRACT_ADDRESS[network];
  const nftManagerPositionsContract = useMemo(
    () =>
      !(nftManagerPositionsAddress && signer)
        ? null
        : new ethers.Contract(
            nftManagerPositionsAddress,
            NFT_POSITIONS_MANAGER_ABI,
            signer
          ),
    [nftManagerPositionsAddress, signer]
  );

  const stakingRewardsContract = useMemo(
    () =>
      !(stakingRewardsAddress && signer)
        ? null
        : new ethers.Contract(
            stakingRewardsAddress,
            STAKING_REWARDS_ABI,
            signer
          ),
    [stakingRewardsAddress, signer]
  );
  const icoContract = useMemo(
    ()=>
    !(IcoContractAddress && signer)
    ? null 
    :new ethers.Contract(
      IcoContractAddress,
      ICO_ABI,
      signer
    )
  )

  return (
    <ContractsContext.Provider
      value={{
        token0Address,
        token1Address,
        rewardTokenAddress,
        token0Decimals,
        token1Decimals,
        rewardTokenDecimals,
        token0Symbol,
        token1Symbol,
        rewardTokenSymbol,
        stakingRewardsContract,
        nftManagerPositionsContract,
        icoContract
      }}
    >
      {children}
    </ContractsContext.Provider>
  );
};

export function useContracts() {
  const context = useContext(ContractsContext);
  if (!context) {
    throw new Error('Missing Contracts context');
  }
  const {
    token0Address,
    token1Address,
    rewardTokenAddress,
    token0Decimals,
    token1Decimals,
    rewardTokenDecimals,
    token0Symbol,
    token1Symbol,
    rewardTokenSymbol,
    stakingRewardsContract,
    nftManagerPositionsContract,
    icoContract
  } = context;

  return {
    token0Address,
    token1Address,
    rewardTokenAddress,
    token0Decimals,
    token1Decimals,
    rewardTokenDecimals,
    token0Symbol,
    token1Symbol,
    rewardTokenSymbol,
    stakingRewardsContract,
    nftManagerPositionsContract,
    icoContract
  };
}

import { useCallback, useState } from 'react';

import { useWallet } from '../contexts/wallet';
import { useContracts } from '../contexts/contracts';
import { useNotifications } from '../contexts/notifications';
import { useData } from '../contexts/data';

const usePosition = (tokenId) => {
  const { tx } = useNotifications();
  const { address , nonce } = useWallet();
  const {
    nftManagerPositionsContract,
    stakingRewardsContract, 
  } = useContracts();
  const { currentIncentive } = useData();

 
  const [isWorking, setIsWorking] = useState(null);
  const [isClaiming, setIsClaiming] = useState(null);
  // const [inProcess,setInProcess] = useState(false)
  const showLoader = ()=>{

    $(".large-loader").show();
 
  }
  const hideLoader = ()=>{

    $(".large-loader").hide();
 
  }
  // const updateInProcess = (status)=>{
  //   setInProcess(status);
  // }
  const approve = useCallback(
    async (next) => {
      if (
        !(
          nftManagerPositionsContract &&
          stakingRewardsContract &&
          currentIncentive
        )
      )
        return;
        // showLoader()
      try {
       console.log('got nonce',nonce)
        setIsWorking('Approving...');
        await tx('Approving...', 'Approved!', () =>
        
          nftManagerPositionsContract.approve(
            stakingRewardsContract.address,
            tokenId,
            // {
            //  gasPrice:200000000000,
            //  gasPrice:200000000000,
            //  nonce
            // }
          )
        );
        // hideLoader()
        next(true);
      } catch (e) {
        console.warn(e);
      } finally {
        setIsWorking(null);
      }
    },
    [
      tokenId,
      currentIncentive,
      stakingRewardsContract,
      nftManagerPositionsContract,
      tx,
    ]
  );

  const transfer = useCallback(
    async (next) => {
      if (
        !(
          address &&
          nftManagerPositionsContract &&
          stakingRewardsContract &&
          currentIncentive
        )
      )
        return;

      try {
        setIsWorking('Transfering...');
        await tx(
          'Transfering...',
          'Transfered!',
          () =>
            nftManagerPositionsContract[
              'safeTransferFrom(address,address,uint256)'
            ](address, stakingRewardsContract.address, tokenId) // https://stackoverflow.com/questions/68289806/no-safetransferfrom-function-in-ethers-js-contract-instance
        );
        next();
      } catch (e) {
        console.warn(e);
      } finally {
        setIsWorking(null);
      }
    },
    [
      tokenId,
      currentIncentive,
      stakingRewardsContract,
      nftManagerPositionsContract,
      address,
      tx,
    ]
  );

  const stake = useCallback(
    async (next) => {
      if (!(stakingRewardsContract && currentIncentive)) return;

      try {
        setIsWorking('Staking...');
        await tx('Staking...', 'Staked!', () =>
          stakingRewardsContract.stakeToken(currentIncentive.key, tokenId)
        );
        next();
      } catch (e) {
        console.warn(e);
      } finally {
        setIsWorking(null);
      }
    },
    [tokenId, currentIncentive, stakingRewardsContract, tx]
  );

  const unstake = useCallback(
    async (next) => {
      if (!(stakingRewardsContract && currentIncentive)) return;

      try {
        setIsWorking('Unstaking...');
        await tx('Unstaking...', 'Unstaked!', () =>
          stakingRewardsContract.unstakeToken(currentIncentive.key, tokenId)
        );
        next();
      } catch (e) {
        console.warn(e);
      } finally {
        setIsWorking(null);
      }
    },
    [tokenId, currentIncentive, stakingRewardsContract, tx]
  );

  const claim = useCallback(
    async (next) => {
      if (!(stakingRewardsContract && currentIncentive && address)) return;

      try {
        setIsWorking('Claiming...');
        setIsClaiming('Claiming...')
        const reward = await stakingRewardsContract.rewards(
          currentIncentive.key.rewardToken,
          address
        );
        await tx('Claiming...', 'Claimed!', () =>
          stakingRewardsContract.claimReward(
            currentIncentive.key.rewardToken,
            address,
            reward
          )
        );
        next();
      } catch (e) {
        console.warn(e);
      } finally {
        setIsWorking(null);
        setIsClaiming(null);
      }
    },
    [currentIncentive, address, stakingRewardsContract, tx]
  );

  const withdraw = useCallback(
    async (next) => {
      if (!(stakingRewardsContract && address)) return;

      try {
        setIsWorking('Withdrawing...');
        await tx('Withdrawing...', 'Withdrew!', () =>
          stakingRewardsContract.withdrawToken(tokenId, address, [])
        );
        next();
      } catch (e) {
        console.warn(e);
      } finally {
        setIsWorking(null);
      }
    },
    [tokenId, address, stakingRewardsContract, tx]
  );

  return { isWorking, approve, transfer, stake, unstake, claim, withdraw };
};

export default usePosition;


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
import { useContracts } from '../contexts/contracts';
import { useWallet } from '../contexts/wallet';
import { useNotifications } from '../contexts/notifications';
import usePosition from './usePosition';
import { useData } from '../contexts/data';
import * as ethers from "ethers";

 const useRewards = () => {
    // const classes = useStyles();
    const { stakingRewardsContract } = useContracts();
    const {
      currentIncentiveRewardTokenSymbol,
      currentIncentiveRewardTokenDecimals,
      currentIncentive,
    } = useData();
    // const {claim : claiimm} = usePosition()
    const { address } = useWallet();
    const { tx } = useNotifications();

    const [reward, setReward] = useState(ethers.BigNumber.from(0));
    const [isClaiming, setIsClaiming] = useState(false);
  
    useEffect(() => {
      if (!(stakingRewardsContract && currentIncentive && address)) return;
  
      let isMounted = true;
      const unsubs = [
        () => {
          isMounted = false;
        },
      ];
  
      
  
      const subscribe = () => {
        const tokenUnstakedEvent = stakingRewardsContract.filters.TokenUnstaked();
        stakingRewardsContract.on(tokenUnstakedEvent, load);
        unsubs.push(() => {
          stakingRewardsContract.off(tokenUnstakedEvent, load);
        });
  
        const rewardClaimedEvent = stakingRewardsContract.filters.RewardClaimed();
        stakingRewardsContract.on(rewardClaimedEvent, load);
        unsubs.push(() => {
          stakingRewardsContract.off(rewardClaimedEvent, load);
        });
      };
  
      // load();
      // subscribe();
  
      return () => {
        unsubs.map((u) => u());
      };
    }, [stakingRewardsContract, currentIncentive, address]);
    const subscribe = () => {
      const tokenUnstakedEvent = stakingRewardsContract.filters.TokenUnstaked();
      stakingRewardsContract.on(tokenUnstakedEvent, load);
      unsubs.push(() => {
        stakingRewardsContract.off(tokenUnstakedEvent, load);
      });

      const rewardClaimedEvent = stakingRewardsContract.filters.RewardClaimed();
      stakingRewardsContract.on(rewardClaimedEvent, load);
      unsubs.push(() => {
        stakingRewardsContract.off(rewardClaimedEvent, load);
      });
    };
    const load = async () => {
      let isMounted = true;
      const unsubs = [
        () => {
          isMounted = false;
        },
      ];
      const reward = await stakingRewardsContract.rewards(
        currentIncentive.key.rewardToken,
        address
      );
      if (isMounted) setReward(reward);
    };
    
    const claim = async () => {
      if (!(stakingRewardsContract && currentIncentive)) return;
  
      try {
        setIsClaiming(true);
        const reward = await stakingRewardsContract.rewards(
          currentIncentive.key.rewardToken,
          address
        );
        await tx("Claiming...", "Claimed!", () =>
          stakingRewardsContract.claimReward(
            currentIncentive.key.rewardToken,
            address,
            reward
          )
        );
      } catch (e) {
        console.warn(e);
      } finally {
        setIsClaiming(false);
      }
    };
    const updateReward = (reward) =>{
      setReward(reward)
    }
    return {load,subscribe,reward,updateReward}
  };
  export default useRewards;
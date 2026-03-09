import {
  FC,
  useState,
  useContext,
  useMemo,
  useEffect,
  createContext,
  ReactNode,
} from 'react';
import _flatten from 'lodash/flatten';
import _orderBy from 'lodash/orderBy';
import _round from 'lodash/round';

import { useWallet } from '../contexts/wallet';
import { useContracts } from '../contexts/contracts';
import useTokenInfo from '../hooks/useTokenInfo';
import { Incentive, LiquidityPosition } from '../utills/types';
import { toBigNumber } from '../utills/big-number';
import * as request from '../utills/request';
import { SUBGRAPHS ,REWARD_REFRESH_RATE  , TOKEN_0_ADDRESS,TOKEN_1_ADDRESS} from '../utills/config';

const DataContext = createContext(null);

export const DataProvider = ({ children }) => {
  const {
    stakingRewardsContract,
    nftManagerPositionsContract,
  } = useContracts();
  const { network, address } = useWallet();
  const [positions, setPositions] = useState([])
  const [incentives, setIncentiveIds] = useState([])
  const [inPorcess,setInProcess] = useState(false)
  const [currentIncentiveId, setCurrentIncentiveId] = useState(
    null
  );
  const [rewardCall, setRewardCall] = useState(0);
 const updateInProcess = (status)=>{
    setInProcess(status);
  }
  const currentIncentive = useMemo(
    () =>
      !currentIncentiveId
        ? null
        : incentives.find((incentive) => incentive.id === currentIncentiveId) ??
          null,
    [currentIncentiveId, incentives]
  );

  const {
    decimals: currentIncentiveRewardTokenDecimals,
    symbol: currentIncentiveRewardTokenSymbol,
  } = useTokenInfo(currentIncentive?.key.rewardToken ?? null);

  // load incentives
  useEffect(() => {
    if (!network) return;

    const load = async () => {
      const subgraph = request.subgraph(SUBGRAPHS[network.name]);
      const { incentives } = await subgraph(
        `query {
          incentives(where:{pool:"0x94fee67225c85ff29a7743696ba4b77b2b8262ad"} ,orderBy: endTime, orderDirection: desc) {
            id
            rewardToken
            pool
            startTime
            endTime
            refundee
            reward
            ended
          }
        }`,
        {}
      );
      setIncentiveIds(
        incentives.map(
          ({
            id,
            rewardToken,
            pool,
            startTime,
            endTime,
            refundee,
            reward,
            ended,
          }) =>
            ({
              id,
              reward: toBigNumber(reward),
              ended,
              key: {
                rewardToken,
                pool,
                startTime: Number(startTime),
                endTime: Number(endTime),
                refundee,
              },
            })
        )
      );
      setCurrentIncentiveId(incentives[0]?.id ?? null);
    };

    load();
  }, [network]);

  // load owned and transfered positions
  useEffect(() => {
    if (
      !(
        nftManagerPositionsContract &&
        stakingRewardsContract &&
        address &&
        currentIncentiveId &&
        currentIncentive
      )
    )
      return;

    let isMounted = true;
    const unsubs = [
      () => {
        isMounted = false;
      },
    ];

    const loadPositions = async (owner) => {
      const noOfPositions = await nftManagerPositionsContract.balanceOf(owner);
      const positions = await Promise.all(
        new Array(noOfPositions.toNumber())
          .fill(0)
          .map((_, index) => loadPosition(owner, index))
      );
      const ownerPositions = [];
      positions.forEach((position) => {
        if (position) {
          ownerPositions.push(position);
        }
      });
      return ownerPositions;
    };

    const loadPosition = async (
      owner,
      index
    ) => {
      const tokenId = await nftManagerPositionsContract.tokenOfOwnerByIndex(
        owner,
        index
      );
      // We can filter positions using positions methods because it 
      // returns token0 and token1 so we can check on that base
      const { liquidity ,token0,token1} = await nftManagerPositionsContract.positions( 
        tokenId
      );
      if (liquidity.isZero()) return null;
      const position = await stakingRewardsContract.deposits(tokenId);
      // if (owner !== address && position.owner !== address) return null;
     
      if ((owner !== address && position.owner !== address) || (token0 !== TOKEN_0_ADDRESS[network.name]
        &&  token1 !== TOKEN_1_ADDRESS[network.name])) return null;
      let staked = false;
      let reward = toBigNumber(0);
      const positionsGraph = request.subgraph(SUBGRAPHS[network.name]);
      const { positions: detail } = await positionsGraph(
        `query {
          positions(where:{tokenId:${Number(tokenId.toString())}}){
            id
            tokenId
            owner
            staked
            depositedToken0
            depositedToken1
            withdrawnToken0
            withdrawnToken1
          }
        }`,
        {}
      );
      let tokens = null;
      if (detail.length > 0) {
        let token0 = detail[0].depositedToken0 - detail[0].withdrawnToken0;
        let token1 = detail[0].depositedToken1 - detail[0].withdrawnToken1;
        tokens = {
          token0: _round(token0, 3).toFixed(3),
          token1: _round(token1, 3).toFixed(3),
        };


        if(detail[0].staked){

          try {
            const [rewardNumber] = await stakingRewardsContract.getRewardInfo(
              currentIncentive.key,
              tokenId
            );
            reward = toBigNumber(rewardNumber.toString());
            
            staked = true;
          } catch {}
        }
      }
      
      
      return {
        tokenId: Number(tokenId.toString()),
        owner,
        reward,
        staked,
        tokens
      };
    };

    const load = async () => {
      const owners = [address, stakingRewardsContract.address];
    
      const positions = await Promise.all(owners.map(loadPositions));
     
      if (isMounted) {
        setPositions(_orderBy(_flatten(positions), 'tokenId'));
      }
    };
    load();
    
    const interval = setInterval(() => {
     
      // load();
    }, REWARD_REFRESH_RATE);
  
    // unsubs.map((u) => u());
    return () => clearInterval(interval);
    // load();

    return () => {
    };
  }, [
    nftManagerPositionsContract,
    stakingRewardsContract,
    address,
    currentIncentiveId,
    currentIncentive,
    rewardCall
  ]);

  useEffect(() => {
    if (!(stakingRewardsContract && currentIncentiveId)) return;

    let isMounted = true;
    const unsubs = [
      () => {
        isMounted = false;
      },
    ];

    const updateStaked = async (tokenId, incentiveId) => {
      if (incentiveId !== currentIncentiveId) return;
      if (isMounted) {
        const positionss = await Promise.all(
          positions.map(async (position) => {
            if (position.tokenId !== Number(tokenId.toString()))
              return position;
            let reward = toBigNumber(0);
            try {
              const [rewardNumber] = await stakingRewardsContract.getRewardInfo(
                currentIncentive.key,
                position.tokenId
              );
              reward = toBigNumber(rewardNumber.toString());
            } catch {}
            if (reward.isZero()) reward = toBigNumber(0);
            position.staked = true;
            position.reward = reward;
            return position;
          })
        );
        setPositions(positionss);
      }
    };
    const updateStakedOld = (tokenId, incentiveId) => {
      if (incentiveId !== currentIncentiveId) return;
      if (isMounted) {
        setPositions((positions) =>
          positions.map((position) => {
            if (position.tokenId !== Number(tokenId.toString()))
              return position;
            position.staked = true;
            return position;
          })
        );
      }
    };

    const updateUnstaked = (tokenId, incentiveId) => {
      if (incentiveId !== currentIncentiveId) return;
      if (isMounted) {
        setPositions((positions) =>
          positions.map((position) => {
            if (position.tokenId !== Number(tokenId.toString()))
              return position;
            position.staked = false;
            position.reward = toBigNumber(0);
            return position;
          })
        );
      }
    };

    const subscribe = () => {
      const stakedEvent = stakingRewardsContract.filters.TokenStaked();
      const unstakedEvent = stakingRewardsContract.filters.TokenUnstaked();

      stakingRewardsContract.on(stakedEvent, updateStaked);
      stakingRewardsContract.on(unstakedEvent, updateUnstaked);

      unsubs.push(() => {
        stakingRewardsContract.off(stakedEvent, updateStaked);
      });
      unsubs.push(() => {
        stakingRewardsContract.off(unstakedEvent, updateUnstaked);
      });
    };

    subscribe();

    return () => {
      unsubs.map((u) => u());
    };
  }, [stakingRewardsContract, positions, currentIncentiveId]);

  return (
    <DataContext.Provider
      value={{
        positions,
        incentives,
        currentIncentiveId,
        rewardCall,
        currentIncentive,
        setCurrentIncentiveId,
        setRewardCall,
        currentIncentiveRewardTokenSymbol,
        currentIncentiveRewardTokenDecimals,
        inPorcess,
        updateInProcess
      }}
    >
      {children}
    </DataContext.Provider>
  );
};

export function useData() {
  const context = useContext(DataContext);
  if (!context) {
    throw new Error('Missing Data context');
  }
  const {
    positions,
    incentives,
    currentIncentiveId,
    rewardCall,
    currentIncentive,
    setCurrentIncentiveId,
    setRewardCall,
    currentIncentiveRewardTokenSymbol,
    currentIncentiveRewardTokenDecimals,
    inPorcess,
    updateInProcess
  } = context;

  return {
    positions,
    incentives,
    currentIncentiveId,
    rewardCall,
    currentIncentive,
    setCurrentIncentiveId,
    setRewardCall,
    currentIncentiveRewardTokenSymbol,
    currentIncentiveRewardTokenDecimals,
    inPorcess,
    updateInProcess
  };
}

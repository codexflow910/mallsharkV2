import React, { useEffect, useState, useCallback, useMemo } from "react";
import ReactDOM from "react-dom";
import Web3 from "web3";
import _flatten from "lodash/flatten";
import _orderBy from "lodash/orderBy";
import { toBigNumber } from "../utills/big-number.js";
import * as request from "../utills/request";
const config = require("./config.js");
import moment from 'moment';
import { ethers } from "ethers";
import NFT_POSITIONS_MANAGER_ABI from "../abis/nft_positions_manager.json";
import STAKING_REWARDS_ABI from "../abis/staking_rewards.json";
import MetaMaskOnboarding from "@metamask/onboarding";
import { formatUnits } from "../utills/big-number.js";
const RPC = "https://rinkeby.infura.io/v3/";
const App = () => {
  const onboarding = React.useRef();
  const [accounts, setAccounts] = React.useState([]);
  const [signer, setSigner] = React.useState(null);
  const [address, setAddress] = useState(null);
  const [network, setNetwork] = useState(null);
  const [positions, setPositions] = useState([]);
  const [incentives, setIncentiveIds] = useState([]);
  const [reward, setReward] = useState(ethers.BigNumber.from(0))
  const [currentIncentiveId, setCurrentIncentiveId] = useState(null);
  let provider = new Web3.providers.HttpProvider(RPC);
  const nftManagerPositionsContract = useMemo(
    () =>
      !(config.nftManagerPositionsAddress && signer)
        ? null
        : new ethers.Contract(
            config.nftManagerPositionsAddress,
            NFT_POSITIONS_MANAGER_ABI,
            signer
          ),
    [config.nftManagerPositionsAddress, signer]
  );
  useEffect(() => {
    if (!onboarding.current) {
      onboarding.current = new MetaMaskOnboarding();
    }
  }, []);
  useEffect(() => {
    async function handleNewAccounts(newAccounts) {
      setAccounts(newAccounts);
      await setProvider(window.ethereum);
      await loadIncentives();
      // await loadReward();
      // const signer = provider.getSigner();
      // setSigner(signer);
      // getUSDCBalance()
    }
    if (MetaMaskOnboarding.isMetaMaskInstalled()) {
      window.ethereum
        .request({ method: "eth_requestAccounts" })
        .then(handleNewAccounts);
      window.ethereum.on("accountsChanged", handleNewAccounts);
      return () => {
        window.ethereum.off("accountsChanged", handleNewAccounts);
      };
    }
  }, []);
  const setProvider = useCallback(async (web3Provider) => {
    web3Provider.on("accountsChanged", () => {
      window.location.reload();
    });
    web3Provider.on("chainChanged", () => {
      window.location.reload();
    });
    // web3Provider.on('disconnect', () => {
    //   disconnect();
    // });
    const provider = new ethers.providers.Web3Provider(web3Provider);

    const { name :network } = await provider.getNetwork();
    setNetwork(network);
    // setNetwork(~['homestead'].indexOf(network) ? NETWORK_MAINNET : network)
    const signer = provider.getSigner();

    setSigner(signer);
    setAddress(await signer.getAddress());
    
  }, []);
  useEffect(()=>{
    loadReward();
    
  })
  useEffect(()=>{
    load();
    
  })

  const stakingRewardsContract = useMemo(
    () =>
      !(config.stakingRewardsAddress && signer)
        ? null
        : new ethers.Contract(
            config.stakingRewardsAddress,
            STAKING_REWARDS_ABI,
            signer
          ),
    [config.stakingRewardsAddress, signer]
  );
 

 
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
  const loadPosition = async (owner, index) => {
    
    const tokenId = await nftManagerPositionsContract.tokenOfOwnerByIndex(
      owner,
      index
    );

    const { liquidity } = await nftManagerPositionsContract.positions(tokenId);

    if (liquidity.isZero()) {
      return null;
    }
    const position = await stakingRewardsContract.deposits(tokenId);
    if (owner !== address && position.owner !== address) {
      return null;
    }

    let staked = false;
    let reward = toBigNumber(0);
    try {
      const [rewardNumber] = await stakingRewardsContract.getRewardInfo(
        currentIncentive.key,
        tokenId
      );
      reward = toBigNumber(rewardNumber.toString());
      
      staked = true;
    } catch {}
    return {
      tokenId: Number(tokenId.toString()),
      owner,
      reward,
      staked,
    };
  };
  const loadIncentives = async () => {
    const subgraph = request.subgraph(config.SUBGRAPHS);
 
    const { incentives } = await subgraph(
      `query {
          incentives(orderBy: endTime, orderDirection: desc) {
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
        }) => ({
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
  const currentIncentive = useMemo(
    () =>
      !currentIncentiveId
        ? null
        : incentives.find((incentive) => incentive.id === currentIncentiveId) ??
          null,
    [currentIncentiveId, incentives]
  );
  const load = async () => {
    const owners = [address, stakingRewardsContract.address];
    
    const positions = await Promise.all(owners.map(loadPositions));
   

    setPositions(_orderBy(_flatten(positions), "tokenId"));
    
  };
  const loadReward = async () => {
    const reward = await stakingRewardsContract.rewards(
      currentIncentive.key.rewardToken,
      address
    );
    setReward(reward);
  };
  const AddressNetwork = () => {
 
    const shortAddress =    address && `${address.slice(0, 6)}....${address.slice(-4)}`;
    return (
      <>
       
      <span>Account:   {shortAddress}</span>
          <span>Network: {network}</span>

      </>
     
    )
  }
 
  const LiquidityPositionTableRow = ({ position }) => {
    return (
      <tr>
        <td>{position.tokenId.toString()}</td>
        <td>{formatUnits(position.reward, 18)}</td>

        <td className="text-right ">
          <a href="#" className={position.staked ? 'btn btn-primary' : 'btn btn-outline-primary'}>
          {position.staked ? 'Unstake' : 'Stake'}
          </a>
          &nbsp;&nbsp;
          <a href="#" className="btn btn-outline-primary">
            Withdraw
          </a>
        </td>
      </tr>
    );
  };
  return (
    <div className="container stkngbx">
      <div className="row mt-50">
        <div className="col-md-6">
          <h3>Staking</h3>
        </div>
        <div className="col-md-6 text-right">
        {
      !address ? null : (
        
        <AddressNetwork
        
        />
      )
    }
          
        </div>
        <hr />
      </div>

      <div className="row mt-50">
        <div className="col-md-12">
          <h2 className="text-center">
            You have 4 MATIC-ZIT Liquidity Positions
          </h2>
          <h3 className="text-center text-primary">
            Get more by providing liquidity to the MATIC-ZIT pool over here
          </h3>
        </div>

        <hr />

        <div className="row">
          <div className="col-md-6 mb-40">
            
            {!currentIncentiveId ? (
               <span>Loading Incentives ...</span>
            ) : (
                  <>
                    
                    <select
                    class="form-select" aria-label="Default select example"
                      labelId='incentive-label'
                      id='incentive'
                      value={currentIncentiveId}
                      displayEmpty
                      onChange={(e) => {
                        setCurrentIncentiveId(e.target.value);
                      }}
                    >
                      {incentives.map((incentive) => (
                        <option value={incentive.id} key={incentive.id}>
                          {formatTimestamp(incentive.key.startTime)} -{' '}
                          {formatTimestamp(incentive.key.endTime)}{' '}
                          {incentive.ended ? 'ENDED' : ''}
                        </option>
                      ))}
                    </select>
                  </>
                )}
              
          </div>
          <div className="col-md-6 text-right mb-40">
            <span> Rewards: {' '} {formatUnits(reward, 18)} {' '} ZIT</span>

            <a href="#" className="btn btn-primary">
              Claim
            </a>{" "}
          </div>
          
          <hr />
          <table className="tblnm table table-striped">
            <tr>
              <th>ID</th> <th>Rewards</th> <th></th>
            </tr>

            {positions.map((position) => {
              return (
                // <h2>hamzza</h2>
                <LiquidityPositionTableRow
                  key={position.tokenId}
                  {...{ position }}
                />
              );
            })}

      
          </table>
        </div>
      </div>
    </div>
  );
};
function formatTimestamp(unix) {
  return moment.unix(unix).local().format('YYYY-MM-DD HHmm[h]');
}
export default App;
if (document.getElementById("staking")) {
  ReactDOM.render(<App />, document.getElementById("staking"));
}

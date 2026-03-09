import { FC, useCallback, useState, useEffect } from "react";
import { makeStyles } from "@material-ui/core/styles";
import { withRouter } from "react-router-dom";
import * as ethers from "ethers";
import Box from "@material-ui/core/Box";
import Button from "@material-ui/core/Button";
import Typography from "@material-ui/core/Typography";
import Table from "@material-ui/core/Table";
import TableHead from "@material-ui/core/TableHead";
import TableBody from "@material-ui/core/TableBody";
import TableRow from "@material-ui/core/TableRow";
import TableCell from "@material-ui/core/TableCell";
import Select from "@material-ui/core/Select";
import MenuItem from "@material-ui/core/MenuItem";
import InputLabel from "@material-ui/core/InputLabel";
import FormControl from "@material-ui/core/FormControl";
import Tooltip from "@material-ui/core/Tooltip";
import InfoIcon from "@material-ui/icons/Info";
import moment from "moment";
import usePosition from "../hooks/usePosition";
import { useWallet } from "../contexts/wallet";
import { useContracts } from "../contexts/contracts";
import { useNotifications } from "../contexts/notifications";
import { useData } from "../contexts/data";
import { LiquidityPosition } from "../utills/types";
import { formatUnits } from "../utills/big-number";

export const useStyles = makeStyles((theme) => ({
  maxButton: {
    height: 35,
  },
  depositButtonCell: {
    width: 110,
    padding: 5,
  },
  depositButton: {
    width: 100,
  },
}));

const Stake = ({ history }) => {
  const classes = useStyles();
  const {
    startConnecting: startConnectingWallet,
    address,
    connectMetamask,
  } = useWallet();
  const { token0Address, token1Address, token0Symbol, token1Symbol } =
    useContracts();
  const { positions, currentIncentiveId, incentives, setCurrentIncentiveId } =
    useData();

  return (
    <>
      <Box p={5}>
        {!address ? (
          <>
            <Box>
              <Typography variant="h5">
                You are about to earn rewards by staking Witnet tokens on
                Ethereum!
              </Typography>
            </Box>

            <Box mt={2}>
              <Button
                color="secondary"
                variant="contained"
                // onClick={startConnectingWallet}
                onClick={connectMetamask}
              >
                Connect Wallet
              </Button>
            </Box>
          </>
        ) : (
          <>
            <Box>
              <Typography>
                You have {positions.length} {token0Symbol}-{token1Symbol}{" "}
                liquidity positions.
              </Typography>
            </Box>

            <Box>
              <Typography variant="caption">
                Get {!positions.length ? "some" : "more"} by providing liquidity
                to the {token0Symbol}-{token1Symbol} Pool over{" "}
                <a
                  href={`https://app.uniswap.org/#/add/${[
                    token1Address,
                    token0Address,
                  ].join("/")}`}
                  target="_blank"
                  rel="noopener noreferrer"
                >
                  here
                </a>
                .
              </Typography>
            </Box>

            <Box m={2} mt={3} className="flex flex-grow justify-space">
              <FormControl>
                {!currentIncentiveId ? (
                  <Box>
                    <Typography variant="h5"> 
                      No Incentives available
                    </Typography>
                  </Box>
                ) : (
                  <>
                    <InputLabel id="incentive-label" shrink>
                      Incentive
                    </InputLabel>
                    <Select
                      labelId="incentive-label"
                      id="incentive"
                      value={currentIncentiveId}
                      displayEmpty
                      onChange={(e) => {
                        setCurrentIncentiveId(e.target.value);
                      }}
                    >
                      {incentives.map((incentive) => (
                        <MenuItem value={incentive.id} key={incentive.id}>
                          {formatTimestamp(incentive.key.startTime)} -{" "}
                          {formatTimestamp(incentive.key.endTime)}{" "}
                          {incentive.ended ? "ENDED" : ""}
                        </MenuItem>
                      ))}
                    </Select>
                  </>
                )}
              </FormControl>

              <ClaimAvailableReward />
            </Box>

            {!positions.length ? null : (
              <Box mt={2}>
                <Table aria-label="Loans" size={"small"}>
                  <TableHead>
                    <TableRow>
                      <TableCell>ID</TableCell>
                      <TableCell>Size</TableCell>
                      <TableCell>Rewards</TableCell>
                      <TableCell
                        align="right"
                        className={classes.depositButtonCell}
                      ></TableCell>
                      <TableCell
                        align="right"
                        className={classes.depositButtonCell}
                      ></TableCell>
                    </TableRow>
                  </TableHead>
                  <TableBody>
                    {positions.map((position) => (
                      <LiquidityPositionTableRow
                        key={position.tokenId}
                        {...{ position, history }}
                      />
                    ))}
                  </TableBody>
                </Table>
              </Box>
            )}
          </>
        )}
      </Box>
    </>
  );
};

const LiquidityPositionTableRow = ({ position, history }) => {
  const classes = useStyles();
  const { address } = useWallet();
  const { token0Decimals, token0Symbol, token1Symbol ,rewardTokenDecimals,rewardTokenSymbol } = useContracts();
  const {
    isWorking,
    unstake: unstakee,
    approve,
    transfer,
    stake: stakee,
    claim,
    withdraw: withdraww,
    isClaiming,
  } = usePosition(position.tokenId);
  const { currentIncentiveId } = useData();
  const { nftManagerPositionsContract, stakingRewardsContract } =
    useContracts();
  const stake = useCallback(async () => {
    const load = async () => {
      if (!(stakingRewardsContract && nftManagerPositionsContract)) return;
      
      const [approvedAddress, owner] = await Promise.all([
        nftManagerPositionsContract.getApproved(position.tokenId),
        nftManagerPositionsContract.ownerOf(position.tokenId),
      ]);
      position.owner = owner;
      if (owner === stakingRewardsContract.address) {
       await stakee(() => {});
        // setActiveStep(2);
      } else if (approvedAddress === stakingRewardsContract.address) {
       await transfer(() => {

          load();
        });

        // setActiveStep(1);
      } else {
        await approve(() => {
          
          load();
        });
      }
    };
    load();
    // history.push(`/stake/${position.tokenId}`);
  }, [position.tokenId, history]);

  const unstake = useCallback(async () => {
    unstakee(history.push("/staking"));
    // history.push(`/unstake/${position.tokenId}`);
  }, [position.tokenId, history]);

  const withdraw = useCallback(async () => {
    if (!(stakingRewardsContract && currentIncentiveId)) return;
    const load = async () => {
      const deposit = await stakingRewardsContract.deposits(position.tokenId);
      const isStaked = deposit?.numberOfStakes !== 0;
      if (!isStaked) {
        // setActiveStep(1);
        if (!(stakingRewardsContract && nftManagerPositionsContract)) return;
        const [approvedAddress, owner] = await Promise.all([
          nftManagerPositionsContract.getApproved(position.tokenId),
          nftManagerPositionsContract.ownerOf(position.tokenId),
        ]);
        
        position.owner = owner;
        if (owner === stakingRewardsContract.address) {
          withdraww(async () => {
            await claim(() => {
              stake();
            });
          });
          // setActiveStep(2);
        } else if (approvedAddress === stakingRewardsContract.address) {
          await claim(() => {
            stake();
          });

          // setActiveStep(1);
        }

        // withdraww(async () => {

        //   await claim(() => {
        //     stake();
        //   });
        // });
      } else {
        unstakee(() => {
          load();
        });
      }
    };
    load();

    // history.push(`/withdraw/${position.tokenId}`);
  }, [position.tokenId, history]);

  return (
    <TableRow>
      <TableCell component="th" scope="row">
        {position.tokenId.toString()}
      </TableCell>
      <TableCell component="th" scope="row">
        {!position.tokens
          ? "N/A"
          : `${position.tokens.token0} ${token0Symbol} - ${position.tokens.token1} ${token1Symbol} `}
      </TableCell>
      <TableCell>
        {!position.reward.isZero() ? (
          <Box className="flex items-center">
            <Box mr={1}>{`${formatUnits(position.reward, rewardTokenDecimals)} ${rewardTokenSymbol}`}</Box>
            <Tooltip
              title="Unstake position in order to claim accrued rewards."
              arrow
              placement="top"
            >
              <Box className="flex items-center cursor">
                <InfoIcon fontSize="small" />
              </Box>
            </Tooltip>
          </Box>
        ) : (
          "-"
        )}
      </TableCell>
      <TableCell align="right" className={classes.depositButtonCell}>
        <Button
          color="secondary"
          variant="contained"
          onClick={position.staked ? unstake : stake}
          className={classes.depositButton}
          disabled={position.staked}
        >
          {isWorking ? isWorking : "Deposit"}
        </Button>
      </TableCell>

      <TableCell align="right" className={classes.depositButtonCell}>
        <Button
          color="secondary"
          variant="contained"
          onClick={withdraw}
          className={classes.depositButton}
          disabled={position.owner === address}
        >
          {isClaiming ? isClaiming : "Claim"}
        </Button>
      </TableCell>
    </TableRow>
  );
};

const ClaimAvailableReward = () => {
  const classes = useStyles();
  const { stakingRewardsContract } = useContracts();
  const {
    currentIncentiveRewardTokenSymbol,
    currentIncentiveRewardTokenDecimals,
    currentIncentive,
  } = useData();
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

    const load = async () => {
      const reward = await stakingRewardsContract.rewards(
        currentIncentive.key.rewardToken,
        address
      );
      if (isMounted) setReward(reward);
    };

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

    load();
    subscribe();

    return () => {
      unsubs.map((u) => u());
    };
  }, [stakingRewardsContract, currentIncentive, address]);

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

  return (
    <Box className="flex items-center">
      {currentIncentive ? (
        <>
         <Box mr={1}>REWARDS:</Box>{" "}
      <Box mr={2}>
        {formatUnits(reward, currentIncentiveRewardTokenDecimals)}{" "}
        {currentIncentiveRewardTokenSymbol}
      </Box>
        </>
      ) : (<></>)}
     
      {/* <Button
        color="secondary"
        variant="contained"
        onClick={claim}
        className={classes.depositButton}
        disabled={isClaiming || reward.isZero()}
      >
        {isClaiming ? "Claiming..." : "Claim"}
      </Button> */}
    </Box>
  );
};

function formatTimestamp(unix) {
  return moment.unix(unix).local().format("YYYY-MM-DD HHmm[h]");
}

export default withRouter(Stake);

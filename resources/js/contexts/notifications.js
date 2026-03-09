import { FC, useContext, createContext, ReactNode,useState } from 'react';
import { useSnackbar } from 'notistack';
import * as ethers from 'ethers'
// import {useData} from './data'
const NotificationsContext = createContext(null);

export const NotificationsProvider = ({
  children,
}) => {
  const { enqueueSnackbar } = useSnackbar();
  // const [inProcess,setInProcess] = useState(false)
  // const {inProcess,updateInProcess} = useData()
  const [globalReward,setGlobalReward] = useState(ethers.BigNumber.from(0))
  //  const updateInProcess = (status)=>{
  //   setInProcess(status);
  // }
   const updateGlobalReward = (reward)=>{
    setGlobalReward(reward);
  }
  const showTxNotification = (description, hash) => {
    enqueueSnackbar(
      { type: 'tx', description, hash },
      {
        persist: true,
      }
    );
  };

  const showErrorNotification = (msg) => {
    enqueueSnackbar(
      {
        type: 'error',
        message: msg?.error?.message || msg.responseText || msg.message || msg,
      },
      {
        persist: true,
      }
    );
  };

  const showSuccessNotification = (title, message) => {
    enqueueSnackbar(
      {
        type: 'success',
        title,
        message,
      },
      {
        persist: true,
      }
    );
  };

  const tx = async (
    startNotification,
    endNotification,
    makeTx= () => { hash, wait }
  ) => {
    try {
     
      const { hash, wait } = await makeTx();
      showTxNotification(startNotification, hash);
      await wait();
      // // updateInProcess(false)
      showTxNotification(endNotification, hash);
      
    } catch (e) {
      // updateInProcess(false)
      showErrorNotification(e);
      throw e;
    }
  };
  const nftTx = async (
    startNotification,
    endNotification,
    makeTx= () => { hash, wait }
  ) => {
    try {
      const res = await makeTx();
      // const { hash, wait } = await makeTx();
      // showTxNotification(startNotification, hash);
     let wai =  await res.wait();
      // // updateInProcess(false)
      // showTxNotification(endNotification, hash);
      return wai;
    } catch (e) {
      // updateInProcess(false)
      // showErrorNotification(e);
      throw e;
    }
  };
  const icoTx = async (
    startNotification,
    endNotification,
    makeTx= () => { hash, wait }
  ) => {
    try {
      const res = await makeTx();
      // const { hash, wait } = await makeTx();
      // showTxNotification(startNotification, hash);
     let wai =  await res.wait();
      // // updateInProcess(false)
      // showTxNotification(endNotification, hash);
      return wai;
    } catch (e) {
      // updateInProcess(false)
      // showErrorNotification(e);
      throw e;
    }
  };

  return (
    <NotificationsContext.Provider
      value={{
        showTxNotification,
        showErrorNotification,
        showSuccessNotification,
        tx,
        icoTx,
        nftTx,
        
        // updateInProcess,
        // inProcess,
        globalReward,
        updateGlobalReward
      }}
    >
      {children}
    </NotificationsContext.Provider>
  );
};

export function useNotifications() {
  const context = useContext(NotificationsContext);
  if (!context) {
    throw new Error('Missing Notifications context');
  }
  const {
    showTxNotification,
    showErrorNotification,
    showSuccessNotification,
    tx,
    icoTx,
    nftTx,
    // updateInProcess,
    // inProcess,
    globalReward,
    updateGlobalReward
  } = context;
  return {
    showTxNotification,
    showErrorNotification,
    showSuccessNotification,
    tx,
    icoTx,
    nftTx,
    // updateInProcess,
    // inProcess,
    globalReward,
    updateGlobalReward
  };
}

import { FC, useState, useContext, createContext, ReactNode } from 'react';

const UIContext = createContext();

 const UIProvider= ({ children }) => {
  const [notification, notify] = useState();

  const showErrorNotification = (e) => {
    notify(e.message);
  };

  return (
    <UIContext.Provider
      value={{
        notification,
        notify,
        showErrorNotification,
      }}
    >
      {children}
    </UIContext.Provider>
  );
};

export default UIProvider;

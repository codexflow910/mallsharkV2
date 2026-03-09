import { FC, useState, useContext, createContext, ReactNode } from 'react';

const UIContext = createContext<{
  notification: null,
  notify : '',
  showErrorNotification : '',
};

// export const UIProvider= ({ children }) => {
//   const [notification, notify] = useState();

//   const showErrorNotification = (e) => {
//     notify(e.message);
//   };

//   return (
//     <UIContext.Provider
//       value={{
//         notification,
//         notify,
//         showErrorNotification,
//       }}
//     >
//       {children}
//     </UIContext.Provider>
//   );
// };

export function useUI() {
  const context = useContext(UIContext);
  if (!context) {
    throw new Error('Missing UI context');
  }
  const { notification, notify, showErrorNotification } = context;

  return {
    notification,
    notify,
    showErrorNotification,
  };
}

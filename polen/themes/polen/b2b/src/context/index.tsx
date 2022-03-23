import React, { createContext, useContext } from "react";
import { polMessageInitial } from "components/PolMessage";

const AppContext = createContext(null);

export function AppWrapper({ children }) {
  const [polMessage, setPolMessage] = React.useState(polMessageInitial);
  let sharedState = {
    polMessage: polMessage,
    setPolMessage: setPolMessage,
  };

  return (
    <AppContext.Provider value={sharedState}>{children}</AppContext.Provider>
  );
}

export function useAppContext() {
  return useContext(AppContext);
}

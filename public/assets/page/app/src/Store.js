import React, {createContext, Fragment, useReducer} from 'react';
import combineReducers from "react-combine-reducers";
import {booksState, booksReducer} from "./container/Books/store/reducer";
import AlertModal, {TYPE_ERROR} from "./components/AlertModal";
import {appReducer, appState} from "./appReducer";
import * as Action from "./appAction";

const Store = createContext({});
const {Provider} = Store;

const [rootReducerCombined, initialStateCombined] = combineReducers({
  app: [appReducer,appState],
  books: [booksReducer,booksState],
});
const StateProvider = ({children}) => {
  const [state, dispatch] = useReducer(rootReducerCombined, initialStateCombined, () => initialStateCombined);
  const closeAlertError = () => {
    dispatch({type: Action.SET_ERROR, message: null})
  };
  return <Fragment>
    <Provider value={{state, dispatch}}>{children}</Provider>
    {state.app?.systemError && <AlertModal type={TYPE_ERROR} message={state.app.systemError} closeHandler={closeAlertError}/>}
  </Fragment>;
};

export { Store, StateProvider }

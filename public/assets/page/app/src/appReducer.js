import * as Action from "./appAction";

export const appState ={
  systemError: null
};

export const appReducer = (state, action) => {
  if (action.type === Action.SET_ERROR) {
    return {...state, systemError: action.message}
  }
  return state
};
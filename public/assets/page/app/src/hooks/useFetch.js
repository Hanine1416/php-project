import {useContext} from "react";
import {Store} from "../Store";
import {baseUrl, messages, queryParamBuilder} from "../utils";

function useFetch(){
  const {dispatch} = useContext(Store);

  return async (path,method,data,options={}) => {
    try{
      let url = queryParamBuilder(`${baseUrl}${path}`,options.query);
      if(method.toUpperCase() === 'GET'){
        data = null;
      }
      let response = await fetch(url,{
        method:method,
        body:data,
        headers:{
          ['X-Requested-With']: 'XMLHttpRequest',
        },
        ...options
      });
      if(response.status >= 200 && response.status<300){
        return await response.json()
      }else{
        throw response;
      }
    }catch (e) {
      dispatch({type:'SET_ERROR',message:e.msg??messages.error});
      throw new Error('Unexpected action');
    }
  }
}
export default useFetch;

import {isEmpty} from 'lodash';

export const isMobile = () => {
  return /(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|Android|Silk|lge |maemo|midp|mmp|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i.test(navigator.userAgent)
};
export const baseUrl = window.baseUrl;
export const imageCdn = window.imageCdn;
export const moment = window.moment;
export const messages = window.messages?{...window.messages,error:window.errorMsg}:{error:window.errorMsg};
export const languages = window.languages;
/** Build url with query params */
export const queryParamBuilder = (path,params) => {
  let url = new URL(path);
  if(params){
    Object.keys(params).forEach(key => {
      let param = params[key] instanceof Array ? params[key].join(';'):params[key];
      if(param){
        url.searchParams.append(key, param)
      }
    });
  }
  return url;
};

/** Get query params as array */
export const queryParamsReader = () => {
  let url = new URL(window.location.href);
  let params = [];
  url.searchParams.forEach((value,key) =>{
    params[key] = value.includes(';')?value.split(';'):value;
  });
  return params;
};

/** Save query param into url */
export const historyPush = (key,value,state={}) => {
  let url = new URL(window.location.href);
  /** Check if value is an object of multi data */
  if(value instanceof Object){
    Object.keys(value).forEach(field => {
      let elem = value[field] instanceof Array ?value[field].join(';'):value[field];
      if(elem)
        url.searchParams.set(field,elem);
      else
        url.searchParams.delete(field);
    })
  }else{
    value = value instanceof Array ?value.join(';'):value;
    if(!isEmpty(value))
      url.searchParams.set(key,value);
    else
      url.searchParams.delete(key);
  }
  window.history.pushState(state,`changing ${key}`,url);
};
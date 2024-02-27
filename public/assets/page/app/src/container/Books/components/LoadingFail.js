import React from 'react';
import {messages} from "../../../utils";

function LoadingFail(props) {
  return (
    <div className="loading-error block-center text-center">
      <img src={"/assets/img/error-cloud.png"} width="100" alt="loading fail icon"/>
      <div className="mt-20">
        <h3 className="mb-10">{messages.data_loading_failed}</h3>
        <button onClick={props.reloadData}><i className="fas fa-sync-alt"/> {messages.try_again}</button>
      </div>
    </div>
  );
}

export default LoadingFail;
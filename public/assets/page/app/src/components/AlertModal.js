import React, {Fragment} from 'react';
import PropTypes from 'prop-types';
import {messages} from "../utils";

export const TYPE_CONFIRM = "TYPE_CONFIRM";
export const TYPE_INFO = "TYPE_INFO";
export const TYPE_ERROR = "TYPE_ERROR";
function AlertModal(props) {
  let bodyClass = 'confirm-info';
  let msgClass = 'info-msg';
  let controls = <button onClick={() => props.closeHandler()} className="btn btn-blue reverse" id="box-ok">Ok</button>;
  if (props.type === TYPE_CONFIRM) {
    controls =
      <Fragment>
        <button onClick={() => props.closeHandler()}
                className="btn btn-blue reverse"  style={{marginRight:'24px'}}>{messages.alert_box_btn_no}</button>
        <button onClick={() => props.confirmHandler()} className="btn btn-blue">
          {messages.alert_box_btn_yes} <span/>
        </button>
      </Fragment>;
    msgClass = "confirm-msg";
  }else if(props.type === TYPE_ERROR){
    bodyClass = 'error-info';
    msgClass = 'error-msg';
  }

  return (
    <div className={"box active "+bodyClass}>
      <div className="confirmBox">
        <div className="box-content">
          <div className="box-img">
          </div>
          <div className="box-messages">
            <p className={"box-msg "+msgClass} style={{display:'block'}}>{props.message}</p>
          </div>
        </div>
        <div className="box-footer">
          {controls}
        </div>
      </div>
      <div className="confirmBox-background"/>
    </div>
  );
}

AlertModal.prototype = {
  type: PropTypes.oneOf([TYPE_CONFIRM,TYPE_ERROR,TYPE_INFO]).isRequired,
  message: PropTypes.string.isRequired,
  closeHandler: PropTypes.func.isRequired,
  confirmHandler: PropTypes.func
};
export default AlertModal;
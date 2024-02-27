import React from "react";

function Checkbox(props) {

  return (
    <div className={"small-12 medium-12 large-12 columns checkbox__item last-check-box "+props.className}>
      <input checked={props.checked} disabled={props.attrDisabled} onChange={e => props.changeHandler(e.target.checked,props.field,props.value)}
             id={props.id} type="checkbox" className="checkbox__input"/>
        <label htmlFor={props.id}  className="checlbox__label small-checkbox" >{props.label}</label>
    </div>
  )
}

export default Checkbox
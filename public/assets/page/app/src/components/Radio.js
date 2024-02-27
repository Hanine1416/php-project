import React from 'react';

function Radio(props) {
  return (
    <div className="checkbox__item radio-box mt_checkbox small-12 medium-12 large-3 columns ">
      <input checked={props.checked} name={props.name} onChange={e => props.changeHandler(props.value)} id={props.id}  type="radio"
             className="checkbox__input asc"/>
      <label htmlFor={props.id}  className="checlbox__label">{props.label}</label>
    </div>
  );
}

export default Radio;
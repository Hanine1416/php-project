import React, {Fragment, useRef, useEffect} from 'react';
import {messages} from "../utils";

const $ = window.$;

function Selector(props) {
  const selectRef = useRef(null);
  const showError = !props.valid && props.isRequired;

  /** Handle select2 plugin */
  useEffect(() => {
    let elem = selectRef.current;
    if (elem && props.select2 && !$(elem).data('select2')) {
      let parent = $(elem).closest('.modal');
      $(elem).select2({
        placeholder: props.placeholder ? props.placeholder : '' ,
        dropdownParent: parent.length>0?parent:$(elem).parent(),
      });
      if(props.changeHandler instanceof Function){
        $(elem).on('change', (event) => {
          props.changeHandler(event.target.value,props.field);
        });
      }
    }
    if(props.value){
      $(elem).trigger('change.select2');
    }
    return function cleanup() {
      if(props.select2){
        $(elem).select2('destroy').off();
      }
    }
  }, [props.options,props.placeholder,props.select2,selectRef,props.value]);

  return (
    <Fragment>
      <label className="titleShow">{props.label}</label>
      <div className={props.className}>
        <select ref={selectRef} className={'filter-selector-list ' + (showError && 'react-warning')} value={props.value}
                name={props.id} id={props.id}>
          <option/>
          {props.options.map(option =>
            <option selected={option.value === props.value} key={option.value} value={option.value}>{option.name}</option>)}

        </select>
        {showError && <span className="react-error p-relative">{messages.error_required}</span>}
      </div>
    </Fragment>
  );
}


export default Selector;
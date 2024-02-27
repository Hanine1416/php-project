import React, {Fragment, useContext, useState} from 'react';
import Checkbox from "../../../../components/Checkbox";
import {isMobile, messages} from "../../../../utils";
import * as Action from "../../store/actions";
import {Store} from "../../../../Store";

const MAX_FILTER_DISPLAY = 5;

function YearFilter(props) {

  let [maxShowYears, setMaxShowYears] = useState(MAX_FILTER_DISPLAY);
  const [active, setActive] = useState(props.isActive || props.selectedYears.length);
  const {state, dispatch} = useContext(Store);
  const filterChangeHandler = (e,field,value) => {
    props.onFilterChange(e, value, 'selectedYears')
      if(value == "soon") {
          let readingListActive = true;
          if(state.books.readingListState == true)  readingListActive = false;
          dispatch({type:Action.SET_READING_FILTER, readingListState:readingListActive})
      }
  };

  return (
    <div className={"small-12 columns expand-filter border-top p-0 " + (active && "active")}>
      <div className="catTitle p-16" onClick={(e) => setActive(!active)}>
          {messages.pub_year}
        <div className="expandCat">
          <span className="plus"/>
        </div>
      </div>
      <ul className="catName" id="year-filter-container">
        {Object.keys(props.years).reverse().map((key, index) => {
            let label = key;
            let attrDisabled = '';
            if(key == "soon" ) label = messages.coming_soon;
            if(key == "published" ) label = messages.published;
            if(props.years[key] == 0) attrDisabled = 'disabled';
            if (maxShowYears == null || maxShowYears > index)
              return <Checkbox key={index} className="input-field" attrDisabled={attrDisabled} checked={props.selectedYears.includes(key)} value={key}
                               changeHandler={filterChangeHandler}  type='selectedYears' id={"years_" + key}
                               label={<Fragment>{label} <small>({props.years[key]})</small></Fragment>}/>
          }
        )}
        {Object.keys(props.years).length > MAX_FILTER_DISPLAY && (maxShowYears ?
          <a className="show-link" onClick={e => setMaxShowYears(null)}>{messages.show_more}</a> :
          <a className="show-link" onClick={e => setMaxShowYears(MAX_FILTER_DISPLAY)}>{messages.show_less}</a>)}
      </ul>
    </div>
  );
}

export default YearFilter;

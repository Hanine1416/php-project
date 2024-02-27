import React, {Fragment, useState, useEffect} from 'react';
import Checkbox from "../../../../components/Checkbox";
import {messages} from "../../../../utils";

const MAX_FILTER_DISPLAY = 5;

function SubCategoryFilter(props) {
  let [maxShowCategories, setMaxShowCategories] = useState(MAX_FILTER_DISPLAY);
  return (
      <div className={"sub-categorie-item " + ((+props.categoryIndex === +props.categorySelector) ? "opened" : "")}>
          <div className="catTitle " onClick={(e)=> props.handleCategorySelectorOpen(props.categoryIndex)}><span className="sub-cat-name">{props.categoryName}</span>
            <div className="collapsed-sub-cat"><span className="plus-icon"></span></div>
        </div>
        <ul className={"catName"} id="category-filter-container">
        {Object.keys(props.subcategories).slice(0, (maxShowCategories || props.subcategories.length)).map(key => {
        return <Checkbox key={key} className="input-field" checked={props.selectedSubCategories.includes(key+'#'+props.mainCategory)}
                     value={key+'#'+props.mainCategory} changeHandler={props.onFilterChange}
                     label={<Fragment>{key} <small>({props.subcategories[key]})</small></Fragment>}
                     id={props.mainCategory.replace(/ /, '_')+"_"+"sub_category_" + key.replace(/ /, '_')}
        />
        })}
        {Object.keys(props.subcategories).length > MAX_FILTER_DISPLAY && (maxShowCategories ?
        <a className="show-link" onClick={e => setMaxShowCategories(null)}>{messages.show_more}</a> :
        <a className="show-link" onClick={e => setMaxShowCategories(MAX_FILTER_DISPLAY)}>{messages.show_less}</a>)}
        </ul>
      </div>
  );
}

export default SubCategoryFilter;
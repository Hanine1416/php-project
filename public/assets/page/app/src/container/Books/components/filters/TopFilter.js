import React, {useContext} from 'react';
import PaginationIndicator from "../paginator/PaginationIndicator";
import FilterViewer from "./FilterViewer";
import {Store} from "../../../../Store";
import * as Action from "../../store/actions";
import Selector from "../../../../components/Selector";
import SortFilter from "./SortFilter";
import {isMobile, messages} from "../../../../utils";
import RetinaImg from "../../../../components/RetinaImg";

const TopFilter = (props) => {
  const {state, dispatch} = useContext(Store);
  const {pagination} = state.books;

  const options = [
    {value: 12, name: "12"},
    {value: 24, name: "24"},
    {value: 36, name: "36"},
    {value: 48, name: "48"}
  ];

  const changePageSize = (newSize) => {
    sessionStorage.setItem('pageSize',newSize);
    dispatch({type: Action.SET_PAGE_SIZE, newPageSize: parseInt(newSize)});
  };

  return (
    <div className="layoutControls nexusSans">
        { !isMobile() &&
        <FilterViewer className="row filters-info"/>
        }
      <div className="row top-pagination-menu">
        <div className="small-7 large-6 medium-5 columns pageResult p-0">
          <PaginationIndicator/>
          <span className="pipe-vertical">|</span>
          <div className="sortBy showNum">
            <Selector select2={true} className="filter-select page-size sort-filter sort-by" labelClassName="titleShow"
                      changeHandler={changePageSize} value={pagination.pageSize}
                      options={options} id="page-size" name={messages.filter_show}/>
          </div>
        </div>
        <div className="small-5 columns filter-block p-0">
          <div className="filter-number">
            {messages.filter_title}
            <a className="filter-selector" onClick={props.handleMobileFilter}>
              <RetinaImg alt="filter icon" className="retina-reload" src={'/assets/img/books-page/sliders@1x.png'} width="22"
                   height="18"/>
            </a>
          </div>
        </div>
        <SortFilter forMobile={false}/>
      </div>
    </div>
  );
};

export default TopFilter;
import React, {Fragment, useContext, useEffect} from "react";
import * as Action from "../../store/actions";
import {Store} from "../../../../Store";
import CategoryFilter from "./CategoryFilter";
import YearFilter from "./YearFilter";
import FilterViewer from "./FilterViewer";
import OtherFilter from "./OtherFilter";
import SortFilter from "./SortFilter";
import {messages} from "../../../../utils";

function Filters(props) {
  const {dispatch, state} = useContext(Store);
  const {categories, years, userCopies, filters, isMobile, filteredBooks, isLoading, search, books} = state.books;
  let selectedYears = filters.selectedYears;
  let selectedSubCategories = filters.selectedSubCategories;
  let selectedUserCopies = filters.selectedUserCopies;

  const checkFilter = (isChecked, elem, type) => {
    /** Required to update changed array reference */
    filters[type] = [...filters[type]];
    /** If checked add element to array */
    if (isChecked) {
      filters[type].push(elem);
    } else {
      const index = filters[type].indexOf(elem);
      filters[type].splice(index, 1);
    }
    dispatch({type: Action.SET_FILTER, filters: filters});
  };

  useEffect(() => {
    let body = document.getElementsByTagName('body')[0];
    if (props.filterHandler.isOpen) {
      body.classList.add('modal-open', 'scrollable');
    } else {
      body.classList.remove('modal-open', 'scrollable');
    }
  }, [props.filterHandler.isOpen]);

  return (
    <div
      className={"small-3 large-3 medium-3 columns filterControl p-0 " + (props.filterHandler.isOpen && 'filter-mobile')}>
      {(!isMobile && search && !isLoading) && <h2>{books.length} {messages.search_result_for} ‘{search}‘</h2>}
      <div className="close" onClick={() => props.filterHandler.setOpen(false)}/>
      <div className="row filter-container">

        <div className="small-12 columns filter-title p-16">{messages.filter_title}</div>
        <OtherFilter type={userCopies} selectedUserCopies={selectedUserCopies} onFilterChange={checkFilter}/>
        {categories.SH && <CategoryFilter name={messages.health_sciences} isActive={true} categories={categories.SH}
                                          onFilterChange={checkFilter} selectedSubCategories={selectedSubCategories}/>}
        {categories.ST &&
        <CategoryFilter name={messages.science_tech} isActive={true} categories={categories.ST}
                        onFilterChange={checkFilter} selectedSubCategories={selectedSubCategories}/>}
        {Object.keys(years).length > 0 &&
        <YearFilter years={years} selectedYears={selectedYears} onFilterChange={checkFilter}/>}
        {isMobile &&
        <Fragment>
          <SortFilter forMobile={true}/>
          <FilterViewer className="small-12 columns border-top p-16 filters-info"/>
          <div className="small-12 columns text-center">
            <a id="mobile-submit-filter" onClick={() => props.filterHandler.setOpen(false)}
               className={"btn btn-blue " + (isLoading && 'loading')}>
              {messages.show_result}<span id="filter-results-nbr"> {!isLoading && `(${filteredBooks.length})`}</span>
            </a>
          </div>
        </Fragment>
        }
      </div>
    </div>
  );
}

export default Filters;

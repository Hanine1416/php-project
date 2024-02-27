import React, {useContext} from 'react';
import PropTypes from "prop-types";
import {range} from 'lodash';
import * as Action from "../../store/actions";
import {Store} from "../../../../Store";
import {historyPush, messages} from "../../../../utils";

const Pagination = props => {
  const {dispatch} = useContext(Store);
  /** the number of first and last pages to be displayed  */
  const extremePagesLimit = 1;
  /** the number of pages that are displayed around the active page */
  const nearbyPagesLimit = 2;

  /** Click change page */
  function setPage(num) {
    if (num > 0 && num <= props.totalPages) {
      dispatch({type: Action.SET_PAGE, newPage: num})
    }
  }

  return (
    <div className="small-12 large-5 medium-6 columns paginationFilter p-0">
      <span>
        <a onClick={e => setPage(props.selectedPage - 1)}
           className={props.selectedPage < 2 ? 'disabled-pagination' : ''}>
          {messages.pagination_prev}
        </a>
      </span>
      {props.selectedPage > 1 ? [
        range(1, extremePagesLimit + 1).map(i => {
          if (i < props.selectedPage - nearbyPagesLimit)
            return <span><a onClick={e => setPage(i)}>{i}</a></span>
        }),
       /* extremePagesLimit + 1 < props.selectedPage - nearbyPagesLimit ? <span className="sep-dots">...</span> : null,*/
        range(props.selectedPage - nearbyPagesLimit, props.selectedPage).map(i => {
          if (i > 0)
            return <span><a onClick={e => setPage(i)}>{i}</a></span>
        })
      ] : null}
      <span className="current">
        <a className="active">{props.selectedPage}</a>
      </span>
      {props.selectedPage < props.totalPages ? [
        range(props.selectedPage + 1, props.selectedPage + nearbyPagesLimit + 1).map(i => {
          if (i <= props.totalPages)
            return <span><a onClick={e => setPage(i)}>{i}</a></span>
        }),
        props.totalPages - extremePagesLimit > props.selectedPage + nearbyPagesLimit ?
          <span className="sep-dots">...</span> : null,
        range(props.totalPages - extremePagesLimit, props.totalPages + 1).map(i => {
          if (i > props.selectedPage + nearbyPagesLimit)
            return <span><a onClick={e => setPage(i)}>{i}</a></span>
        })
      ] : null}
      {props.totalPages > 1 ?
        <span>
          <a onClick={e => setPage(props.selectedPage + 1)}
             className={props.selectedPage === props.totalPages ? 'disabled-pagination' : ''}>
            {messages.pagination_next}
          </a>
        </span> : null}
    </div>
  );
};
Pagination.propTypes = {
  selectedPage: PropTypes.number,
  totalPages: PropTypes.number
};
export default Pagination;
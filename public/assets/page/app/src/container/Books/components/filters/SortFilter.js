import React, {Fragment, useContext} from 'react';
import Selector from "../../../../components/Selector";
import * as Action from "../../store/actions";
import {Store} from "../../../../Store";
import Radio from "../../../../components/Radio";
import {messages} from "../../../../utils";

function SortFilter(props) {

  const {dispatch, state} = useContext(Store);
  const {order} = state.books;

  const orderByOptions = [
    {value: "author-asc", name: messages.sort_by_author_a_z},
    {value: "author-desc", name: messages.sort_by_author_z_a},
    {value: "title-asc", name: messages.sort_by_title_a_z},
    {value: "title-desc", name: messages.sort_by_title_z_a},
    {value: "date-desc", name: messages.sort_by_date_new},
    {value: "date-asc", name: messages.sort_by_date_old},
    {value: "popularity-desc", name: messages.sort_by_popularity},
  ];

  const changePageOrder = (newOrder) => {
    dispatch({type: Action.SET_ORDER_BY, newOrderBy: newOrder});
  };

  return (
    <Fragment>
      {
        props.forMobile ?
          <div className="small-12 columns sort-by mobile-sort border-top p-16">
            <div className="catTitle">{messages.sort_by_title}</div>
            {orderByOptions.map((option, index) =>
              <Radio key={index} label={option.name} value={option.value} checked={order===option.value}
                     id={`filter-order_${option.value}`} name="filter-order" changeHandler={changePageOrder}/>)}
          </div>
          :
          <div className="small-3 large-6 medium-7 columns sortBy p-0">
            <Selector select2={true} className="filter-select sort-filter sort-by" labelClassName="titleShow"
                      changeHandler={changePageOrder} value={order}  selected={order}
                      options={orderByOptions} id="filter-order" label={messages.sort_by_title}/>
          </div>
      }
    </Fragment>
  );
}

export default SortFilter;

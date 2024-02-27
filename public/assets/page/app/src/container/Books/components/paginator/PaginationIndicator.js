import React, {useContext} from 'react';
import {Store} from "../../../../Store";
import {messages} from "../../../../utils";

const PaginationIndicator = (props) => {
  let state = useContext(Store).state;
  const {pagination,filteredBooks} = state.books;

  const fromData = (pagination.selectedPage-1) * pagination.pageSize+1;
  let toData = fromData+pagination.pageSize-1;
  if(toData>filteredBooks.length)
    toData = filteredBooks.length;
  return (
      <div className={"pagination-info "+props.className }>
        {messages.showing_page.replace('#from#',fromData).replace('#length#',toData).replace('#total#',filteredBooks.length)}
      </div>
  );
};
export default PaginationIndicator;
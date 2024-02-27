import BookItem from "./BookItem";
import React from "react";

function BooksContainer(props) {
  return (
      <div id="book-result" className="mainResults" style={{minHeight:1620}}>
        <div className={"row"}>
          {
            props.books.map((book,index) => <BookItem key={index} book={book}/>)
          }
        </div>
      </div>
  )
}

export default BooksContainer
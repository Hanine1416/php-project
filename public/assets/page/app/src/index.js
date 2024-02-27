import React from 'react';
import ReactDOM from 'react-dom';
import { StateProvider } from './Store.js';
import BooksPage from "./container/Books/BooksPage";

let booksContainer = document.getElementById('books-container');
if(booksContainer){
  ReactDOM.render(<StateProvider><BooksPage/></StateProvider>, booksContainer);
}

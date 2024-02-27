import * as Action from "./actions";
import {historyPush, isMobile, moment, queryParamsReader} from "../../../utils";

let params = queryParamsReader();
/** Init state */
export const booksState = {
  books: [],
  categories: [],
  years: [],
  filteredBooks: [],
  userCopies: [],
  userBooks: [],
  filters: {
    selectedCategories: params['cat'] ?? [],
    selectedSubCategories: params['sub'] ?? [],
    selectedYears: params['year'] ?? [],
    selectedUserCopies: params['userCopies'] ?? [],
  },
  pagination: {
    selectedPage: parseInt(params['page'] ?? 1),
    totalPages: 0,
    pageSize: parseInt(sessionStorage.getItem('pageSize') ?? 12),
    bookToShow: []
  },
  type: window.catalogue,
  isLoading: true,
  loadingError: false,
  readingListState: true,
  search: params['s'] ?? '',
  searchBy: params['sb'] ?? '',
  order: params['order'] ?? 'date-desc',
  isMobile: isMobile()
};
/** Fix filters array */
Object.keys(booksState.filters).forEach(key => {
  if (!(booksState.filters[key] instanceof Array)) {
    booksState.filters[key] = [booksState.filters[key]]
  }
});
/** Push state */
historyPush('', '', booksState);
export const booksReducer = (state, action) => {
  switch (action.type) {
    case Action.START_LOADING_DATA: {
      return {
        ...state,
        loadingError: false,
        isLoading: true
      };
    }
    case Action.DATA_LOADED: {
      let filtered = filterBookToShow(action.data.books, state.filters, action.data.userBooks);
      let totalPages = Math.ceil(filtered.length / state.pagination.pageSize);
      let currentPage = state.pagination.selectedPage > totalPages ? totalPages : state.pagination.selectedPage;
      return {
        ...state,
        books: action.data.books,
        categories: action.data.filters.categories,
        years: action.data.filters.years,
        userCopies: action.data.filters.userCopies,
        userBooks: action.data.userBooks,
        filteredBooks: filtered,
        pagination: {
          selectedPage: currentPage,
          totalPages: Math.ceil(filtered.length / state.pagination.pageSize),
          pageSize: state.pagination.pageSize,
          bookToShow: filtered.slice((currentPage - 1) * state.pagination.pageSize,
            currentPage * state.pagination.pageSize)
        },
        isLoading: false
      };
    }
    case Action.SET_PAGE: {
      historyPush('page', action.newPage, {...state});
      return {
        ...state,
        pagination: {
          ...state.pagination,
          selectedPage: action.newPage,
          bookToShow: state.filteredBooks.slice((action.newPage - 1) * state.pagination.pageSize,
            action.newPage * state.pagination.pageSize)
        }
      };
    }
    case Action.SET_ORDER_BY: {
      let books = sortBooks(state.books, action.newOrderBy);
      let reorderedBookToShow = books.slice((state.pagination.selectedPage - 1) * state.pagination.pageSize,
        state.pagination.selectedPage * state.pagination.pageSize);
      historyPush('order', action.newOrderBy, state);
      return {
        ...state,
        books: books,
        order: action.newOrderBy,
        pagination: {
          ...state.pagination,
          bookToShow: reorderedBookToShow
        }
      };
    }
    case Action.SET_FILTER: {
      historyPush('filters', {
        'cat': action.filters.selectedCategories,
        'sub': action.filters.selectedSubCategories,
        'year': action.filters.selectedYears,
        'userCopies': action.filters.userCopies,
      }, state);
      let filteredBooks = filterBookToShow(state.books, action.filters, state.userBooks);
      return {
        ...state,
        filters: {...action.filters},
        filteredBooks: filteredBooks,
        pagination: {
          ...state.pagination,
          selectedPage: 1,
          totalPages: Math.ceil(filteredBooks.length / state.pagination.pageSize),
          bookToShow: filteredBooks.slice(0, state.pagination.pageSize)
        }
      };
    }
    case Action.SET_PAGE_SIZE: {
      let totalPages = Math.ceil(state.filteredBooks.length / action.newPageSize);
      let selectedPage = state.pagination.selectedPage > totalPages ? totalPages : state.pagination.selectedPage;
      let bookToShow = filterBookToShow(state.filteredBooks, state.filters, state.userBooks).slice((selectedPage - 1) * action.newPageSize, selectedPage * action.newPageSize);
      return {
        ...state,
        pagination: {
          ...state.pagination,
          bookToShow: bookToShow,
          pageSize: action.newPageSize,
          totalPages: totalPages,
          selectedPage: selectedPage
        }
      };
    }
    case Action.LOADING_DATA_FAIL:
      return {
        ...state,
        isLoading: false,
        loadingError: true
      };
    case Action.WINDOW_BACK_STATE: {
      if (action.state) {
        return {
          ...state,
          ...action.state,
          filters: {
            ...state.filters,
            ...(action.state.filters || {})
          },
          pagination: {
            ...state.pagination,
            selectedPage: (action.state.pagination.selectedPage || state.pagination.selectedPage)
          }
        };
      }
    }
    case Action.SET_READING_FILTER: {
      return {
        ...state,
        loadingError: false,
        readingListState: action.readingListState
      };
    }
    default: return state;
  }
};

const filterBookToShow = (books, filters, userBooks) => {

  let today = new Date();
  let currentDate = today.getFullYear() + '-' + (today.getMonth() + 1) + '-' + today.getDate();
  return books.filter(book => {
    let showBook = true;
    let readingList = false;
    let requested   = false;
    if(Object.keys(userBooks).length > 0) {
        if(userBooks['Approved'].indexOf(book.isbn) !== -1){
            readingList = true;
            requested   = true;
        }
        if(userBooks['Adopted'].indexOf(book.isbn) !== -1){
            readingList = true;
            requested   = true;
        }
        if(userBooks['Others'].indexOf(book.isbn) !==-1){
            requested   = true;
        }
    }

    let subcategoriesFilterLength = filters.selectedSubCategories.length;
    if(filters.selectedYears.filter(item => item !== 'soon' && item !=='published').length>0 && !filters.selectedYears.includes(book.date.substr(0, 4))){
      showBook = false;
    }
    if (subcategoriesFilterLength && filters.selectedSubCategories.filter(e => book.subcategories.find(cat => cat.name.trim() === e.split('#')[0] && cat.topDiscipline.trim() === e.split('#')[1])).length === 0){
      showBook = false;
    }
    if(filters.selectedUserCopies.includes("hasDigital") && !book.digital){
      showBook = false;
    }
    if(filters.selectedUserCopies.includes("HasStudentResources") && !book.StudentRessource){
      showBook = false;
    }
    if(filters.selectedUserCopies.includes("HasProfessorResources") && !book.InstructorRessource){
      showBook = false;
    }
    //only isNew selected
    if(filters.selectedUserCopies.includes("isNew") && !filters.selectedUserCopies.includes("isMostPopular") && !filters.selectedUserCopies.includes("isTopSeller") && book.tag !== "isNew"){
      showBook = false;
    }
    if(filters.selectedUserCopies.includes("isMostPopular") && !filters.selectedUserCopies.includes("isNew") && !filters.selectedUserCopies.includes("isTopSeller") && book.tag !== "isMostPopular"){
      showBook = false;
    }
    if(filters.selectedUserCopies.includes("isTopSeller") && !filters.selectedUserCopies.includes("isNew") && !filters.selectedUserCopies.includes("isMostPopular") && book.tag !== 'isTopSeller'){
      showBook = false;
    }
    if(filters.selectedUserCopies.includes("isNew") && filters.selectedUserCopies.includes("isTopSeller") && book.tag !== "isNew" && book.tag !== "isTopSeller"){
      showBook = false;
    }
    if(filters.selectedUserCopies.includes("isNew") && filters.selectedUserCopies.includes("isMostPopular") && book.tag !== "isNew" && book.tag !== "isMostPopular"){
      showBook = false;
    }
    if(filters.selectedUserCopies.includes("isTopSeller") && filters.selectedUserCopies.includes("isMostPopular") && book.tag !== "isTopSeller" && book.tag !== "isMostPopular"){
      showBook = false;
    }
    if(filters.selectedUserCopies.includes("isNew")  && filters.selectedUserCopies.includes("isTopSeller") && filters.selectedUserCopies.includes("isMostPopular") && book.tag !== "isNew" && book.tag !== "isTopSeller" && book.tag !== "isMostPopular"){
      showBook = false;
    }

    if(filters.selectedUserCopies.includes("readingList") && !readingList ){
      showBook = false;
    }
    if(filters.selectedUserCopies.includes("requested") && !readingList && !requested){
      showBook = false;
    }
    if(!filters.selectedYears.includes("published") && filters.selectedYears.includes("soon") && moment(book.date) < moment()){
      showBook = false;
    }
    if(!filters.selectedYears.includes("soon") && filters.selectedYears.includes("published") && moment(book.date) >= moment()){
      showBook = false;
    }

    /** Filter according to year filter */
    return showBook;
  })
};

/** Sort results by selected filter order */
const sortBooks = (books, order) => {
  books.sort((book1, book2) => {
    let asc = order.includes('-asc') ? 1 : -1;
    switch (order) {
      case 'date-asc':
      case'date-desc':
        return moment(book1.date) > moment(book2.date) ? asc : -1 * asc;
      case 'author-asc':
      case 'author-desc':
        return book1.author.toUpperCase() > book2.author.toUpperCase() ? asc : -1 * asc;
      case 'title-asc':
      case 'title-desc':
        return book1.title.toUpperCase() > book2.title.toUpperCase() ? asc : -1 * asc;
      case 'popularity-desc':
        return book1.requests > book2.requests ? -1 : (book1.requests < book2.requests ? 1 : (moment(book1.date) > moment(book2.date) ? -1 : 1));
      default:
        return moment(book1.date) > moment(book2.date) ? -1 : 1;
    }
  });
  return books;
};
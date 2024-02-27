import React, {Fragment, useCallback, useContext, useEffect, useState} from 'react';
import Filters from "./components/filters/Filters";
import BooksContainer from "./components/BooksContainer";
import Pagination from "./components/paginator/Pagination";
import PaginationIndicator from "./components/paginator/PaginationIndicator";
import TopFilter from "./components/filters/TopFilter";
import {Store} from "../../Store";
import Loader from "../../components/Loader";
import * as Action from "./store/actions";
import LoadingFail from "./components/LoadingFail";
import {baseUrl, messages} from "../../utils";
import useFetch from "../../hooks/useFetch";
import {isMobile} from "../../utils";

let abortController = new AbortController();

function BooksPage() {

  const {state, dispatch} = useContext(Store);
  const [mobileFilterOpen, setMobileFilterOpen] = useState(false);
  const [dynamicClass, setDynamicClass] = useState("");
  const {pagination, isLoading, filters, order, loadingError, search, searchBy, type} = state.books;
  const myFetch = useFetch();

  useEffect(() => {
    window.addEventListener('scroll', listenToScroll)

    return () => {
      window.removeEventListener('scroll', listenToScroll)
    }
  }, [ ])
/*  function to detect scroll*/
  const listenToScroll = () => {
    const winScroll =
        document.body.scrollTop || document.documentElement.scrollTop

    const height =
        document.documentElement.scrollHeight -
        document.documentElement.clientHeight

    const scrolled = winScroll / height
/*     add sticky class to header*/
    if (dynamicClass !=="sticky" && !isMobile && scrolled > 0.11117021276595744) setDynamicClass("sticky")
    if (dynamicClass !=="sticky" && isMobile && scrolled > 0.06495098039215687) setDynamicClass("sticky")
    else setDynamicClass("")
  }
  useEffect(() => {
    function historySetState(e) {
      dispatch({type: Action.WINDOW_BACK_STATE, state: e.state});
    }

    window.onpopstate = historySetState;
    return function cleanUp() {
      window.removeEventListener('onpopstate', historySetState);
    }
  });

  const fetchBookData = async (selectedCategories) => {
    /** Cancel previous request if not done */
    abortController.abort();
    abortController = new AbortController();
    dispatch({type: Action.START_LOADING_DATA});
    try {
      /** Create query param */
      let data = await myFetch(`/books/${type}`, 'POST', null, {
        query: {cat: selectedCategories, s: search, sb: searchBy, order: order},
        signal: abortController.signal
      }, true);
      dispatch({type: Action.DATA_LOADED, data: data});
    } catch (e) {
      if (e.name !== "AbortError") dispatch({type: Action.LOADING_DATA_FAIL})
    }
  };

    useEffect(() => {
        let filteredBooks = [];
        if(state.books.filteredBooks.length > 0) {
          state.books.filteredBooks.filter(book => {
            const oneBook = {
              isbn: book.isbn
            };
            filteredBooks.push(oneBook);
          });
            const postBody = {
                booksToShow: filteredBooks
            };
            const requestMetadata = {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(postBody)
            };
          fetch("/update-filtered-book-session", requestMetadata);
        }
    });
  useEffect((e) => {
    fetchBookData(filters.selectedCategories)
  }, [filters.selectedCategories]);


  const editSearch = useCallback((search,searchBy) => {
    document.getElementById('searchActive').click();
    document.getElementById('search-input').value=search;
    document.querySelector('input[type=radio][name="type"][value="'+searchBy+'"]').checked=true;
  },[]);

  return (
      <div className="Books">
        {isLoading ? <Loader/> :
            <Fragment>
              {(!loadingError && pagination.bookToShow.length > 0) &&
              <div className={"layoutControls nexusSans bottom-pagination "+dynamicClass} >
                <div className="pagination-header row">
                  <div className="maincontainer ">
                    <PaginationIndicator className="columns large-6 p-0"/>
                    {pagination.totalPages > 1 &&
                    <Pagination selectedPage={pagination.selectedPage} totalPages={pagination.totalPages}/>}
                  </div>
                </div>
              </div>
              }
              <div className="maincontainer">
              <Filters filterHandler={{isOpen: mobileFilterOpen, setOpen: setMobileFilterOpen}}/>

              <div className="small-12 large-8 medium-8 columns resultsArea p-0">
                {(!loadingError && pagination.bookToShow.length>0) && <TopFilter handleMobileFilter={setMobileFilterOpen}/>}
                {!loadingError && (pagination.bookToShow.length > 0 ?
                    <BooksContainer books={pagination.bookToShow}/> :
                    <Fragment>
                      <p>{messages.no_search_result} ‘{search}‘</p>
                      <ul>
                        <li className="editSearch"><a onClick={(e)=>editSearch(search,searchBy)}>{messages.edit_search}
                          <img className="retina-reload" data-toggle="tooltip" data-placement="bottom" title="Edit"
                               src={"/assets/img/books-page/Icon_edit_small@1x.png"} alt="editIcon" width="13" height="15"/></a></li>
                      </ul>
                      <div>{messages.search_go_to} <a href={baseUrl + '/'}>{messages.home_page}</a></div>
                    </Fragment>)}
                {/*    remove pagination from the bottom of the page */}

  {/*              {(!loadingError && pagination.bookToShow.length>0) && <div className="layoutControls nexusSans border-top bottom-pagination">
                  <div className="row">
                    <PaginationIndicator className="columns large-6 p-0"/>
                    {pagination.totalPages > 1 &&
                    <Pagination selectedPage={pagination.selectedPage} totalPages={pagination.totalPages}/>}
                  </div>
                </div>
                }*/}
              </div>
              </div>
            </Fragment>}
        {loadingError && <LoadingFail reloadData={() => fetchBookData(filters.selectedCategories)}/>}
      </div>

  );
}

export default BooksPage;
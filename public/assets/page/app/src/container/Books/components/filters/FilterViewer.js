import React, {useContext} from 'react';
import {Store} from "../../../../Store";
import * as Action from "../../store/actions";
import {messages} from "../../../../utils";

const FilterViewer = (props) => {
    const {state, dispatch} = useContext(Store);
    const filters = state.books.filters;

    function clearFilters() {

        if (document.getElementById('selected-category'))
            document.getElementById('selected-category').style.display = 'none';
        if (document.getElementById('all-books'))
            document.getElementById('all-books').style.display = 'inline-block';

        dispatch({
            type: Action.SET_FILTER, filters: {
                selectedCategories: [],
                selectedSubCategories: [],
                selectedYears: [],
                selectedUserCopies: []
            }
        });
        dispatch({type: Action.SET_READING_FILTER, readingListState: true})
    }

    function removeFilter(from, elem) {
        if (from === 'selectedCategories') {
            var navbreadcrumb = document.getElementById('selected-category');
            elem = elem.replace('undefined:', '');
            var nbcb = '';
            if (navbreadcrumb)
                 nbcb = navbreadcrumb.innerHTML.trim();
            if (elem.trim() === nbcb) {
                if (document.getElementById('selected-category'))
                    document.getElementById('selected-category').style.display = 'none';
                if (document.getElementById('all-books'))
                    document.getElementById('all-books').style.display = 'inline-block';
            }
        }
        const index = filters[from].indexOf(elem);
        filters[from] = [...filters[from]];
        filters[from].splice(index, 1);
        dispatch({type: Action.SET_FILTER, filters: {...filters}})
    }

    let clearFilterButton = null;
    if ([...filters.selectedCategories, ...filters.selectedSubCategories, ...filters.selectedYears, ...filters.selectedUserCopies].length >= 2) {
        clearFilterButton = (<div className="filter-item clear">
            <p>{messages.clear_filters}</p>
            <a onClick={clearFilters} className="close-filter clear-all"/>
        </div>)
    }
    return (
        <div className={props.className}>
            {clearFilterButton}
            {filters.selectedCategories.map((elem, index) => <div key={index} className="filter-item">
                <p>{(elem.split('#')[1]) ? elem.split('#')[1] + ': ' + elem.split('#')[0] : elem.split('#')[0]}</p>
                <a onClick={e => removeFilter('selectedCategories', elem.split('#')[0] + '#' + elem.split('#')[1])}
                   className="close-filter"/></div>)}
            {filters.selectedSubCategories.map((elem, index) => <div key={index} className="filter-item">
                <p>{elem.split('#')[1] + ': ' + elem.split('#')[0]}</p>
                <a onClick={e => removeFilter('selectedSubCategories', elem.split('#')[0] + '#' + elem.split('#')[1])}
                   className="close-filter"/></div>)}
            {filters.selectedYears.map((elem, index) => {
                let label = elem;
                if (elem == "soon") label = messages.coming_soon;
                if (elem == "published") label = messages.published;
                return <div key={index} className="filter-item"><p>{label}</p><a
                    onClick={e => removeFilter('selectedYears', elem)} className="close-filter"/></div>
            })}
            {filters.selectedUserCopies.map((elem, index) => {
                let label = elem;
                if (elem == "requested") label = messages.requested;
                if (elem == "readingList") label = messages.readingList;
                if (elem == "hasDigital") label = messages.digitalCopy;
                if (elem == "isNew") label = messages.what_new;
                if (elem == "isMostPopular") label = messages.most_popular;
                if (elem == "isTopSeller") label = messages.top_seller;
                if (elem == "HasStudentResources") label = messages.student_ressource;
                if (elem == "HasProfessorResources") label = messages.professor_ressource;
                return <div key={index} className="filter-item"><p>{label}</p><a
                    onClick={e => removeFilter('selectedUserCopies', elem)} className="close-filter"/></div>
            })}
        </div>
    );
};

export default FilterViewer;
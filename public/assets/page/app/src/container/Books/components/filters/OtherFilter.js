import React, {Fragment, useContext, useState} from 'react';
import Checkbox from "../../../../components/Checkbox";
import {isMobile, messages} from "../../../../utils";
import {Store} from "../../../../Store";

const MAX_FILTER_DISPLAY = 6;

function OtherFilter(props) {

    let [maxShowFilter] = useState(MAX_FILTER_DISPLAY);
    const {state} = useContext(Store);
    let readingListActive = state.books.readingListState;
    const filterChangeHandler = (e,field,value) => {
        props.onFilterChange(e, value, 'selectedUserCopies')
    };

    return (
        <div className={"small-12 columns other-categ-block "}>
            <ul className="catName">
                {Object.keys(props.type).reverse().map((key, index) => {
                        let label = key;
                        let attrDisabled = '';
                        if(key === "requested" ) label = messages.requested;
                        if(key === "readingList" ) label = messages.readingList;
                        if(key === "hasDigital" ) label = messages.digitalCopy;
                        if(key === "isNew" ) label = messages.what_new;
                        if(key === "isTopSeller" ) label = messages.top_seller;
                        if(key === "isMostPopular" ) label = messages.most_popular;
                        if(key === "HasStudentResources" ) label = messages.student_ressource;
                        if(key === "HasProfessorResources" ) label = messages.professor_ressource;
                        if(props.type[key] === 0) attrDisabled = 'disabled';
                        if(key === "readingList" && !readingListActive) attrDisabled = 'disabled';

                        if (maxShowFilter == null || maxShowFilter > index)
                            return <Checkbox key={index} className='input-field' attrDisabled={attrDisabled} checked={props.selectedUserCopies.includes(key)} value={key}
                                             changeHandler={filterChangeHandler}  type='selectedUserCopies' id={"filter_" + key}
                                             label={<Fragment>{label} <small>({props.type[key]})</small></Fragment>}/>
                    }
                )}
            </ul>
        </div>
    );
}

export default OtherFilter;

import React, {Fragment, useState, useEffect} from 'react';
import Checkbox from "../../../../components/Checkbox";
import SubCategoryFilter from "./SubCategoryFilter";
import {isMobile} from "../../../../utils";

function CategoryFilter(props) {
    const [active, setActive] = useState(props.isActive);
    const [categorySelector,setCategorySelector] = useState(localStorage.getItem("categorySelectorState_"+window.location.pathname.split("/")[2]) || -1)

    const filterChangeHandler = (e, field, value) => {
        props.onFilterChange(e, value, 'selectedSubCategories')
    };

    const handleCategorySelectorOpen = (index) =>setCategorySelector((prev)=>{
        if(prev===index){
            return -1
        }
        return index
    })

    let subcategories = [];
    let slectedItems = [];
    Object.keys(props.categories).forEach((categoryName,categoryIndex) => {
        subcategories.push(<div key={categoryName}>
            <SubCategoryFilter handleCategorySelectorOpen={handleCategorySelectorOpen} categorySelector={categorySelector} categoryIndex={categoryIndex} categoryName={categoryName} subcategories={props.categories[categoryName].subcategories}
                               selectedSubCategories={props.selectedSubCategories}
                               onFilterChange={filterChangeHandler} mainCategory={categoryName}/>
        </div>);
        slectedItems = [...slectedItems,...Object.keys(props.categories[categoryName].subcategories).filter(name => props.selectedSubCategories.includes(name))]
    })
     useEffect(() => {
         if(['hs','st'].includes(window.location.pathname.split("/")[2])){ categorySelector !== -1 && localStorage.setItem("categorySelectorState_"+window.location.pathname.split("/")[2], categorySelector)}
    }, [categorySelector]);

    return (
        <div className={"small-12 columns expand-filter border-top p-0 " + (active && "active")}>
            <div className="catTitle p-16" onClick={(e) => setActive(!active)}>
                {props.name} ({Object.keys(props.categories).reduce((total, elem) => parseInt(props.categories[elem].total) + total, 0)})
                <div className="expandCat">
                    <span className="plus"/>
                </div>
                {isMobile() &&
                <div className="selected-filter">
                    <p>{[...new Set(slectedItems)].join(",")}</p>
                </div>}
            </div>
            <ul className="catName p-0">
                {subcategories}
            </ul>
        </div>
    );
}

export default CategoryFilter;
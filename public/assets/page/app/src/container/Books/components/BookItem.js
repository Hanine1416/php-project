import React from 'react';
import {baseUrl,imageCdn,moment,messages} from "../../../utils";

export const BooksItems = {
    lang : window.languages.lang,
    sessionlang : window.languages.sessionlang
};

function BookItem (props) {

    function showTitle(title) {
    return title.length > 80 ? title.slice(0, 80) + '...' : title
  }
  function imgError(e) {
      var lng = BooksItems.lang !== undefined ? BooksItems.lang : BooksItems.sessionlang;
      e.target.onerror = "";
      const allcat = ['fr','es','de','en'];
      if(allcat.includes(lng) )
      {
          e.target.src = '' + document.location.origin + '/assets/img/no_cover_'+lng+'.png'
      }
      else{
          e.target.src = '' + document.location.origin + '/assets/img/no_cover_en.png';
      }
    return true;
  }
  let publicationDate ;
  if(props.book.date)
    publicationDate = <p className='prodDate'>{moment(props.book.date,'YYYY-MM-DD').format('DD MMM YYYY').replace('.','')}</p>;
    const stars = [];
    for (let i = 1; i <= 5; i++) {
        if (i <= props.book.rating) {
            stars.push(
                <button key={i} className="star active">
                  <span className="stararea">
                    <i className="star_icon"></i>
                  </span>
                </button>
            );
        } else if (i - props.book.rating >= 0.2 && i - props.book.rating <= 0.8) {
            stars.push(
                <button key={i} className="star half_star">
                  <span className="stararea">
                    <i className="star_icon"></i>
                  </span>
                </button>
            );
        } else {
            stars.push(
                <button key={i} className="star">
                  <span className="stararea">
                    <i className="star_icon"></i>
                  </span>
                </button>
            );
        }
    }
  return (
    <div className="small-6 medium-4 large-3 columns prodColumn">
        <div className="singleProduct clearfix">
            {props.book.tag == 'updatedEdition' &&
                <div className="tag_book">
                    <div className="red">{messages.new_edition}</div>
                </div>
            }
            {props.book.tag == 'isNew' &&
                <div className="tag_book">
                    <div className="yellow">{messages.new}</div>
                </div>
            }
            {props.book.tag == 'isMostPopular' &&
                <div className="tag_book">
                    <div className="green">{messages.most_popular}</div>
                </div>
            }
            {props.book.tag == 'isTopSeller' &&
                <div className="tag_book">
                    <div className="purple">{messages.top_seller}</div>
                </div>
            }
            {props.book.tag.length === 0 &&
                <div className="tag_book">
                    <div className="no_tag"></div>
                </div>
            }
          <div className="productImg">
            <a className="imgBlock"
               href={baseUrl+"/book/details/"+props.book.isbn}>
              <img src={ imageCdn +'/'+  props.book.isbn +'.jpg'}
                   onError={e => imgError(e)} alt={ props.book.title }/>
            </a>
          </div>
          <div className="prodTitle nexusSerif">
            <h2><a className="titleBlock "
                   href={baseUrl+"/book/details/"+props.book.isbn}>{showTitle(props.book.title)}</a>
                <span className="tooltip_booktitle">{props.book.title}</span>
            </h2>
          </div>
          <div className="prodAuthor">
            <p>{props.book.author}</p>
            {publicationDate}
          </div>
            <div className="rating_book">
                <div className="stars_rating">
                    <div className="average_reviews">
                       <span className="review-container">{stars}</span>
                        <a className="total_reviews" href={baseUrl+"/book/details/"+props.book.isbn +'#review'}> {props.book.numReviews} {messages.reviews} </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
  )
}

export default BookItem
import React, {useEffect, useRef} from 'react';
import * as PropTypes from "prop-types";

function RetinaImg(props) {
  let img = useRef();

  useEffect(() => {
    let element = img.current;
    if (props.src.indexOf("@1x.") >= 0) {
      let imgRation = window.devicePixelRatio > 3 ? 3 : window.devicePixelRatio < 1 ? 1 : window.devicePixelRatio;
      element.setAttribute('src',props.src.replace("@1x.", "@" + Math.floor(imgRation) + "x."));
    }
  });

  return (
    <img ref={img} {...props} />
  );
}

RetinaImg.prototype = {
  src:PropTypes.string.isRequired
};

export default RetinaImg;
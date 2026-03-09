import React from "react";
function Button(props) {
  console.log(`Button clicked ${props.children}`);
  return (
    <div>
      <button onClick={props.handleClick}> {props.children} </button>
    </div>
  );
}
export default React.memo(Button);

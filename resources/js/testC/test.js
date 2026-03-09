import React, { useEffect, useState, StrictMode, useCallback } from "react";
import ReactDOM from "react-dom";
import Count from "./Count";
import Button from "./Button";
import Title from "./Title";

const TEST = () => {
  console.log("Parent Rendering");
  const [age, setAge] = useState(25);
  const [salary, setSalary] = useState(25000);
  const incrementAge = useCallback(() => {
    setAge(age + 1);
  },[age]);
  const incrementSalary = useCallback(() => {
    setSalary(salary + 1000);
  },[salary]);
  return <>
  <Title/>
  <Count text="age" count={age} />
  <Button handleClick={incrementAge}>Increment my age</Button>
  <Count text="salary" count={salary} />
  <Button handleClick={incrementSalary}>Increment my salary</Button> 
  </>;
};
export default TEST;
if (document.getElementById("test")) {
    ReactDOM.render(<TEST />, document.getElementById("test"));
  }

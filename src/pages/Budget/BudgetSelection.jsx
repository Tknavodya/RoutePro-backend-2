import React, { useState } from "react";
import StartingPoint from "../../Components/StartingPoint";
import BudgetSelector from "./BudgetSelector";
import NearbyAttractions from "../Route/NearbyAttractions";
import MapComponent from "../../Components/MapComponentb";
import "./BudgetSelection.css";

export default function BudgetSelection() {
  const [location, setLocation] = useState("");
  const [budget, setBudget] = useState(5000);
  const [attractions, setAttractions] = useState([]);

  return (
    <div className="budget-container">
      <h1 className="budget-heading">Budget Explorer</h1>
      <p className="budget-subtitle">Discover Sri Lanka within your budget</p>

      <div className="budget-main">
        <div className="budget-sidebar">
          <StartingPoint location={location} setLocation={setLocation} />
          <BudgetSelector budget={budget} setBudget={setBudget} />
          <NearbyAttractions attractions={attractions} />
        </div>
        <div className="budget-map-area">
          <MapComponent
            location={location}
            budget={budget}
            setAttractions={setAttractions}
          />
        </div>
      </div>
    </div>
  );
}

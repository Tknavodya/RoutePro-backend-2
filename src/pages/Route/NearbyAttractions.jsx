import React from "react";

export default function NearbyAttractions({ attractions }) {
  return (
    <div className="budget-card">
      <h2>Nearby Attractions</h2>
      {attractions.length === 0 ? (
        <p className="note">No attractions yet — enter a starting point & select a package!</p>
      ) : (
        <ul>
          {attractions.map((place, index) => (
            <li key={index}>
              <strong>{place.name}</strong><br />
              <small>{place.type}</small><br />
              <small>{place.address}</small>
            </li>
          ))}
        </ul>
      )}
    </div>
  );
}
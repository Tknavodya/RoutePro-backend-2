import React, { useState } from "react";

export default function TripDateSelector({ onClose, onConfirm }) {
  const [fromDate, setFromDate] = useState("");
  const [toDate, setToDate] = useState("");

  // Get today's date in YYYY-MM-DD format
  const today = new Date().toISOString().split('T')[0];

  const handleConfirm = () => {
    if (fromDate && toDate) {
      onConfirm({ fromDate, toDate });
    }
  };

  return (
    <div className="popup-overlay">
      <div className="popup-content">
        <h2>Select Trip Dates</h2>
        <p>Please select your trip dates before booking driver and guide.</p>
        
        <div style={{ margin: "1rem 0" }}>
          <label htmlFor="trip-from-date"><strong>Trip Start Date:</strong></label><br />
          <input
            id="trip-from-date"
            type="date"
            value={fromDate}
            onChange={e => setFromDate(e.target.value)}
            min={today} // Prevent selecting previous dates
            style={{ marginTop: "0.5rem", padding: "0.5rem", borderRadius: "0.3rem", border: "1px solid #ccc", width: "100%" }}
          />
        </div>
        
        <div style={{ margin: "1rem 0" }}>
          <label htmlFor="trip-to-date"><strong>Trip End Date:</strong></label><br />
          <input
            id="trip-to-date"
            type="date"
            value={toDate}
            onChange={e => setToDate(e.target.value)}
            min={fromDate} // Ensure end date is not before start date
            style={{ marginTop: "0.5rem", padding: "0.5rem", borderRadius: "0.3rem", border: "1px solid #ccc", width: "100%" }}
          />
        </div>
        
        <div style={{ marginTop: "1.5rem" }}>
          <button onClick={onClose} style={{ marginRight: "1rem" }}>Cancel</button>
          <button 
            onClick={handleConfirm} 
            disabled={!fromDate || !toDate}
            style={{ 
              backgroundColor: fromDate && toDate ? "#04614e" : "#ccc",
              color: "white",
              padding: "0.5rem 1rem",
              border: "none",
              borderRadius: "0.3rem",
              cursor: fromDate && toDate ? "pointer" : "not-allowed"
            }}
          >
            Proceed to Booking
          </button>
        </div>
      </div>
    </div>
  );
}

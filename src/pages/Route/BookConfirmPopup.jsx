import React, { useState } from "react";

export default function BookConfirmPopup({ item, type, onCancel, onConfirm }) {
  const [selectedDate, setSelectedDate] = useState("");
  
  // Get today's date in YYYY-MM-DD format for min date restriction
  const today = new Date().toISOString().split('T')[0];
  
  // Get max date (e.g., 6 months from now)
  const maxDate = new Date();
  maxDate.setMonth(maxDate.getMonth() + 6);
  const maxDateString = maxDate.toISOString().split('T')[0];
  
  const handleConfirm = () => {
    if (!selectedDate) {
      alert("Please select a booking date");
      return;
    }
    onConfirm(selectedDate);
  };

  if (!item) return null;
  
  return (
    <div className="popup-overlay">
      <div className="popup-content">
        <h2>Confirm Your Booking</h2>
        <div className="booking-details">
          <p><strong>Name:</strong> {item.name}</p>
          <p><strong>Location:</strong> {item.city || item.hometown}</p>
          <p><strong>Experience:</strong> {item.experience} years</p>
          {type === "driver" && <p><strong>Vehicle:</strong> {item.vehicle}</p>}
          {type === "guide" && <p><strong>Languages:</strong> {item.languages ? item.languages.join(", ") : "N/A"}</p>}
          <p><strong>Contact:</strong> {item.contact || item.mobile}</p>
        </div>
        
        <div className="date-selection">
          <label htmlFor="bookingDate"><strong>Select Booking Date:</strong></label>
          <input
            type="date"
            id="bookingDate"
            value={selectedDate}
            onChange={(e) => setSelectedDate(e.target.value)}
            min={today}
            max={maxDateString}
            required
            style={{
              width: "100%",
              padding: "0.75rem",
              marginTop: "0.5rem",
              border: "2px solid #ddd",
              borderRadius: "0.5rem",
              fontSize: "1rem",
              backgroundColor: "#fff"
            }}
          />
          <small style={{ color: "#666", fontSize: "0.85rem", marginTop: "0.25rem", display: "block" }}>
            * You can only select dates from today onwards
          </small>
        </div>
        
        <div className="popup-buttons" style={{ marginTop: "2rem", display: "flex", gap: "1rem", justifyContent: "center" }}>
          <button 
            onClick={onCancel} 
            style={{ 
              padding: "0.75rem 1.5rem", 
              backgroundColor: "#6c757d", 
              color: "white", 
              border: "none", 
              borderRadius: "0.5rem",
              fontSize: "1rem",
              cursor: "pointer"
            }}
          >
            Cancel
          </button>
          <button 
            onClick={handleConfirm} 
            style={{ 
              padding: "0.75rem 1.5rem", 
              backgroundColor: "#04614e", 
              color: "white", 
              border: "none", 
              borderRadius: "0.5rem",
              fontSize: "1rem",
              cursor: "pointer"
            }}
          >
            Confirm Booking
          </button>
        </div>
      </div>
    </div>
  );
}

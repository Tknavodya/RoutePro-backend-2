import React from "react";

export default function BookingSuccessPopup({ onClose, contact, bookingDate }) {
  // Format the date nicely
  const formatDate = (dateString) => {
    if (!dateString) return "";
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', { 
      weekday: 'long', 
      year: 'numeric', 
      month: 'long', 
      day: 'numeric' 
    });
  };

  // Format date range or single date
  const formatBookingDate = (bookingDate) => {
    if (!bookingDate) return "";
    
    // Check if it's a date range object
    if (typeof bookingDate === 'object' && bookingDate.fromDate && bookingDate.toDate) {
      const fromFormatted = formatDate(bookingDate.fromDate);
      const toFormatted = formatDate(bookingDate.toDate);
      
      // If same date, show only once
      if (bookingDate.fromDate === bookingDate.toDate) {
        return fromFormatted;
      }
      
      return `${fromFormatted} to ${toFormatted}`;
    }
    
    // Single date string
    return formatDate(bookingDate);
  };

  return (
    <div className="popup-overlay">
      <div className="popup-content">
        <div style={{ textAlign: "center" }}>
          <div style={{ fontSize: "3rem", color: "#04614e", marginBottom: "1rem" }}>✓</div>
          <h2 style={{ color: "#04614e", marginBottom: "1rem" }}>Booking Confirmed!</h2>
          <p style={{ fontSize: "1.1rem", marginBottom: "1rem" }}>
            Your booking has been successfully confirmed.
          </p>
          
          {bookingDate && (
            <div style={{ 
              backgroundColor: "#f8f9fa", 
              padding: "1rem", 
              borderRadius: "0.5rem", 
              border: "2px solid #04614e", 
              marginBottom: "1rem" 
            }}>
              <p style={{ margin: "0", fontWeight: "bold", color: "#04614e" }}>
                📅 Trip Dates: {formatBookingDate(bookingDate)}
              </p>
            </div>
          )}
          
          {contact && (
            <div style={{ 
              backgroundColor: "#e8f5f3", 
              padding: "1rem", 
              borderRadius: "0.5rem", 
              marginBottom: "1.5rem" 
            }}>
              <p style={{ margin: "0", fontWeight: "bold" }}>
                📞 Contact: <span style={{ color: "#04614e" }}>{contact}</span>
              </p>
              <small style={{ color: "#666" }}>Save this number for your trip!</small>
            </div>
          )}
          
          <button 
            onClick={onClose} 
            style={{ 
              backgroundColor: "#04614e", 
              color: "white", 
              padding: "0.75rem 2rem", 
              border: "none", 
              borderRadius: "0.5rem", 
              fontSize: "1rem",
              cursor: "pointer",
              fontWeight: "600"
            }}
          >
            Done
          </button>
        </div>
      </div>
    </div>
  );
}

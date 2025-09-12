import React, { useState } from "react";
import { useParams, useNavigate } from "react-router-dom";
import { FaCar, FaPhoneAlt, FaMapMarkerAlt, FaIdCard, FaUser } from "react-icons/fa";
import drivers from "./driversData";
import BookingSuccessPopup from "./BookingSuccessPopup";
import "./GuideDetails.css"; // Use the same CSS as GuideDetails

// Sample reviews for drivers
const sampleReviews = [
  "Very professional and friendly! Highly recommended.",
  "Clean vehicle and safe driving. Will book again!"
];

const renderStars = (rating) => {
  return Array.from({ length: 5 }, (_, i) => (
    <span key={i} style={{ color: i < rating ? "#FFD700" : "#ddd" }}>
      &#9733;
    </span>
  ));
};

export default function DriverDetails() {
  const { id } = useParams();
  const navigate = useNavigate();
  const [showSuccess, setShowSuccess] = useState(false);
  const [bookingDate, setBookingDate] = useState("");
  
  // Find driver by id (convert string to number)
  const driver = drivers.find(driver => driver.id === parseInt(id));
  
  console.log('DriverDetails - ID from params:', id);
  console.log('DriverDetails - Found driver:', driver);

  if (!driver) {
    return (
      <div style={{ padding: '2rem', textAlign: 'center' }}>
        <h2>Driver Not Found</h2>
        <p>The driver you're looking for doesn't exist.</p>
        <button onClick={() => navigate('/bookdriver')}>Back to Drivers</button>
      </div>
    );
  }

  const handleBook = () => {
    // Get the previously selected trip dates from localStorage
    const tripDates = localStorage.getItem('tripDates');
    let fromDate = new Date().toISOString().split('T')[0]; // Default to today
    let toDate = new Date().toISOString().split('T')[0]; // Default to today
    
    if (tripDates) {
      try {
        const dates = JSON.parse(tripDates);
        fromDate = dates.fromDate || fromDate;
        toDate = dates.toDate || toDate;
      } catch (error) {
        console.log('Error parsing trip dates, using today as default');
      }
    }
    
    // Create date range object for booking
    const bookingDates = { fromDate, toDate };
    setBookingDate(bookingDates);
    setShowSuccess(true);
    
    // Mark driver as booked by updating the driver object
    const driverIndex = drivers.findIndex(d => d.id === parseInt(id));
    if (driverIndex !== -1) {
      // Add this booking to driver's bookings array
      drivers[driverIndex].bookings.push({
        startDate: fromDate,
        endDate: toDate
      });
      
      // Update the booked status
      drivers[driverIndex].booked = true;
      drivers[driverIndex].bookedDate = bookingDates;
      
      // Optional - you can save to localStorage here to persist data
      localStorage.setItem('driversData', JSON.stringify(drivers));
    }
    console.log(`Driver booked from ${fromDate} to ${toDate}`);
  };

  const handleSuccessClose = () => {
    setShowSuccess(false);
    navigate('/bookdriver'); // Navigate back to booking page
  };

  return (
    <div>
      <div className="guide-details-container">
        <div className="guide-image-details">
          <img src={driver.image} alt={driver.name} style={{ width: "200px", borderRadius: "1rem", marginBottom: "1rem" }} />
          <div className="guide-reviews">
            <span style={{ fontSize: "1.2rem", color: "#FFD700" }}>{renderStars(driver.rating)}</span>
            <span style={{ marginLeft: "0.5rem", color: "#555" }}>({driver.rating} rating)</span>
            <div style={{ marginTop: "1rem", textAlign: "left" }}>
              <div className="guide-review-row">
                <FaUser style={{marginRight: "6px", fontSize: "1.1em"}} />
                <span>&quot;{sampleReviews[0]}&quot;</span>
              </div>
              <div className="guide-review-row">
                <FaUser style={{marginRight: "6px", fontSize: "1.1em"}} />
                <span>&quot;{sampleReviews[1]}&quot;</span>
              </div>
            </div>
          </div>
        </div>
        <div className="guide-info-details">
          <h2>Driver Details</h2>
          <p><strong>Name:</strong> {driver.name}</p>
          <p><strong>Location:</strong> {driver.location}</p>
          <p><strong>Years of Experience:</strong> {driver.experience}</p>
          <p><FaCar style={{marginRight: '8px'}}/><strong>Vehicle:</strong> {driver.vehicle}</p>
          <p><FaIdCard style={{marginRight: '8px'}}/><strong>License:</strong> {driver.license}</p>
          <p><FaPhoneAlt style={{marginRight: '8px'}}/><strong>Contact No:</strong> {driver.contact}</p>
          <button onClick={handleBook}>Book Driver</button>
          {showSuccess && (
            <BookingSuccessPopup 
              onClose={handleSuccessClose} 
              contact={driver.contact} 
              bookingDate={bookingDate}
            />
          )}
        </div>
      </div>
    </div>
  );
}

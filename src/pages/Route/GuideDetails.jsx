import React, { useState } from "react";
import { FaGlobe, FaPhoneAlt, FaUser } from "react-icons/fa";
import "./GuideDetails.css";
import { useParams, useNavigate } from "react-router-dom";
import BookingSuccessPopup from "./BookingSuccessPopup";
import guides from "./guidesData";

// Example reviews for each guide (could be part of guide object)
const sampleReviews = [
  "Excellent knowledge of local culture and history!",
  "Very friendly and speaks multiple languages fluently!"
];

export default function GuideDetails() {
  const { id } = useParams();
  const navigate = useNavigate();
  const [showSuccess, setShowSuccess] = useState(false);
  
  // Find guide by id (convert string to number)
  const guide = guides.find(guide => guide.id === parseInt(id));
  
  console.log('GuideDetails - ID from params:', id);
  console.log('GuideDetails - Found guide:', guide);

  if (!guide) return <div>No guide found.</div>;

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
    
    // Skip confirmation popup and directly show success
    setShowSuccess(true);
    
    // Mark guide as booked by updating the guide object
    const guideIndex = guides.findIndex(g => g.id === parseInt(id));
    if (guideIndex !== -1) {
      // Add this booking to guide's bookings array
      guides[guideIndex].bookings.push({
        startDate: fromDate,
        endDate: toDate
      });
      
      // Update booked status
      guides[guideIndex].booked = true;
      guides[guideIndex].bookedDate = { fromDate, toDate };
      
      // Optional - save to localStorage for persistence
      localStorage.setItem('guidesData', JSON.stringify(guides));
    }
    console.log(`Guide booked from ${fromDate} to ${toDate}`);
  };

  const handleSuccessClose = () => {
    setShowSuccess(false);
    navigate('/bookdriver'); // Navigate back to booking page
  };

  // Render stars for rating
  const renderStars = (rating) => {
    return Array.from({ length: 5 }, (_, i) => (
      <span key={i} className={i < rating ? "star filled" : "star"}>
        &#9733;
      </span>
    ));
  };

  return (
    <div>
      <div className="guide-details-container">
      <div className="guide-image-details">
        <img src={guide.image} alt={guide.name} style={{ width: "200px", borderRadius: "1rem", marginBottom: "1rem" }} />
        <div className="guide-reviews">
          <span style={{ fontSize: "1.2rem", color: "#FFD700" }}>{renderStars(guide.rating)}</span>
          <span style={{ marginLeft: "0.5rem", color: "#555" }}>({guide.rating} rating)</span>
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
        <h2>Guide Details</h2>
        <p><strong>Name:</strong> {guide.name}</p>
        <p><strong>Hometown:</strong> {guide.hometown}</p>
        <p><strong>Years of Experience:</strong> {guide.experience}</p>
        <p><FaGlobe style={{marginRight: '8px'}}/><strong>Languages:</strong> 
          <span className="guide-languages">
            {guide.languages.map((lang, index) => (
              <span key={index} className="language-tag">{lang}</span>
            ))}
          </span>
        </p>
        <p><FaPhoneAlt style={{marginRight: '8px'}}/><strong>Contact No:</strong> {guide.mobile}</p>
        <button onClick={handleBook}>Book Guide</button>
        {showSuccess && (
          <BookingSuccessPopup 
            onClose={handleSuccessClose} 
            contact={guide.mobile} 
            bookingDate={(() => {
              const tripDates = localStorage.getItem('tripDates');
              if (tripDates) {
                try {
                  const dates = JSON.parse(tripDates);
                  return { 
                    fromDate: dates.fromDate || new Date().toISOString().split('T')[0],
                    toDate: dates.toDate || new Date().toISOString().split('T')[0]
                  };
                } catch (error) {
                  const today = new Date().toISOString().split('T')[0];
                  return { fromDate: today, toDate: today };
                }
              }
              const today = new Date().toISOString().split('T')[0];
              return { fromDate: today, toDate: today };
            })()} 
          />
        )}
      </div>
    </div>
    </div>
  );
}

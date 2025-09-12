// LocalGuidesSection.jsx
import React from "react";
import "./LocalGuidesSection.css";
import { useNavigate } from "react-router-dom";
import { useEffect, useState } from "react";
import { apiMethods } from "../../utils/api-client";

const renderStars = (rating) => {
  return Array.from({ length: 5 }, (_, i) => (
    <span key={i} className={`star ${i < rating ? "filled" : ""}`}>
      ★
    </span>
  ));
};

export default function LocalGuidesSection() {
  const navigate = useNavigate();
  const [guides, setGuides] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);
  
  // Get trip dates from localStorage
  const tripDatesStr = localStorage.getItem('tripDates');
  let tripDates = null;
  if (tripDatesStr) {
    try {
      tripDates = JSON.parse(tripDatesStr);
    } catch (error) {
      console.log('Error parsing trip dates');
    }
  }

  useEffect(() => {
    const fetchGuides = async () => {
      try {
        console.log("🚀 Attempting to fetch guides from API...");
        console.log("API Base URL:", apiMethods.getBackendUrl());
        console.log("Full URL:", `${apiMethods.getBackendUrl()}/guides`);
        
        // Test direct fetch first
        const directUrl = `${apiMethods.getBackendUrl()}/guides`;
        console.log("🔍 Testing direct fetch to:", directUrl);
        
        const directResponse = await fetch(directUrl, {
          method: 'GET',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json'
          }
        });
        
        console.log("📊 Direct fetch response status:", directResponse.status);
        console.log("📊 Direct fetch response headers:", directResponse.headers);
        
        if (directResponse.ok) {
          const directData = await directResponse.json();
          console.log("📊 Direct fetch response data:", directData);
        } else {
          console.log("❌ Direct fetch failed:", await directResponse.text());
        }
        
        // Now try with the API client
        const response = await apiMethods.authenticatedRequest("/guides", null, "GET");
        console.log("📊 API Client Response:", response);
        
        // Handle different response structures
        let guideData = [];
        if (response.data) {
          // Check for different response structures from backend
          if (Array.isArray(response.data.guides)) {
            guideData = response.data.guides;
          } else if (Array.isArray(response.data.data)) {
            guideData = response.data.data;
          } else if (Array.isArray(response.data)) {
            guideData = response.data;
          }
        } else if (Array.isArray(response)) {
          guideData = response;
        }
        
        console.log("✅ Processed guides data:", guideData);
        
        if (guideData.length === 0) {
          console.log("⚠️ No guides found in database");
          setGuides([]);
          setError("No guides available at the moment");
        } else {
          setGuides(guideData);
        }
        
      } catch (err) {
        console.error("❌ Error fetching guides from API:", err);
        setGuides([]);
        setError("Failed to load guides. Please try again later.");
      } finally {
        setLoading(false);
      }
    };
    
    // Fetch real guides from database
    fetchGuides();
  }, []);

  // Helper function to check if a guide is available - check database status
  const isGuideAvailable = (guide) => {
    // Debug: Log each guide's status
    console.log(`Guide ${guide.name || guide.user_name} status:`, guide.status);
    console.log("Full guide object:", guide);
    
    // Only show guides that are NOT "nonavailable"
    return guide.status !== "nonavailable";
    
    // Alternative: Only show guides with specific available statuses
    // return guide.status === "available" || guide.status === "Available";
  };
  
  // Filter guides based on availability only (ignore date filtering)
  const availableGuides = Array.isArray(guides) ? guides.filter(isGuideAvailable) : [];
  
  // Add debug logging
  console.log("Total guides fetched:", guides);
  console.log("Available guides after filtering:", availableGuides);
  console.log("Trip dates:", tripDates);
  
  if (loading) return <div>Loading guides...</div>;
  if (error) return <div style={{padding: '20px', color: 'red'}}>{error}</div>;
  
  // Show message if no guides available
  if (availableGuides.length === 0) {
    return (
      <section className="guides-section">
        <h2>MEET YOUR LOCAL GUIDES</h2>
        <p className="subtitle">
          {Array.isArray(guides) && guides.length > 0 
            ? `Found ${guides.length} guides, but none are available for the selected dates.`
            : "No guides found in the database."
          }
        </p>
        <div style={{marginTop: '10px', fontSize: '14px', color: '#666'}}>
          <p>Debug info:</p>
          <p>Total guides: {Array.isArray(guides) ? guides.length : 'Not an array'}</p>
          <p>Trip dates: {tripDates ? `${tripDates.fromDate} to ${tripDates.toDate}` : 'None selected'}</p>
        </div>
      </section>
    );
  }

  return (
    <section className="guides-section">
      <h2>MEET YOUR LOCAL GUIDES</h2>
      <p className="subtitle">
        Expert local guides ready to unlock the hidden gems of Sri Lanka!
        {tripDates && tripDates.fromDate && tripDates.toDate && 
          ` Available for your trip from ${new Date(tripDates.fromDate).toLocaleDateString()} to ${new Date(tripDates.toDate).toLocaleDateString()}.`
        }
      </p>
      <div className="cards">
        {availableGuides.map((guide) => {
          // Debug: Log guide image info
          console.log(`Guide ${guide.name} image info:`, {
            image: guide.image,
            photo_url: guide.photo_url,
            photo: guide.photo
          });
          
          return (
            <article key={guide.id} className="guide-card">
              <div className="image-container">
                <img 
                  src={guide.photo_url || guide.image || 'https://via.placeholder.com/150x150/28A745/FFFFFF?text=Guide'} 
                  alt={guide.name} 
                  className="guide-image" 
                  onError={(e) => {
                    console.log(`Image failed to load for ${guide.name}:`, e.target.src);
                    e.target.src = 'https://via.placeholder.com/150x150/28A745/FFFFFF?text=Guide';
                  }}
                />
                <span className="price-badge">{guide.status || guide.availability || 'Available'}</span>
                {guide.verified && <span className="badge verified">Verified</span>}
                {guide.recommended && <span className="badge recommended">Recommended</span>}
              </div>
              <div className="card-body">
                <h3>{guide.name}</h3>
                <ul className="guide-info">
                  <li>🌍 {guide.specialization || guide.languages || 'Specialization'}</li>
                  <li>📍 {guide.location || 'Location Info'}</li>
                  <li>✅ {guide.languages || 'Languages'}</li>
                </ul>
                <div className="rating">
                  {renderStars(guide.rating || 4)}
                  <span className="rating-text">({guide.rating || 4}/5)</span>
                </div>
                <div className="experience">
                  <span>Experience: {guide.experience || '5+'} years</span>
                </div>
                <button 
                  className="book-now-btn"
                  onClick={() => {
                    // Store guide info in localStorage for booking
                    localStorage.setItem('selectedGuide', JSON.stringify({
                      id: guide.id,
                      name: guide.name,
                      specialization: guide.specialization || guide.languages,
                      location: guide.location,
                      rating: guide.rating || 4,
                      phone: guide.phone,
                      photo_url: guide.photo_url
                    }));
                    navigate('/booking');
                  }}
                >
                  Book Now
                </button>
              </div>
            </article>
          );
        })}
      </div>
    </section>
  );
}

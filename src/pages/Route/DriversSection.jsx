// DriversSection.jsx
import React from "react";
import "./DriversSection.css";
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

export default function DriversSection() {
  const navigate = useNavigate();
  const [drivers, setDrivers] = useState([]);
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
    const fetchDrivers = async () => {
      try {
        console.log("🚀 Attempting to fetch drivers from API...");
        console.log("API Base URL:", apiMethods.getBackendUrl());
        console.log("Full URL:", `${apiMethods.getBackendUrl()}/drivers`);
        
        // Test direct fetch first
        const directUrl = `${apiMethods.getBackendUrl()}/drivers`;
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
        const response = await apiMethods.authenticatedRequest("/drivers", null, "GET");
        console.log("📊 API Client Response:", response);
        
        // Handle different response structures
        let driverData = [];
        if (response.data) {
          // Check for different response structures from backend
          if (Array.isArray(response.data.drivers)) {
            driverData = response.data.drivers;
          } else if (Array.isArray(response.data.data)) {
            driverData = response.data.data;
          } else if (Array.isArray(response.data)) {
            driverData = response.data;
          }
        } else if (Array.isArray(response)) {
          driverData = response;
        }
        
        console.log("✅ Processed drivers data:", driverData);
        
        if (driverData.length === 0) {
          console.log("⚠️ No drivers found in database");
          setDrivers([]);
          setError("No drivers available at the moment");
        } else {
          setDrivers(driverData);
        }
        
      } catch (err) {
        console.error("❌ Error fetching drivers from API:", err);
        setDrivers([]);
        setError("Failed to load drivers. Please try again later.");
      } finally {
        setLoading(false);
      }
    };
    
    // Fetch real drivers from database
    fetchDrivers();
  }, []);

  // Helper function to check if a driver is available - check database status
  const isDriverAvailable = (driver) => {
    // Debug: Log each driver's status
    console.log(`Driver ${driver.name || driver.user_name} status:`, driver.status);
    console.log("Full driver object:", driver);
    
    // Only show drivers that are NOT "nonavailable"
    return driver.status !== "nonavailable";
    
    // Alternative: Only show drivers with specific available statuses
    // return driver.status === "available" || driver.status === "Available";
  };
  
  // Filter drivers based on availability only (ignore date filtering)
  const availableDrivers = Array.isArray(drivers) ? drivers.filter(isDriverAvailable) : [];
  
  // Add debug logging
  console.log("Total drivers fetched:", drivers);
  console.log("Available drivers after filtering:", availableDrivers);
  console.log("Trip dates:", tripDates);
  
  if (loading) return <div>Loading drivers...</div>;
  if (error) return <div style={{padding: '20px', color: 'red'}}>{error}</div>;
  
  // Show message if no drivers available
  if (availableDrivers.length === 0) {
    return (
      <section className="drivers-section">
        <h2>MEET YOUR LOCAL DRIVERS</h2>
        <p className="subtitle">
          {Array.isArray(drivers) && drivers.length > 0 
            ? `Found ${drivers.length} drivers, but none are available for the selected dates.`
            : "No drivers found in the database."
          }
        </p>
        <div style={{marginTop: '10px', fontSize: '14px', color: '#666'}}>
          <p>Debug info:</p>
          <p>Total drivers: {Array.isArray(drivers) ? drivers.length : 'Not an array'}</p>
          <p>Trip dates: {tripDates ? `${tripDates.fromDate} to ${tripDates.toDate}` : 'None selected'}</p>
        </div>
      </section>
    );
  }

  return (
    <section className="drivers-section">
      <h2>MEET YOUR LOCAL DRIVERS</h2>
      <p className="subtitle">
        Professional, verified drivers ready to make your journey memorable!
        {tripDates && tripDates.fromDate && tripDates.toDate && 
          ` Available for your trip from ${new Date(tripDates.fromDate).toLocaleDateString()} to ${new Date(tripDates.toDate).toLocaleDateString()}.`
        }
      </p>
      <div className="cards">
        {availableDrivers.map((driver) => {
          // Debug: Log driver image info
          console.log(`Driver ${driver.name} image info:`, {
            image: driver.image,
            photo_url: driver.photo_url,
            photo: driver.photo
          });
          
          return (
            <article key={driver.id} className="driver-card">
              <div className="image-container">
                <img 
                  src={driver.photo_url || driver.image || 'https://via.placeholder.com/150x150/4A90E2/FFFFFF?text=Driver'} 
                  alt={driver.name} 
                  className="driver-image" 
                  onError={(e) => {
                    console.log(`Image failed to load for ${driver.name}:`, e.target.src);
                    e.target.src = 'https://via.placeholder.com/150x150/4A90E2/FFFFFF?text=Driver';
                  }}
                />
                <span className="price-badge">{driver.status || driver.availability || 'Available'}</span>
                {driver.verified && <span className="badge verified">Verified</span>}
                {driver.recommended && <span className="badge recommended">Recommended</span>}
              </div>
              <div className="card-body">
                <h3>{driver.name}</h3>
                <ul className="driver-info">
                  <li>🚗 {driver.vehicle_type || driver.vehicle || 'Vehicle Info'}</li>
                  <li>📍 {driver.location || 'Location Info'}</li>
                  <li>✅ {driver.license_no || driver.license || 'Licensed'}</li>
                </ul>
                <div className="rating">
                  {renderStars(driver.rating || 4)}
                  <span className="rating-text">({driver.rating || 4}/5)</span>
                </div>
                <div className="experience">
                  <span>Experience: {driver.experience || '5+'} years</span>
                </div>
                <button 
                  className="book-now-btn"
                  onClick={() => {
                    // Store driver info in localStorage for booking
                    localStorage.setItem('selectedDriver', JSON.stringify({
                      id: driver.id,
                      name: driver.name,
                      vehicle: driver.vehicle_type || driver.vehicle,
                      location: driver.location,
                      rating: driver.rating || 4,
                      phone: driver.phone,
                      photo_url: driver.photo_url
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

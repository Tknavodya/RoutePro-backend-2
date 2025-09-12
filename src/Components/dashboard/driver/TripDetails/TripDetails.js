import React from 'react';
import './TripDetails.css';

const trips = [
  { id: 'G001', traveler: 'Helena', time: '9:00 AM', fee: 'Rs. 5500', location: 'Sigiriya', type: 'Half-day', status: 'Active' },
  { id: 'G002', traveler: 'Monk H.', time: '11:30 AM', fee: 'Rs. 4200', location: 'Kandy', type: 'Temple tour', status: 'Active' },
  { id: 'G003', traveler: 'Family L', time: '2:00 PM', fee: 'Rs. 7800', location: 'Galle', type: 'City tour', status: 'Scheduled' }
];

const TripDetails = () => (
  <div className="trip-details">
    <div className="trip-details-header">
      <h2>Upcoming Tours</h2>
      <span className="trip-count">{trips.length}</span>
    </div>
    <div className="trip-grid">
      {trips.map(t => (
        <div key={t.id} className="trip-card">
          <div className="trip-card-header">
            <span className={`status-badge ${t.status.toLowerCase()}`}>{t.status}</span>
            <span className="trip-id">{t.id}</span>
          </div>
          <p><strong>Traveler:</strong> {t.traveler}</p>
          <p><strong>Start Time:</strong> {t.time}</p>
          <p><strong>Fee:</strong> {t.fee}</p>
          <p><strong>Location:</strong> {t.location}</p>
          <p><strong>Tour Type:</strong> {t.type}</p>
          <div className="trip-actions">
            <button>Start Tour</button>
            <button>View Route</button>
            <button>Contact</button>
          </div>
        </div>
      ))}
    </div>
  </div>
);

export default TripDetails;

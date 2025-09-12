import React from 'react';
import './Availability.css';

const Availability = ({ status, setStatus }) => {
  return (
    <div className="availability-panel">
      <h2>Availability & Info</h2>

      <p>
        <strong>Status:</strong>{' '}
        <span className={status.toLowerCase()}>{status}</span>
      </p>

      {/* Change Status Button Centered */}
      <div className="button-center">
  <button
    className={`action-btn ${status.toLowerCase()}`}
    onClick={() =>
      setStatus(status === 'Available' ? 'Unavailable' : 'Available')
    }
  >
    {status === 'Available' ? 'Go Unavailable' : 'Go Available'}
  </button>
</div>


      <div className="driver-info">
        <p><strong>Working Hours:</strong> 9:00 AM – 6:00 PM</p>
        <p><strong>Contact:</strong> 077 123 4567</p>
        <p><strong>Vehicle:</strong> Toyota Prius / WP CAB 1234</p>
        <p><strong>Languages:</strong>
          <input type="text" defaultValue="Sinhala, English" />
        </p>

        {/* Save Changes Button Centered Under Languages */}
        <div className="button-center">
          <button className="action-btn">Save Changes</button>
        </div>
      </div>
    </div>
  );
};

export default Availability;

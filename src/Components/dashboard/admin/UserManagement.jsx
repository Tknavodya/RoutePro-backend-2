import React, { useState, useEffect } from "react";
import "./UserManagement.css";

const UserManagement = () => {
  const [activeTab, setActiveTab] = useState("travelers");
  const [users, setUsers] = useState({
    travelers: [],
    drivers: [],
    guides: []
  });
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState(null);
  const [searchTerm, setSearchTerm] = useState("");

  useEffect(() => {
    fetchUsers();
  }, [activeTab, searchTerm]);

  const fetchUsers = async () => {
    try {
      setLoading(true);
      setError(null);
      
      // Use the drivers endpoint which now returns all user types
      const url = `http://localhost/RoutePro-backend(02)/public/drivers?search=${encodeURIComponent(searchTerm)}`;
      
      const response = await fetch(url, {
        method: 'GET',
        headers: {
          'Content-Type': 'application/json',
        },
        credentials: 'include'
      });

      const data = await response.json();
      console.log('API Response data:', data); // Debug log
      console.log('Drivers with status:', data.drivers?.slice(0, 5).map(d => ({name: d.name, status: d.status}))); // First 5 drivers
      console.log('Guides with status:', data.guides?.slice(0, 5).map(g => ({name: g.name, status: g.status}))); // First 5 guides
      if (data.success) {
        // The endpoint now returns all user types
        const grouped = {
          travelers: data.travelers || [],
          drivers: data.drivers || [],
          guides: data.guides || []
        };
        console.log('Grouped data:', grouped); // Debug log
        setUsers(grouped);
      } else {
        setError(data.message || 'Failed to fetch users');
      }
    } catch (err) {
      console.error('Error fetching users:', err);
      setError('Failed to connect to server');
    } finally {
      setLoading(false);
    }
  };

  const updateRating = async (userId, newRating) => {
    try {
      const response = await fetch('http://localhost/RoutePro-backend(02)/public/admin/users/update-rating', {
        method: 'PUT',
        headers: {
          'Content-Type': 'application/json',
        },
        credentials: 'include',
        body: JSON.stringify({
          userId: userId,
          rating: newRating
        })
      });

      const data = await response.json();
      
      if (data.success) {
        // Refresh the data
        fetchUsers();
      } else {
        alert('Failed to update rating: ' + data.message);
      }
    } catch (err) {
      console.error('Error updating rating:', err);
      alert('Failed to update rating');
    }
  };

  const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString();
  };

  const formatCurrency = (amount) => {
    return `Rs. ${amount.toLocaleString()}`;
  };

  const renderTravelersTable = () => (
    <div className="table-container">
      <table className="users-table">
        <thead>
          <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Email</th>
            <th>Phone</th>
            <th>Rating</th>
            <th>Join Date</th>
          </tr>
        </thead>
        <tbody>
          {users.travelers?.length > 0 ? (
            users.travelers.map((traveler) => (
              <tr key={traveler.id}>
                <td>{traveler.id}</td>
                <td>{traveler.name}</td>
                <td>{traveler.email}</td>
                <td>{traveler.phone || 'N/A'}</td>
                <td>
                  <span className={`rating ${(traveler.rating || 0) < 2.5 ? 'low' : ''}`}>
                    ⭐ {parseFloat(traveler.rating || 0).toFixed(1)}
                  </span>
                </td>
                <td>{formatDate(traveler.created_at)}</td>
              </tr>
            ))
          ) : (
            <tr>
              <td colSpan="6" className="no-data">
                No travelers found
              </td>
            </tr>
          )}
        </tbody>
      </table>
    </div>
  );

  const renderDriversTable = () => (
    <div className="table-container">
      <table className="users-table">
        <thead>
          <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Email</th>
            <th>Phone</th>
            <th>Vehicle</th>
            <th>Experience</th>
            <th>Rating</th>
            <th>Status</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          {users.drivers?.length > 0 ? (
            users.drivers.map((driver) => (
              <tr key={driver.id}>
                <td>{driver.id}</td>
                <td>{driver.name}</td>
                <td>{driver.email}</td>
                <td>{driver.phone || 'N/A'}</td>
                <td>{driver.vehicle_type || 'N/A'}</td>
                <td>{driver.experience || 0} years</td>
                <td>
                  <span className={`rating ${(driver.rating || 0) < 2.5 ? 'low' : ''}`}>
                    ⭐ {parseFloat(driver.rating || 0).toFixed(1)}
                  </span>
                </td>
                <td>
                  <span className={`status-badge ${driver.status?.toLowerCase() === 'nonavailable' ? 'unavailable' : driver.status?.toLowerCase() || 'unknown'}`}>
                    {driver.status === 'nonavailable' ? 'Unavailable' : 
                     driver.status === 'available' ? 'Available' : 
                     driver.status || 'Unknown'}
                  </span>
                </td>
                <td>
                  {(driver.rating || 0) < 2.5 && (
                    <button 
                      className="reset-rating-btn"
                      onClick={() => updateRating(driver.id, 0)}
                    >
                      Reset Rating
                    </button>
                  )}
                </td>
              </tr>
            ))
          ) : (
            <tr>
              <td colSpan="9" className="no-data">
                No drivers found
              </td>
            </tr>
          )}
        </tbody>
      </table>
    </div>
  );

  const renderGuidesTable = () => (
    <div className="table-container">
      <table className="users-table">
        <thead>
          <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Email</th>
            <th>Phone</th>
            <th>Languages</th>
            <th>Experience</th>
            <th>Rating</th>
            <th>Status</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          {users.guides?.length > 0 ? (
            users.guides.map((guide) => (
              <tr key={guide.id}>
                <td>{guide.id}</td>
                <td>{guide.name}</td>
                <td>{guide.email}</td>
                <td>{guide.phone || 'N/A'}</td>
                <td>{guide.languages || 'N/A'}</td>
                <td>{guide.experience || 0} years</td>
                <td>
                  <span className={`rating ${(guide.rating || 0) < 2.5 ? 'low' : ''}`}>
                    ⭐ {parseFloat(guide.rating || 0).toFixed(1)}
                  </span>
                </td>
                <td>
                  <span className={`status-badge ${guide.status?.toLowerCase() === 'nonavailable' ? 'unavailable' : guide.status?.toLowerCase() || 'unknown'}`}>
                    {guide.status === 'nonavailable' ? 'Unavailable' : 
                     guide.status === 'available' ? 'Available' : 
                     guide.status || 'Unknown'}
                  </span>
                </td>
                <td>
                  {(guide.rating || 0) < 2.5 && (
                    <button 
                      className="reset-rating-btn"
                      onClick={() => updateRating(guide.id, 0)}
                    >
                      Reset Rating
                    </button>
                  )}
                </td>
              </tr>
            ))
          ) : (
            <tr>
              <td colSpan="9" className="no-data">
                No guides found
              </td>
            </tr>
          )}
        </tbody>
      </table>
    </div>
  );

  return (
    <div className="user-management">
      <div className="page-header">
        <h2>User Management</h2>
        <div className="search-bar">
          <input 
            type="text" 
            placeholder="Search users..." 
            value={searchTerm}
            onChange={(e) => setSearchTerm(e.target.value)}
          />
          <button>🔍</button>
        </div>
      </div>

      <div className="user-tabs">
        <button
          className={`tab ${activeTab === "travelers" ? "active" : ""}`}
          onClick={() => setActiveTab("travelers")}
        >
          👥 Travelers ({users.travelers?.length || 0})
        </button>
        <button 
          className={`tab ${activeTab === "drivers" ? "active" : ""}`} 
          onClick={() => setActiveTab("drivers")}
        >
          🚗 Drivers ({users.drivers?.length || 0})
        </button>
        <button 
          className={`tab ${activeTab === "guides" ? "active" : ""}`} 
          onClick={() => setActiveTab("guides")}
        >
          🗺️ Guides ({users.guides?.length || 0})
        </button>
      </div>

      <div className="tab-content">
        {loading ? (
          <div className="loading-container">
            <div className="loading-spinner"></div>
            <p>Loading users...</p>
          </div>
        ) : error ? (
          <div className="error-container">
            <h3>Error loading users</h3>
            <p>{error}</p>
            <button onClick={fetchUsers} className="retry-btn">
              Retry
            </button>
          </div>
        ) : (
          <>
            {activeTab === "travelers" && renderTravelersTable()}
            {activeTab === "drivers" && renderDriversTable()}
            {activeTab === "guides" && renderGuidesTable()}
          </>
        )}
      </div>
    </div>
  );
};

export default UserManagement;

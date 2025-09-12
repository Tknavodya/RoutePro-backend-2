import React, { useState } from 'react';
import { FaHome, FaRoute, FaUsers, FaCog, FaBell, FaSignOutAlt, FaSearch, 
         FaChartLine, FaCalendarAlt, FaMapMarkerAlt, FaUserTie, FaCarAlt, FaStar } from 'react-icons/fa';
import './AdminDashboard.css';

const AdminDashboard = () => {
  const [activeTab, setActiveTab] = useState('dashboard');
  const [searchQuery, setSearchQuery] = useState('');
  
  // Mock notification data
  const notifications = [
    { id: 1, message: 'New driver registered', time: '10 mins ago' },
    { id: 2, message: 'Trip #1234 completed', time: '1 hour ago' },
    { id: 3, message: 'System maintenance scheduled', time: '2 hours ago' }
  ];

  // Mock trip data
  const trips = [
    { id: 1, tripId: 'T-1001', driver: 'John Smith', passenger: 'Emma Davis', status: 'Completed', date: '2025-08-07', amount: '$24.50', rating: 5 },
    { id: 2, tripId: 'T-1002', driver: 'Robert Johnson', passenger: 'Michael Brown', status: 'In Progress', date: '2025-08-07', amount: '$18.75', rating: null },
    { id: 3, tripId: 'T-1003', driver: 'Sarah Williams', passenger: 'David Wilson', status: 'Cancelled', date: '2025-08-06', amount: '$0.00', rating: null },
    { id: 4, tripId: 'T-1004', driver: 'Jennifer Lee', passenger: 'James Taylor', status: 'Completed', date: '2025-08-06', amount: '$32.20', rating: 4 },
    { id: 5, tripId: 'T-1005', driver: 'Chris Evans', passenger: 'Lisa Anderson', status: 'Completed', date: '2025-08-05', amount: '$15.30', rating: 5 }
  ];

  // Monthly performance data
  const performanceData = {
    revenue: [12500, 13200, 14800, 13900, 15200, 16300],
    trips: [410, 425, 460, 450, 480, 510],
    newUsers: [85, 92, 78, 84, 96, 105],
    months: ['Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug']
  };

  return (
    <div className="admin-dashboard-container">
      {/* Sidebar */}
      <div className="admin-sidebar">
        <div className="logo">
          <h2>Route Pro</h2>
        </div>
        <ul className="nav-links">
          <li className={activeTab === 'dashboard' ? 'active' : ''} onClick={() => setActiveTab('dashboard')}>
            <FaHome /> <span>Dashboard</span>
          </li>
          <li className={activeTab === 'trips' ? 'active' : ''} onClick={() => setActiveTab('trips')}>
            <FaRoute /> <span>Trips</span>
          </li>
          <li className={activeTab === 'users' ? 'active' : ''} onClick={() => setActiveTab('users')}>
            <FaUsers /> <span>Users</span>
          </li>
          <li className={activeTab === 'settings' ? 'active' : ''} onClick={() => setActiveTab('settings')}>
            <FaCog /> <span>Settings</span>
          </li>
        </ul>
        <div className="sidebar-footer">
          <button className="logout-btn"><FaSignOutAlt /> Logout</button>
        </div>
      </div>

      {/* Main Content Area */}
      <div className="main-content">
        {/* Header */}
        <header className="admin-header">
          <div className="search-bar">
            <FaSearch />
            <input 
              type="text" 
              placeholder="Search trips, users..." 
              value={searchQuery}
              onChange={(e) => setSearchQuery(e.target.value)}
            />
          </div>
          <div className="admin-profile">
            <div className="notifications">
              <FaBell />
              <span className="notification-count">{notifications.length}</span>
              <div className="notification-dropdown">
                <h4>Notifications</h4>
                {notifications.map(notification => (
                  <div key={notification.id} className="notification-item">
                    <p>{notification.message}</p>
                    <span>{notification.time}</span>
                  </div>
                ))}
              </div>
            </div>
            <div className="admin-info">
              <span>Admin User</span>
              <img src="https://via.placeholder.com/40" alt="Admin" className="admin-avatar" />
            </div>
          </div>
        </header>

        {/* Dashboard Content */}
        <div className="dashboard-content">
          <h1>Admin Dashboard</h1>
          
          {/* Quick Stats Cards */}
          <div className="stats-cards">
            <div className="stat-card">
              <div className="stat-icon">
                <FaRoute />
              </div>
              <div className="stat-details">
                <h3>Total Trips</h3>
                <p className="stat-number">1,254</p>
                <p className="stat-trend positive">+12% from last month</p>
              </div>
            </div>
            <div className="stat-card">
              <div className="stat-icon">
                <FaCarAlt />
              </div>
              <div className="stat-details">
                <h3>Active Drivers</h3>
                <p className="stat-number">48</p>
                <p className="stat-trend positive">+5% from last month</p>
              </div>
            </div>
            <div className="stat-card">
              <div className="stat-icon">
                <FaChartLine />
              </div>
              <div className="stat-details">
                <h3>Revenue</h3>
                <p className="stat-number">$12,458</p>
                <p className="stat-trend positive">+8% from last month</p>
              </div>
            </div>
            <div className="stat-card">
              <div className="stat-icon">
                <FaStar />
              </div>
              <div className="stat-details">
                <h3>Customer Satisfaction</h3>
                <p className="stat-number">4.8/5</p>
                <p className="stat-trend neutral">No change from last month</p>
              </div>
            </div>
          </div>
          
          {/* Metrics Summary Section */}
          <div className="metrics-section">
            <h2>Performance Metrics</h2>
            <div className="metrics-container">
              <div className="metrics-chart">
                <h3>Monthly Revenue</h3>
                <div className="chart-container">
                  {performanceData.revenue.map((value, index) => (
                    <div key={index} className="chart-bar-container">
                      <div 
                        className="chart-bar" 
                        style={{ height: `${(value / Math.max(...performanceData.revenue)) * 100}%` }}
                      ></div>
                      <span className="chart-label">{performanceData.months[index]}</span>
                    </div>
                  ))}
                </div>
              </div>
              <div className="metrics-summary">
                <div className="metric-item">
                  <div className="metric-icon">
                    <FaCalendarAlt />
                  </div>
                  <div className="metric-content">
                    <h4>Average Daily Trips</h4>
                    <p>17.2</p>
                    <span className="metric-change positive">+2.3 from last week</span>
                  </div>
                </div>
                <div className="metric-item">
                  <div className="metric-icon">
                    <FaMapMarkerAlt />
                  </div>
                  <div className="metric-content">
                    <h4>Popular Destinations</h4>
                    <p>Downtown (34%)</p>
                    <span className="metric-secondary">Airport (27%)</span>
                  </div>
                </div>
                <div className="metric-item">
                  <div className="metric-icon">
                    <FaUserTie />
                  </div>
                  <div className="metric-content">
                    <h4>Top Drivers</h4>
                    <p>John Smith (32 trips)</p>
                    <span className="metric-secondary">Sarah Williams (28 trips)</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
          
          {/* Trips Table Section */}
          <div className="trips-section">
            <div className="section-header">
              <h2>Recent Trips</h2>
              <button className="view-all-btn">View All</button>
            </div>
            <div className="trips-table-container">
              <table className="trips-table">
                <thead>
                  <tr>
                    <th>Trip ID</th>
                    <th>Driver</th>
                    <th>Passenger</th>
                    <th>Date</th>
                    <th>Status</th>
                    <th>Amount</th>
                    <th>Rating</th>
                  </tr>
                </thead>
                <tbody>
                  {trips.map(trip => (
                    <tr key={trip.id} className={`status-${trip.status.toLowerCase().replace(' ', '-')}`}>
                      <td>{trip.tripId}</td>
                      <td>{trip.driver}</td>
                      <td>{trip.passenger}</td>
                      <td>{trip.date}</td>
                      <td>
                        <span className={`status-badge ${trip.status.toLowerCase().replace(' ', '-')}`}>
                          {trip.status}
                        </span>
                      </td>
                      <td>{trip.amount}</td>
                      <td>
                        {trip.rating ? (
                          <div className="rating">
                            {[...Array(5)].map((_, i) => (
                              <span key={i} className={i < trip.rating ? 'star filled' : 'star'}>★</span>
                            ))}
                          </div>
                        ) : (
                          <span className="no-rating">N/A</span>
                        )}
                      </td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
};

export default AdminDashboard;
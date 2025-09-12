"use client"
import "./Sidebar.css"

const Sidebar = ({ currentPage, setCurrentPage }) => {
  const menuItems = [
    { id: "dashboard", label: "Dashboard", icon: "🏠" },
    { id: "trips", label: "Trips", icon: "📍" },
    { id: "users", label: "Users", icon: "👥" },
    { id: "notifications", label: "Notifications", icon: "🔔" },
  ]

  return (
    <div className="sidebar">
      <div className="sidebar-header">
        <div className="logo">
          <span className="logo-icon">📊</span>
          <div className="logo-text">
            <h2>TravelAdmin</h2>
            <p>Admin Dashboard</p>
          </div>
        </div>
      </div>

      <nav className="sidebar-nav">
        <div className="nav-section">
          <h3>Navigation</h3>
          <ul>
            {menuItems.map((item) => (
              <li key={item.id}>
                <button
                  className={`nav-item ${currentPage === item.id ? "active" : ""}`}
                  onClick={() => setCurrentPage(item.id)}
                >
                  <span className="nav-icon">{item.icon}</span>
                  <span className="nav-label">{item.label}</span>
                </button>
              </li>
            ))}
          </ul>
        </div>
      </nav>
    </div>
  )
}

export default Sidebar

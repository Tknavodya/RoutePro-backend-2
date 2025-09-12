import "./TripManagement.css"

const TripManagement = () => {
  const trips = [
    {
      id: "TR001",
      status: "completed",
      route: { from: "Colombo", to: "Kandy" },
      date: "2024-01-15",
      duration: "2 days",
      amount: 15000,
      traveler: {
        name: "John Doe",
        email: "john@example.com",
        phone: "+94 77 123 4567",
      },
      driver: {
        name: "Kasun Silva",
        phone: "+94 77 987 6543",
        rating: 4.8,
      },
      guide: {
        name: "Nimal Perera",
        phone: "+94 77 456 7890",
        rating: 4.6,
      },
      attractions: ["Temple of Tooth", "Royal Botanical Gardens"],
    },
    {
      id: "TR002",
      status: "ongoing",
      route: { from: "Colombo", to: "Galle" },
      date: "2024-01-20",
      duration: "1 day",
      amount: 12000,
      traveler: {
        name: "Jane Smith",
        email: "jane@example.com",
        phone: "+94 77 234 5678",
      },
      driver: {
        name: "Sunil Fernando",
        phone: "+94 77 876 5432",
        rating: 2.1,
      },
      guide: null,
      attractions: [],
    },
  ]

  return (
    <div className="trip-management">
      <div className="page-header">
        <h2>Trip Management</h2>
        <div className="header-controls">
          <div className="search-bar">
            <input type="text" placeholder="Search trips..." />
            <button>🔍</button>
          </div>
          <select className="status-filter">
            <option>All Status</option>
            <option>Completed</option>
            <option>Ongoing</option>
            <option>Cancelled</option>
          </select>
        </div>
      </div>

      <div className="trip-list">
        {trips.map((trip) => (
          <div key={trip.id} className="trip-card">
            <div className="trip-header">
              <div className="trip-info">
                <div className="trip-id-section">
                  <span className="trip-id">{trip.id}</span>
                  <span className={`trip-status ${trip.status}`}>{trip.status}</span>
                </div>
                <div className="trip-route">
                  📍 {trip.route.from} → {trip.route.to}
                </div>
                <div className="trip-date">{trip.date}</div>
              </div>
              <div className="trip-amount">
                <div className="amount">Rs. {trip.amount.toLocaleString()}</div>
                <div className="duration">{trip.duration}</div>
              </div>
            </div>

            <div className="trip-participants">
              <div className="participant">
                <h4>👥 Traveler</h4>
                <div className="participant-info">
                  <div className="name">{trip.traveler.name}</div>
                  <div className="contact">{trip.traveler.email}</div>
                  <div className="contact">{trip.traveler.phone}</div>
                </div>
              </div>

              <div className="participant">
                <h4>🚗 Driver</h4>
                <div className="participant-info">
                  <div className="name">
                    {trip.driver.name}
                    <span className="rating">⭐ {trip.driver.rating}</span>
                    {trip.driver.rating < 3 && <span className="warning">⚠️</span>}
                  </div>
                  <div className="contact">{trip.driver.phone}</div>
                </div>
              </div>

              <div className="participant">
                <h4>🗺️ Guide</h4>
                <div className="participant-info">
                  {trip.guide ? (
                    <>
                      <div className="name">
                        {trip.guide.name}
                        <span className="rating">⭐ {trip.guide.rating}</span>
                      </div>
                      <div className="contact">{trip.guide.phone}</div>
                    </>
                  ) : (
                    <div className="no-guide">No guide assigned</div>
                  )}
                </div>
              </div>
            </div>

            {trip.attractions.length > 0 && (
              <div className="attractions">
                <h4>Attractions</h4>
                <div className="attraction-tags">
                  {trip.attractions.map((attraction, index) => (
                    <span key={index} className="attraction-tag">
                      {attraction}
                    </span>
                  ))}
                </div>
              </div>
            )}
          </div>
        ))}
      </div>
    </div>
  )
}

export default TripManagement

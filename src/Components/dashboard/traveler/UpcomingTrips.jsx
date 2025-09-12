import React from 'react';
import styles from './UpcomingTrips.module.css';

const trips = [
  { 
    id: 'T001', 
    destination: 'Sigiriya Rock Fortress', 
    driver: 'John Silva', 
    date: 'Mar 15, 2024', 
    time: '9:00 AM', 
    duration: '4 hours',
    fee: 'Rs. 5,500', 
    status: 'Confirmed',
    vehicle: 'Toyota Prius',
    pickup: 'Colombo Fort'
  },
  { 
    id: 'T002', 
    destination: 'Kandy Temple Tour', 
    driver: 'Priya Fernando', 
    date: 'Mar 18, 2024', 
    time: '11:30 AM', 
    duration: '6 hours',
    fee: 'Rs. 7,200', 
    status: 'Pending',
    vehicle: 'Honda Vezel',
    pickup: 'Kandy Railway Station'
  },
  { 
    id: 'T003', 
    destination: 'Galle Fort & Beach', 
    driver: 'Ruwan Perera', 
    date: 'Mar 22, 2024', 
    time: '2:00 PM', 
    duration: '8 hours',
    fee: 'Rs. 9,800', 
    status: 'Confirmed',
    vehicle: 'Toyota KDH Van',
    pickup: 'Mount Lavinia Hotel'
  }
];

const UpcomingTrips = () => (
  <section className={styles.upcomingTrips}>
    <div className={styles.sectionHeader}>
      <h3>Upcoming Trips</h3>
      <span className={styles.tripCount}>{trips.length}</span>
    </div>
    
    <div className={styles.tripGrid}>
      {trips.map(trip => (
        <div key={trip.id} className={styles.tripCard}>
          <div className={styles.tripCardHeader}>
            <span className={`${styles.statusBadge} ${styles[trip.status.toLowerCase()]}`}>
              {trip.status}
            </span>
            <span className={styles.tripId}>{trip.id}</span>
          </div>
          
          <div className={styles.tripInfo}>
            <h4 className={styles.destination}>{trip.destination}</h4>
            <p><strong>Driver:</strong> {trip.driver}</p>
            <p><strong>Date:</strong> {trip.date}</p>
            <p><strong>Time:</strong> {trip.time}</p>
            <p><strong>Duration:</strong> {trip.duration}</p>
            <p><strong>Vehicle:</strong> {trip.vehicle}</p>
            <p><strong>Pickup:</strong> {trip.pickup}</p>
            <p className={styles.fee}><strong>Total Fee:</strong> {trip.fee}</p>
          </div>
          
          <div className={styles.tripActions}>
            <button className={styles.primaryBtn}>View Details</button>
            <button className={styles.secondaryBtn}>Contact Driver</button>
            <button className={styles.dangerBtn}>Cancel Trip</button>
          </div>
        </div>
      ))}
    </div>
  </section>
);

export default UpcomingTrips;

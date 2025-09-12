import React from 'react';
import { useNavigate } from 'react-router-dom';
import styles from './QuickActions.module.css';

const QuickActions = () => {
  const navigate = useNavigate();

  const handlePlanTrip = () => {
    navigate('/route');
  };

  return (
    <section className={styles.quickActions}>
      <h3>Quick Actions</h3>
      <div className={styles.actionContainer}>
        <button className={styles.actionBtn} onClick={handlePlanTrip}>
          <span className={styles.actionIcon}>🗺️</span>
          Plan New Trip
        </button>
      </div>
    </section>
  );
};

export default QuickActions;

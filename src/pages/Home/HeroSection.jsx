import React from 'react';
import './HeroSection.css';
import CountUp from '../../Components/CountUp'; // your custom CountUp or react-countup?

const HeroSection = () => {
  return (
    <div className="hero-section">
      <div className="hero-buttons">
        <button className="primary-btn">Start Planning Your Trip</button>
        <button className="secondary-btn">Explore Attractions</button>
      </div>

      <div className="stats">
        <div className="stat-card feature-card">
          <h2>
            <CountUp from={0} to={500} duration={2} separator="," />+
          </h2>
          <p>Tourist Destinations</p>
        </div>

        <div className="stat-card feature-card">
          <h2>
            <CountUp from={0} to={10000} duration={2.5} separator="," />+
          </h2>
          <p>Happy Travelers</p>
        </div>

        <div className="stat-card feature-card">
          <h2>
            <CountUp from={0} to={50} duration={1.5} separator="," />+
          </h2>
          <p>Cultural Events</p>
        </div>
      </div>
    </div>
  );
};

export default HeroSection;

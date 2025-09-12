import React from 'react';
import './FeaturesSection.css';
import { FaRoute, FaMapMarkedAlt, FaLeaf, FaTheaterMasks, FaMoneyBillWave, FaUserTie, FaUtensils, FaCloudSun } from 'react-icons/fa';



const FeaturesSection = () => {
  return (
    <section className="features-section">
      <h1> <span className="green-text">Everything You Need for the Perfect Trip</span></h1>
      <p className="description">RoutePro combines intelligent planning with local expertise to create unforgettable travel experiences across Sri Lanka.</p>

      <div className="features-grid">
  <FeatureCard
    icon={<FaRoute />}
    title="Smart Route Planning"
    desc="Find the shortest and most efficient routes between destinations with real-time traffic updates."
    color="green"
  />
  <FeatureCard
    icon={<FaMapMarkedAlt />}
    title="Tourist Attractions"
    desc="Discover hidden gems and popular tourist spots along your route with detailed information."
    color="green"
  />
  <FeatureCard
    icon={<FaLeaf />}
    title="Eco-Friendly Travel"
    desc="Explore Sri Lanka sustainably with eco-tour options and tips for reducing your travel footprint."
    color="green"
  />
  <FeatureCard
  icon={<FaTheaterMasks />}
  title="Cultural Experiences"
  desc="Learn about local festivals, traditions, and authentic Sri Lankan cultural experiences."
  color="green"
/>

  <FeatureCard
    icon={<FaMoneyBillWave />}
    title="Budget Packages"
    desc="Affordable plans that fit your travel budget without compromising on experience."
    color="green"
  />
  <FeatureCard
    icon={<FaUserTie />}
    title="Guide Booking"
    desc="Hire professional local guides to enhance your journey with authentic insights."
    color="green"
  />
  <FeatureCard
    icon={<FaUtensils />}
    title="Local Cuisine"
    desc="Taste traditional Sri Lankan dishes recommended by local experts."
    color="green"
  />
  <FeatureCard
    icon={<FaCloudSun />}
    title="Real-Time Weather Updates"
    desc="Stay informed with live weather forecasts for your destinations to plan each day perfectly."
    color="green"
  />
</div>


    </section>
  );
};

const FeatureCard = ({ icon, title, desc, color }) => (
  <div className="feature-card">
    <div className={`icon-wrapper ${color}`}>
      {icon}
    </div>
    <h3>{title}</h3>
    <p>{desc}</p>
  </div>
);

export default FeaturesSection;

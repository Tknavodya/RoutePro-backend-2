import React from 'react';
import './LocalEventsFoods.css';
import { useNavigate } from 'react-router-dom';

const LocalEventsFoods = () => {
  const navigate = useNavigate(); // ✅ Initialize the hook

  const handleExploreClick = () => {
    navigate('/culture'); // ✅ Navigate to your /culture page
  };

  return (
    <section className="events-section">
      <div className="overlay">
        <div className="content">
          <h1>
            <span className="highlight-teal">Explore Local Events & Foods</span>{' '}
            
          </h1>
          <p className="description">
          Local events bring people together to celebrate culture and community. Festivals, markets, and traditional foods keep ancient customs alive with timeless flavors.          </p>
          <button className="explore-btn" onClick={handleExploreClick}>
            Explore More
          </button>

          <div className="food-cards">
            <div className="food-card">
              <img src="/images/milk rice.jpg" alt="Meal 1" />
              <p>Milk Rice (Kiribath)</p>
            </div>
            <div className="food-card">
              <img src="/images/pittu.jpg" alt="Meal 2" />
              <p>Pittu</p>
            </div>
            <div className="food-card">
              <img src="/images/rice and curry.jpg" alt="Meal 3" />
              <p>Rice and Curry</p>
            </div>
            <div className="food-card">
              <img src="/images/navam.jpg" alt="Meal 4" />
              <p>Navam Perahara</p>
            </div>
            <div className="food-card">
              <img src="/images/vesak-festival.jpg" alt="Meal 5" />
              <p>Vesak Festival</p>
            </div>
          </div>
        </div>
      </div>
    </section>
  );
};

export default LocalEventsFoods;

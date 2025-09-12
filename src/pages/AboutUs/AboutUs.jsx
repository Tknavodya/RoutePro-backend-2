import React from "react";
import "./aboutus.css";


export default function AboutUs() {
  return (
    <div className="about-container">
      <div className="about-left">
        <img
          src="/images/aboutus.jpg"
          alt="About RoutePro"
          className="about-image"
        />
      </div>
      <div className="about-right">
        <h1>About Us</h1>
        <h2>Your All-in-One Travel Planner & Sightseeing Guide</h2>

        <h3>Get to Know Us</h3>
        <p>
          RoutePro is an all-in-one digital trip planning platform designed to make exploring Sri Lanka seamless, scenic, and culturally immersive. Whether you're mapping out a road trip, booking trusted local services, or uncovering hidden gems across the island, RoutePro simplifies every step from route planning and vehicle booking to food trails and festival highlights.
        </p>

        <h3>Our Mission</h3>
        <p>
          To develop a unified, user-friendly travel planner that streamlines journey planning and promotes immersive cultural experiences across Sri Lanka.
        </p>

        <h3>Our Objectives</h3>
        <ul>
          <li>Centralize route planning, vehicle booking, guide services, and sightseeing discovery</li>
          <li>Provide flexible, budget-conscious travel packages for different user preferences</li>
          <li>Enable travelers to discover district-specific foods and traditional festivals</li>
          <li>Integrate smart routing with scenic and cultural attraction highlights</li>
          <li>Support user feedback through ratings and review systems</li>
          <li>Empower local service providers through a digital marketplace</li>
        </ul>
      </div>
    </div>
  );
}

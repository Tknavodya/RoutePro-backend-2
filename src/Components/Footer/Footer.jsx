import React from "react";
import "./Footer.css";
import {
  FaFacebookF,
  FaTwitter,
  FaTiktok,
  FaYoutube,
  FaEnvelope,
  FaPhoneAlt,
} from "react-icons/fa";
import { Link } from "react-router-dom";

const Footer = () => {
  return (
    <footer className="footer">
      <div className="footer-container">
        <div className="footer-section about">
          <h2 className="logo">
            <span className="routepro-brand">Route</span>
            <span className="yellow">Pro.</span>
          </h2>
          <p>
            Your ultimate travel companion for exploring Sri Lanka. Discover
            amazing destinations, plan efficient routes, and experience
            authentic local culture.
          </p>
          <div className="social-icons">
            <FaFacebookF />
            <FaTwitter />
            <FaTiktok />
            <FaYoutube />
          </div>
        </div>

       <div className="footer-section links">
          <h4>Quick Links</h4>
          <ul>
            <li>
              <Link to="/route">Route Planning</Link>
            </li>
            <li>
              <Link to="/homepage">Attractions</Link>
            </li>
            <li>
              <Link to="/culture">Culture</Link> {/* ✅ Use React Router Link */}
            </li>
            <li>
              <Link to="/budget">Packages</Link>
            </li>
          </ul>
        </div>

        <div className="footer-section info">
          <h4>Information</h4>
          <ul>
            <li>
              <a href="aboutus">About Us</a>
            </li>
            <li>
              <a href="termsconditions">Terms and Conditions</a>
              
            </li>
            <li>
              <a href="privacypolicy">Privacy Policy</a>
            </li>
            <li>
              <a href="contactus">Contact Us</a>
            </li>
          </ul>
        </div>

        <div className="footer-section contact">
          <h4>Contact</h4>
          <p>
            <FaEnvelope className="icon" /> info@routepro.lk
          </p>
          <p>
            <FaPhoneAlt className="icon" /> +94 11 234 567
          </p>
        </div>

        <div className="footer-bottom">
      <p>
        © 2025 RoutePro All Rights Reserved.
     </p>
        </div>
      </div>
    </footer>
  );
};

export default Footer;
import React from "react";
import "./contactus.css";


export default function ContactUs() {
  return (
    <div className="contact-container">
      <div className="contact-left">
        <img
          src="/images/contactus.jpg"
          alt="Contact Us"
          className="contact-image"
        />
      </div>
      <div className="contact-right">
        <h1>Contact Us</h1>
        <p>We’re here to help you with support, partnerships, security, and investment inquiries.</p>

        <h3>Member Relations & Support</h3>
        <p>Email: <a href="mailto:support@routepro.com">support@routepro.com</a></p>

        <h3>Partnerships & Brand Enquiries</h3>
        <p>Email: <a href="mailto:partnerships@routepro.com">partnerships@routepro.com</a></p>

        <h3>Report a Security Issue</h3>
        <p>Email: <a href="mailto:security@routepro.com">security@routepro.com</a></p>

        <h3>Investors</h3>
        <p>Website: <a href="https://www.routeproinvest.com" target="_blank" rel="noopener noreferrer">www.routeproinvest.com</a></p>
        <p>Email: <a href="mailto:investors@routepro.com">investors@routepro.com</a></p>
      </div>
    </div>
  );
}

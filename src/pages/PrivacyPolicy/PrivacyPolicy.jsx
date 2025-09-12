import React from "react";
import "./PrivacyPolicy.css";

export default function PrivacyPolicy() {
  return (
    <div className="privacy-page">
      

      <main className="privacy-main">
        <div className="privacy-left">
          <img
            src="/images/privacy.jpg"
            alt="Privacy"
            className="privacy-image"
          />
        </div>

        <div className="privacy-right">
          <h2 className="privacy-title">Privacy Policy</h2>

          <div className="privacy-card">
            <h3>Information We Collect</h3>
            <ul>
              <li>Personal details like name, email, phone number, and location when you register.</li>
              <li>Trip preferences, booking details, and ratings you provide.</li>
              <li>Technical data such as IP address, browser type, and usage patterns.</li>
            </ul>
          </div>

          <div className="privacy-card">
            <h3>How We Use Your Information</h3>
            <ul>
              <li>To manage user accounts and process bookings.</li>
              <li>To match travelers with drivers and guides based on trip needs.</li>
              <li>To improve the quality and security of our platform.</li>
              <li>To send trip updates, notifications, or support messages.</li>
            </ul>
          </div>

          <div className="privacy-card">
            <h3>Data Sharing</h3>
            <p>We do not sell or rent your information. Data is only shared with:</p>
            <ul>
              <li>Verified drivers or guides when you make a booking.</li>
              <li>Service providers that help us run RoutePro (e.g., email services, payment processors).</li>
              <li>Authorities, if required by law or to enforce our Terms of Service.</li>
            </ul>
          </div>

          <div className="privacy-card">
            <h3>Your Privacy Rights</h3>
            <ul>
              <li>You can update or delete your personal information from your account settings.</li>
              <li>You may request to deactivate your account at any time.</li>
            </ul>
          </div>

          <div className="privacy-card">
            <h3>Data Security</h3>
            <p>We use secure technologies like HTTPS and encrypted databases to protect your personal data from unauthorized access.</p>
          </div>

          <div className="privacy-card">
            <h3>Cookies</h3>
            <p>We use cookies to improve your experience on our website. You can disable cookies in your browser settings.</p>
          </div>

          <div className="privacy-card">
            <h3>Contact</h3>
            <p>If you have any questions, please email us at: <a href="mailto:support@routepro.com">support@routepro.com</a></p>
          </div>
        </div>
      </main>

     
    </div>
  );
}

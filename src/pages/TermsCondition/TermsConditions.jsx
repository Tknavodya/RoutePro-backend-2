import React from "react";
import "./termsconditions.css";


export default function TermsAndConditions() {
  return (
    <div className="terms-container">
      <div className="terms-left">
        <img
          src="/images/terms.jpg"
          alt="Terms and Conditions"
          className="terms-image"
        />
      </div>
      <div className="terms-right">
        <h1>Terms and Conditions</h1>
        <p>Welcome to RoutePro! By using our platform, you agree to the following terms:</p>

        <h3>Use of the Platform</h3>
        <p>RoutePro is a travel planning platform that connects travelers with verified drivers and tour guides. You can use our services to:</p>
        <ul>
          <li>Plan routes across Sri Lanka</li>
          <li>Book vehicles and guides</li>
          <li>Explore recommended sightseeing spots</li>
        </ul>

        <h3>User Responsibilities</h3>
        <ul>
          <li>You must be 18 years or older to create an account.</li>
          <li>You agree to provide accurate, complete, and updated information.</li>
          <li>You are responsible for keeping your account login information secure.</li>
        </ul>

        <h3>Bookings and Payments</h3>
        <ul>
          <li>All bookings are confirmed directly between the traveler and the service provider (driver or guide).</li>
          <li>Payments must be processed through the RoutePro system.</li>
          <li>Cancellations and refunds will follow our platform’s policy.</li>
        </ul>

        <h3>Ratings and Account Monitoring</h3>
        <ul>
          <li>After a trip, travelers can rate and review the service.</li>
          <li>Any driver or guide who receives an average rating below 2.5 will be flagged for admin review.</li>
          <li>Accounts that consistently perform poorly may be suspended or removed from the system.</li>
        </ul>

        <h3>Account Termination</h3>
        <p>We reserve the right to suspend or delete any account that violates our terms or receives repeated complaints or low ratings.</p>

        <h3>Data Privacy</h3>
        <p>Your personal information is protected under our Privacy Policy. We do not share or sell your data to third parties.</p>

        <h3>Contact Us</h3>
        <p>If you have any questions or concerns, please contact us at: <a href="mailto:support@routepro.com">support@routepro.com</a></p>
      </div>
    </div>
  );
}

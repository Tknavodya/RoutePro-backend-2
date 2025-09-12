import React, { useState, useEffect } from "react";
import { useLocation, useNavigate, Link } from "react-router-dom";
import axios from "axios";
import "./VerifyOTPPage.css";

const VerifyOTPPage = () => {
  const [otp, setOtp] = useState(["", "", "", "", "", ""]);
  const [loading, setLoading] = useState(false);
  const [message, setMessage] = useState("");
  const [error, setError] = useState("");
  const [resendLoading, setResendLoading] = useState(false);
  const [timeLeft, setTimeLeft] = useState(300); // 5 minutes
  const [email, setEmail] = useState("");
  
  const location = useLocation();
  const navigate = useNavigate();

  useEffect(() => {
    // Get email from navigation state
    const state = location.state;
    if (!state?.email) {
      navigate("/forgot-password");
      return;
    }
    setEmail(state.email);
  }, [location, navigate]);

  useEffect(() => {
    // Countdown timer
    if (timeLeft > 0) {
      const timer = setTimeout(() => setTimeLeft(timeLeft - 1), 1000);
      return () => clearTimeout(timer);
    }
  }, [timeLeft]);

  const handleOtpChange = (index, value) => {
    if (value.length > 1) return; // Only allow single digit
    
    const newOtp = [...otp];
    newOtp[index] = value;
    setOtp(newOtp);

    // Auto-focus next input
    if (value && index < 5) {
      const nextInput = document.getElementById(`otp-${index + 1}`);
      if (nextInput) nextInput.focus();
    }
  };

  const handleKeyDown = (index, e) => {
    // Handle backspace
    if (e.key === "Backspace" && !otp[index] && index > 0) {
      const prevInput = document.getElementById(`otp-${index - 1}`);
      if (prevInput) prevInput.focus();
    }
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    setLoading(true);
    setMessage("");
    setError("");

    const otpCode = otp.join("");
    if (otpCode.length !== 6) {
      setError("Please enter all 6 digits of the OTP.");
      setLoading(false);
      return;
    }

    try {
      const response = await axios.post(
        "http://localhost/RoutePro-backend(02)/public/auth/verify-otp",
        {
          email,
          otp: otpCode
        },
        {
          headers: {
            'Content-Type': 'application/json',
          },
          timeout: 10000,
        }
      );

      if (response.data.success) {
        setMessage("OTP verified successfully! Redirecting...");
        // Navigate to reset password with token immediately
        navigate(`/reset-password?token=${response.data.token}`);
      } else {
        setError(response.data.message || "Invalid OTP. Please try again.");
      }
    } catch (err) {
      console.error("OTP verification error:", err);
      if (err.response?.data?.message) {
        setError(err.response.data.message);
      } else {
        setError("An error occurred. Please try again.");
      }
    } finally {
      setLoading(false);
    }
  };

  const handleResendOTP = async () => {
    setResendLoading(true);
    setMessage("");
    setError("");

    try {
      const response = await axios.post(
        "http://localhost/RoutePro-backend(02)/public/auth/resend-otp",
        { email },
        {
          headers: {
            'Content-Type': 'application/json',
          },
          timeout: 10000,
        }
      );

      if (response.data.success) {
        setMessage("New OTP sent to your email!");
        setTimeLeft(300); // Reset timer
        setOtp(["", "", "", "", "", ""]); // Clear OTP inputs
      } else {
        setError(response.data.message || "Failed to resend OTP.");
      }
    } catch (err) {
      console.error("Resend OTP error:", err);
      setError("Failed to resend OTP. Please try again.");
    } finally {
      setResendLoading(false);
    }
  };

  const formatTime = (seconds) => {
    const mins = Math.floor(seconds / 60);
    const secs = seconds % 60;
    return `${mins}:${secs.toString().padStart(2, '0')}`;
  };

  return (
    <div className="verify-otp-container">
      <div className="verify-otp-box">
        <div className="verify-otp-header">
          <h2>Verify OTP</h2>
          <p>We've sent a 6-digit verification code to</p>
          <strong>{email}</strong>
        </div>

        <form onSubmit={handleSubmit} className="verify-otp-form">
          <div className="otp-inputs">
            {otp.map((digit, index) => (
              <input
                key={index}
                id={`otp-${index}`}
                type="text"
                value={digit}
                onChange={(e) => handleOtpChange(index, e.target.value)}
                onKeyDown={(e) => handleKeyDown(index, e)}
                maxLength="1"
                className="otp-input"
                disabled={loading}
                autoComplete="off"
              />
            ))}
          </div>

          <div className="timer-section">
            {timeLeft > 0 ? (
              <p className="timer">
                Code expires in: <span className="time-left">{formatTime(timeLeft)}</span>
              </p>
            ) : (
              <p className="expired">Code has expired</p>
            )}
          </div>

          {error && <div className="error-message">{error}</div>}
          {message && <div className="success-message">{message}</div>}

          <button
            type="submit"
            className={`submit-btn ${loading ? "loading" : ""}`}
            disabled={loading || timeLeft === 0}
          >
            {loading ? "Verifying..." : "Verify OTP"}
          </button>

          <div className="resend-section">
            <button
              type="button"
              onClick={handleResendOTP}
              className="resend-btn"
              disabled={resendLoading || timeLeft > 240} // Allow resend after 1 minute
            >
              {resendLoading ? "Sending..." : "Resend OTP"}
            </button>
          </div>
        </form>

        <div className="verify-otp-footer">
          <p>
            Wrong email?{" "}
            <Link to="/forgot-password" className="back-link">
              Go back
            </Link>
          </p>
        </div>
      </div>
    </div>
  );
};

export default VerifyOTPPage;

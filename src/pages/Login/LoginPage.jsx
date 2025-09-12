// LoginPage.js
import React, { useState, useEffect } from "react";
import "./LoginPage.css";
import axios from "axios";
import { useNavigate, Link } from "react-router-dom";

const LoginPage = () => {
  const [email, setEmail] = useState("");
  const [password, setPassword] = useState("");
  const [loading, setLoading] = useState(false);
  const navigate = useNavigate();

  // Check if user is already logged in and redirect accordingly
  useEffect(() => {
    const checkAuthStatus = () => {
      const userId = localStorage.getItem("userId");
      const role = localStorage.getItem("role");
      const sessionStartTime = localStorage.getItem("sessionStartTime");

      // Check if user is logged in and session is valid
      if (userId && role && sessionStartTime) {
        // Optional: Check session timeout (24 hours = 86400000 ms)
        const sessionAge = Date.now() - parseInt(sessionStartTime);
        const sessionLimit = 24 * 60 * 60 * 1000; // 24 hours

        if (sessionAge < sessionLimit) {
          // User is logged in with valid session, redirect to appropriate dashboard
          console.log("User already logged in, redirecting to dashboard");
          
          // Use replace instead of navigate to prevent going back to login
          if (role === "driver") {
            navigate("/driver-dashboard", { replace: true });
          } else if (role === "guide") {
            navigate("/guide-dashboard", { replace: true });
          } else if (role === "traveller") {
            navigate("/traveller-dashboard", { replace: true });
          } else if (role === "admin") {
            navigate("/admin-dashboard", { replace: true });
          } else {
            navigate("/", { replace: true });
          }
        } else {
          // Session expired, clear localStorage
          console.log("Session expired, clearing authentication data");
          localStorage.removeItem("userId");
          localStorage.removeItem("role");
          localStorage.removeItem("userName");
          localStorage.removeItem("userEmail");
          localStorage.removeItem("userRating");
          localStorage.removeItem("sessionStartTime");
          localStorage.removeItem("userProfile");
        }
      }
    };

    checkAuthStatus();
  }, [navigate]);

  const handleLogin = async (e) => {
    e.preventDefault();
    setLoading(true);

    try {
      console.log("Attempting login with:", { email });

      // Try the new MVC API endpoint first, then fallback to legacy
      let response;
      try {
        response = await axios.post(
          "http://localhost/RoutePro-backend(02)/public/index.php/auth/login", 
          {
            email,
            password,
          },
          {
            headers: {
              'Content-Type': 'application/json',
            },
            withCredentials: true, // Important: include cookies for session
            timeout: 10000,
          }
        );
      } catch (newApiError) {
        console.log("New API failed, trying legacy endpoint...", newApiError.message);
        
        // Fallback to legacy endpoint
        response = await axios.post(
          "http://localhost/RoutePro-backend(02)/app/controllers/Login.php", 
          {
            email,
            password,
          },
          {
            headers: {
              'Content-Type': 'application/json',
            },
            withCredentials: true, // Important: include cookies for session
            timeout: 10000,
          }
        );
      }

      const result = response.data;
      console.log("Login response:", result);

      if (result.success) {
        // Store user data in localStorage for immediate access
        localStorage.setItem("userId", result.userId);
        localStorage.setItem("role", result.role);
        localStorage.setItem("userName", result.name);
        localStorage.setItem("userEmail", result.email);
        localStorage.setItem("userRating", result.rating || "0");
        localStorage.setItem("sessionStartTime", Date.now().toString()); // Add session timestamp
        
        // Store additional profile data if available
        if (result.user && result.user.profile) {
          localStorage.setItem("userProfile", JSON.stringify(result.user.profile));
        }

        console.log(`Login successful for ${result.role}: ${result.name}`);
        
        // Navigate based on role using the inheritance-based system
        if (result.role === "driver") {
          navigate("/driver-dashboard", { replace: true });
        } else if (result.role === "guide") {
          navigate("/guide-dashboard", { replace: true });
        } else if (result.role === "traveller") {
          navigate("/traveller-dashboard", { replace: true });
        } else if (result.role === "admin") {
          navigate("/admin-dashboard", { replace: true });
        } else {
          navigate("/", { replace: true });
        }
        
      } else {
        // Handle login failure
        const errorMessage = result.error || result.message || "Login failed";
        console.error("Login failed:", errorMessage);
        alert("Login failed: " + errorMessage);
      }
    } catch (error) {
      console.error("Login error:", error);
      
      // Enhanced error handling
      let errorMessage = "An unexpected error occurred. Please try again.";
      
      if (error.response) {
        // Server responded with error status
        const serverError = error.response.data;
        if (serverError && serverError.error) {
          errorMessage = serverError.error;
        } else if (serverError && serverError.message) {
          errorMessage = serverError.message;
        } else {
          errorMessage = `Server error (${error.response.status})`;
        }
      } else if (error.request) {
        // Network error
        errorMessage = "Network error. Please check your connection and try again.";
      } else if (error.code === 'ECONNABORTED') {
        // Timeout error
        errorMessage = "Request timeout. Please try again.";
      }
      
      alert(errorMessage);
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="login-container">
      <div className="login-left">
                 <img src="/images/login.jpg" alt="Train Scenic" />
      </div>
      <div className="login-right">
                                     <img className="logo-image" src="/images/new logo.png" alt="Logo" />
        <h2 className="welcome">Welcome</h2>
        <p className="login-subtitle">Login with Email</p>

        <form onSubmit={handleLogin}>
          <div className="form-group">
            <input
              type="email"
              placeholder="Email"
              value={email}
              onChange={(e) => setEmail(e.target.value)}
              required
              disabled={loading}
            />
          </div>
          <div className="form-group">
            <input
              type="password"
              placeholder="Password"
              value={password}
              onChange={(e) => setPassword(e.target.value)}
              required
              disabled={loading}
            />
          </div>
          <button type="submit" className="login-btn" disabled={loading}>
            {loading ? "LOGGING IN..." : "LOGIN"}
          </button>
          <Link className="forgot-link" to="/forgot-password">
            Forgot your password?
          </Link>
        </form>

      </div>
    </div>
  );
};

export default LoginPage;

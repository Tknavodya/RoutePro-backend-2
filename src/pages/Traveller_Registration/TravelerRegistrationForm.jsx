import React, { useState } from "react";
import "./TravelerRegistrationForm.css";
import axios from "axios";

// Create axios instance with default config (same as Driver form)
const api = axios.create({
  withCredentials: false,
  headers: {
    'Content-Type': 'application/json'
  }
});

export default function TravelerRegistrationForm() {
  const [form, setForm] = useState({
    name: "",
    email: "",
    phone: "",
    password: "",
    confirmPassword: "",
    agree: false,
  });

  const [loading, setLoading] = useState(false);

  const handleChange = (e) => {
    const { name, value, type, checked } = e.target;
    setForm({ ...form, [name]: type === 'checkbox' ? checked : value });
  };

  // 1. Full Name: Only letters and spaces allowed
  const validateName = (name) => /^[a-zA-Z\s]+$/.test(name);

  // 2. Email: Standard email format
  const validateEmail = (email) => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);

  // 3. Phone: Either 10 digits starting with 0 or 9 digits starting with 7
  const validatePhone = (phone) => {
    const cleaned = phone.replace(/[\s-]/g, "");
    return /^0\d{9}$/.test(cleaned) || /^7\d{8}$/.test(cleaned);
  };

  // 4. Password: Minimum 8 characters, at least 1 letter, 1 number, 1 special character
  const validatePassword = (password) =>
    /^(?=.*[A-Za-z])(?=.*\d)(?=.*[^A-Za-z\d]).{8,}$/.test(password);

  const handleSubmit = async (e) => {
    e.preventDefault();

    console.log('Form submission started');
    console.log('Current form state:', form);

    // Step 1: Terms agreement must be ticked
    if (!form.agree) {
      alert('You must agree to the Terms and Conditions and Privacy Policy.');
      return;
    }

    // Step 2: Validate each field in order (trim before validation)
    const trimmedName = form.name.trim();
    const trimmedEmail = form.email.trim();
    const trimmedPhone = form.phone.trim();
    
    if (!validateName(trimmedName)) {
      alert("Full name can only contain letters and spaces.");
      return;
    }

    if (!validateEmail(trimmedEmail)) {
      alert("Invalid email format.");
      return;
    }

    if (!validatePhone(trimmedPhone)) {
      alert("Phone number must be 10 digits starting with 0 or 9 digits starting with 7.");
      return;
    }

    if (!validatePassword(form.password)) {
      alert("Password must be at least 8 characters and include a letter, a number, and a special character.");
      return;
    }

    if (form.password !== form.confirmPassword) {
      alert("Passwords do not match.");
      return;
    }

    // Prepare data
    const { confirmPassword, agree, ...submitData } = form;
    
    // Trim all string fields to remove extra spaces
    submitData.name = submitData.name.trim();
    submitData.email = submitData.email.trim();
    submitData.phone = submitData.phone.trim();
    submitData.password = submitData.password.trim();
    
    // Add role to the payload - use 'traveller' to match backend
    submitData.role = 'traveller';
    
    console.log('Submitting data:', submitData);
    setLoading(true);

    try {
      // Try with fetch API first as it's more reliable
      console.log('Making fetch request...');
      
      const response = await fetch('http://localhost/RoutePro-backend(02)/public/auth/register', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Origin': window.location.origin
        },
        body: JSON.stringify(submitData)
      });

      console.log('Response received:', {
        status: response.status,
        statusText: response.statusText,
        ok: response.ok,
        headers: Object.fromEntries(response.headers.entries())
      });

      if (!response.ok) {
        // For 400 errors, try to get the server's error message
        const errorText = await response.text();
        console.log('Error response text:', errorText);
        
        let errorMessage = `HTTP error! status: ${response.status} - ${response.statusText}`;
        
        try {
          const errorData = JSON.parse(errorText);
          if (errorData.message) {
            errorMessage = errorData.message;
          }
        } catch (parseError) {
          console.log('Could not parse error response as JSON');
        }
        
        throw new Error(errorMessage);
      }

      const responseText = await response.text();
      console.log('Response text length:', responseText.length);
      console.log('Response text:', responseText);

      if (!responseText || responseText.trim() === '') {
        throw new Error('Empty response from server');
      }

      let data;
      try {
        data = JSON.parse(responseText);
        console.log('Successfully parsed JSON:', data);
      } catch (parseError) {
        console.error('JSON parse error:', parseError);
        console.error('Raw response that failed to parse:', responseText);
        throw new Error(`Invalid JSON response: ${parseError.message}`);
      }

      if (data.success) {
        alert("Traveler registered successfully!");
        setForm({
          name: "",
          email: "",
          phone: "",
          password: "",
          confirmPassword: "",
          agree: false,
        });
        // Redirect to login page after successful registration
        window.location.href = '/user-login';
      } else {
        alert("Error: " + (data.message || data.error || "Unknown server error"));
      }
    } catch (error) {
      console.error("Registration error:", error);
      console.error('Error details:', {
        message: error.message,
        name: error.name,
        stack: error.stack
      });
      
      if (error.message.includes('Failed to fetch') || error.message.includes('NetworkError') || error.message.includes('ERR_NETWORK')) {
        alert('Network error: Unable to connect to server. Please check if:\n1. Your backend server is running\n2. You are on the correct port\n3. There are no firewall issues');
      } else if (error.message.includes('JSON') || error.message.includes('Unexpected end') || error.message.includes('Invalid JSON')) {
        alert('Server returned invalid data. This might be a server error. Please check the browser console for more details and try again.');
      } else if (error.message.includes('HTTP error')) {
        alert('Server error: ' + error.message);
      } else {
        alert('Registration failed: ' + error.message);
      }
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="page-container">
      {/* Left Section - Image */}
      <div className="image-section">
        <img src="/images/traverller-reg.png" alt="Traveler" />
      </div>

      {/* Right Section - Form */}
      <div className="form-section">
        <div className="traveler-form-container">
          <div className="form-header">
            <img className="logo-image" src="/images/new logo.png" alt="Logo" />
            <h2>Join as a Traveler</h2>
            <p>Create your traveler account</p>
          </div>

          <form onSubmit={handleSubmit} className="traveler-form">
            <input 
              name="name" 
              type="text" 
              placeholder="Enter your full name" 
              value={form.name} 
              onChange={handleChange} 
              required 
            />
            <input
              name="email"
              type="email"
              placeholder="Enter your email address"
              value={form.email}
              onChange={handleChange}
              required
              onInvalid={(e) =>
                e.target.setCustomValidity("Please enter a valid email (e.g., user@example.com)")
              }
              onInput={(e) => e.target.setCustomValidity("")}
            />
            <input 
              name="phone" 
              type="tel" 
              placeholder="Enter your phone number" 
              value={form.phone} 
              onChange={handleChange} 
              required 
            />
            <input
              name="password"
              type="password"
              placeholder="Create a strong password"
              value={form.password}
              onChange={handleChange}
              required
            />
            <input
              name="confirmPassword"
              type="password"
              placeholder="Confirm your password"
              value={form.confirmPassword}
              onChange={handleChange}
              required
            />

            {/* Terms and Conditions Checkbox */}
            <div className="driver-checkbox-container">
              <label className="driver-checkbox-label">
                <input
                  type="checkbox"
                  name="agree"
                  checked={form.agree}
                  onChange={handleChange}
                  required
                />
                I agree to the{" "}
                <a href="/termsconditions" target="_blank" rel="noopener noreferrer">
                  Terms and Conditions
                </a>{" "}
                and{" "}
                <a href="/privacypolicy" target="_blank" rel="noopener noreferrer">
                  Privacy Policy
                </a>
              </label>
            </div>

            <button 
              type="submit" 
              className="submit-btn" 
              disabled={loading || !form.agree}
            >
              {loading ? "Registering..." : "Create Traveler Account"}
            </button>

            <p className="signin-text">
              Already have a traveler account? <a href="/user-login">Sign in here</a>
            </p>
          </form>
        </div>
      </div>
    </div>
  );
}
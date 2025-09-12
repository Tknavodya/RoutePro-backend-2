import React, { useState } from 'react';
import './GuideRegistrationForm.css';
import axios from 'axios';

// Create axios instance with default config
const api = axios.create({
  withCredentials: false,
  headers: {
    'Content-Type': 'application/json'
  }
});

export default function GuideRegistrationForm() {
  const [form, setForm] = useState({
    name: '',
    email: '',
    phone: '',
    nic: '',
    license_no: '',
    experience: '',
    location: '',
    languages: '',
    password: '',
    confirmPassword: '',
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
const validateEmail = (email) =>
  /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);

// 3. Phone: Either 10 digits starting with 0 or 9 digits starting with 7
const validatePhone = (phone) => {
  const cleaned = phone.replace(/[\s-]/g, '');
  return /^0\d{9}$/.test(cleaned) || /^7\d{8}$/.test(cleaned);
};

// 4. NIC: Either old (9 digits + V/X) or new (12 digits)
const validateNIC = (nic) =>
  /^[0-9]{9}[vVxX]$/.test(nic) || /^[0-9]{12}$/.test(nic);

// 5. Guide License: Alphanumeric only
const validateLicense = (license_no) => /^[a-zA-Z0-9]+$/.test(license_no);

// 6. Experience: Must be a positive integer
const validateExperience = (experience) => /^[1-9][0-9]*$/.test(experience);

// 7. Location: Letters, numbers, commas, and spaces allowed
const validateLocation = (location) =>
  /^[a-zA-Z0-9\s,]+$/.test(location);

// 8. Languages: Letters, commas, and spaces
const validateLanguages = (languages) =>
  /^[a-zA-Z\s,]+$/.test(languages);

// 9. Password: Minimum 8 characters, at least 1 letter, 1 number, 1 special character
const validatePassword = (password) =>
  /^(?=.*[A-Za-z])(?=.*\d)(?=.*[^A-Za-z\d]).{8,}$/.test(password);



  const handleSubmit = async (e) => {
    e.preventDefault();

   // Step 1: Terms agreement must be ticked
if (!form.agree) {
  alert('You must agree to the Terms and Conditions and Privacy Policy.');
  return;
}

// Step 2: Validate each field in order
if (!validateName(form.name)) {
  alert('Full name can only contain letters and spaces.');
  return;
}

if (!validateEmail(form.email)) {
  alert('Invalid email format.');
  return;
}

if (!validatePhone(form.phone)) {
  alert('Phone number must be 10 digits starting with 0 or 9 digits starting with 7.');
  return;
}

if (!validateNIC(form.nic)) {
  alert('NIC must be 9 digits + V/v/X/x or 12 digits.');
  return;
}

if (!validateLicense(form.license_no)) {
  alert('Guide License must be alphanumeric only.');
  return;
}

if (!validateExperience(form.experience)) {
  alert('Experience must be a positive whole number.');
  return;
}

if (!validateLocation(form.location)) {
  alert('Location contains invalid characters.');
  return;
}

if (!validateLanguages(form.languages)) {
  alert('Languages must contain only letters, commas, and spaces.');
  return;
}

if (!validatePassword(form.password)) {
  alert('Password must be at least 8 characters and include a letter, a number, and a special character.');
  return;
}

if (form.password !== form.confirmPassword) {
  alert('Passwords do not match.');
  return;
}

// Prepare data
    const { confirmPassword, agree, ...submitData } = form;
    
    // Add role to the payload
    submitData.role = 'guide';

    setLoading(true);
    try {
      const response = await api.post(
        'http://localhost/RoutePro-backend(02)/public/auth/register',
        submitData
      );

      if (response.data.success) {
        alert('Guide registered successfully!');
        setForm({
          name: '',
          email: '',
          phone: '',
          nic: '',
          license_no: '',
          experience: '',
          location: '',
          languages: '',
          password: '',
          confirmPassword: '',
          agree: false,
        });
        // Redirect to login page after user clicks OK on alert
        window.location.href = '/user-login';
      } else {
        alert('Error: ' + (response.data.message || 'Unknown server error'));
      }
    } catch (error) {
      console.error('Registration error:', error);
      console.error('Error details:', {
        message: error.message,
        response: error.response?.data,
        status: error.response?.status,
        statusText: error.response?.statusText
      });
      
      if (error.response) {
        // Server responded with error status
        const errorMessage = error.response.data?.message || 
                           error.response.data?.error || 
                           `Server error: ${error.response.status}`;
        alert('Registration failed: ' + errorMessage);
      } else if (error.request) {
        // Request was made but no response received
        alert('Network error: Unable to connect to server. Please check if the backend is running.');
      } else {
        // Something else happened
        alert('Registration failed: ' + error.message);
      }
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="guide-page-container">
      {/* LEFT SECTION: IMAGE */}
      <div className="guide-image-section">
                 <img src="/images/guide.jpg" alt="Guide illustration" />
      </div>
      <div className="guide-form-section">
    <div className="guide-form-container">
      <div className="guide-form-header">
                 <img className="guide-logo-image" src="/images/new logo.png" alt="Logo" />
        <h2>Join as a Tour Guide</h2>
        <p>Create your tour guide account</p>
      </div>

      <form onSubmit={handleSubmit} className="guide-form">
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
          name="nic"
          type="text"
          placeholder="Enter your NIC number"
          value={form.nic}
          onChange={handleChange}
          required
        />
        <input
          name="license_no"
          type="text"
          placeholder="Enter your guide license number"
          value={form.license_no}
          onChange={handleChange}
          required
        />
        <input
          name="experience"
          type="number"
          placeholder="Years of experience"
          value={form.experience}
          onChange={handleChange}
          required
        />
        <input
          name="location"
          type="text"
          placeholder="Enter your primary location"
          value={form.location}
          onChange={handleChange}
          required
        />
        <input
          name="languages"
          type="text"
          placeholder="Languages spoken (e.g., English, Sinhala, Tamil)"
          value={form.languages}
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


        <button type="submit" disabled={loading} className="submit-btn">
          {loading ? 'Registering...' : 'Create Guide Account'}
        </button>

        <p className="signin-link">
          Already have a guide account? <a href="/user-login">Sign in here</a>
        </p>
      </form>
    </div>
    </div>
    </div>
  );
}

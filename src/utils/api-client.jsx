import axios from 'axios';

// Update the backend URL to match your actual backend location in XAMPP
const BACKEND_URL = 'http://localhost/RoutePro-backend(02)/public';

// Create axios instance with updated config
const api = axios.create({
  baseURL: BACKEND_URL,
  withCredentials: false,
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json'
  },
  timeout: 15000
});

// API methods
export const apiMethods = {
  // Get backend URL for debugging
  getBackendUrl: () => BACKEND_URL,

  // Login method
  login: async (email, password) => {
    try {
      console.log(`Attempting login to: ${BACKEND_URL}/auth/login.php`);
      const response = await api.post('/auth/login.php', { email, password });
      return response;
    } catch (error) {
      console.error('Login API error:', error);
      throw error;
    }
  },

  // Register method
  register: async (userData) => {
    try {
      console.log(`Attempting registration to: ${BACKEND_URL}/auth/register.php`);
      const response = await api.post('/auth/register.php', userData);
      return response;
    } catch (error) {
      console.error('Registration API error:', error);
      throw error;
    }
  },

  // Generic authenticated request
  authenticatedRequest: async (endpoint, data = null, method = 'GET') => {
    const token = sessionUtils.getSessionToken();
    const config = {
      headers: {
        ...api.defaults.headers,
        'Authorization': token ? `Bearer ${token}` : ''
      }
    };

    try {
      let response;
      switch (method.toUpperCase()) {
        case 'POST':
          response = await api.post(endpoint, data, config);
          break;
        case 'PUT':
          response = await api.put(endpoint, data, config);
          break;
        case 'DELETE':
          response = await api.delete(endpoint, config);
          break;
        default:
          response = await api.get(endpoint, config);
      }
      return response;
    } catch (error) {
      console.error('Authenticated request error:', error);
      throw error;
    }
  }
};

// Session utilities
export const sessionUtils = {
  // Check if user is logged in
  isLoggedIn: () => {
    const userId = localStorage.getItem('userId');
    const role = localStorage.getItem('role');
    return userId && role;
  },

  // Get current user data
  getCurrentUser: () => {
    if (!sessionUtils.isLoggedIn()) return null;
    
    return {
      userId: localStorage.getItem('userId'),
      role: localStorage.getItem('role'),
      name: localStorage.getItem('userName'),
      email: localStorage.getItem('userEmail'),
      rating: localStorage.getItem('userRating') || '0'
    };
  },

  // Get session token
  getSessionToken: () => {
    return localStorage.getItem('sessionToken');
  },

  // Clear session data
  logout: () => {
    localStorage.removeItem('userId');
    localStorage.removeItem('role');
    localStorage.removeItem('userName');
    localStorage.removeItem('userEmail');
    localStorage.removeItem('userRating');
    localStorage.removeItem('sessionToken');
    localStorage.removeItem('userProfile');
    localStorage.removeItem('sessionStartTime');
    
    // Add a logout marker to prevent back navigation to protected pages
    localStorage.setItem('justLoggedOut', 'true');
    
    // Clear browser history and redirect to login page
    window.history.replaceState(null, null, '/user-login');
    
    // Add multiple history entries to prevent easy back navigation
    window.history.pushState(null, null, '/user-login');
    window.history.pushState(null, null, '/user-login');
    
    window.location.href = '/user-login';
  },

  // Set session data
  setSession: (userData) => {
    localStorage.setItem('userId', userData.userId);
    localStorage.setItem('role', userData.role);
    localStorage.setItem('userName', userData.name);
    localStorage.setItem('userEmail', userData.email);
    localStorage.setItem('userRating', userData.rating || '0');
    
    if (userData.sessionToken) {
      localStorage.setItem('sessionToken', userData.sessionToken);
    }
    
    if (userData.profile) {
      localStorage.setItem('userProfile', JSON.stringify(userData.profile));
    }
  }
};

// Add request interceptor for debugging
api.interceptors.request.use(
  (config) => {
    console.log(`🚀 API Request: ${config.method?.toUpperCase()} ${config.baseURL}${config.url}`);
    return config;
  },
  (error) => {
    console.error('❌ API Request Error:', error);
    return Promise.reject(error);
  }
);

// Add response interceptor for debugging
api.interceptors.response.use(
  (response) => {
    console.log(`✅ API Response: ${response.status} ${response.config.url}`);
    return response;
  },
  (error) => {
    console.error(`❌ API Response Error: ${error.response?.status} ${error.config?.url}`);
    return Promise.reject(error);
  }
);

export default api;
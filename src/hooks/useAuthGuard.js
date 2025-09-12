import { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';

/**
 * Custom hook to protect dashboard routes from unauthorized access
 * @param {string} requiredRole - The role required to access this dashboard
 * @returns {object} - { isAuthenticated, isLoading }
 */
const useAuthGuard = (requiredRole) => {
  const [isAuthenticated, setIsAuthenticated] = useState(false);
  const [isLoading, setIsLoading] = useState(true);
  const navigate = useNavigate();

  useEffect(() => {
    const checkAuth = () => {
      // Check if user just logged out
      const justLoggedOut = localStorage.getItem('justLoggedOut');
      if (justLoggedOut) {
        localStorage.removeItem('justLoggedOut');
        console.log("User just logged out, redirecting to login");
        navigate("/user-login", { replace: true });
        return false;
      }

      const userId = localStorage.getItem("userId");
      const role = localStorage.getItem("role");
      const sessionStartTime = localStorage.getItem("sessionStartTime");

      // Check if user is logged in with valid session
      if (!userId || !role || !sessionStartTime) {
        console.log("No authentication data found, redirecting to login");
        navigate("/user-login", { replace: true });
        return false;
      }

      // Check if user has the correct role
      if (role !== requiredRole) {
        console.log(`Invalid role ${role} for ${requiredRole} dashboard, redirecting to login`);
        navigate("/user-login", { replace: true });
        return false;
      }

      // Check session timeout (24 hours)
      const sessionAge = Date.now() - parseInt(sessionStartTime);
      const sessionLimit = 24 * 60 * 60 * 1000; // 24 hours

      if (sessionAge > sessionLimit) {
        console.log("Session expired, clearing data and redirecting to login");
        localStorage.removeItem("userId");
        localStorage.removeItem("role");
        localStorage.removeItem("userName");
        localStorage.removeItem("userEmail");
        localStorage.removeItem("userRating");
        localStorage.removeItem("sessionStartTime");
        localStorage.removeItem("userProfile");
        navigate("/user-login", { replace: true });
        return false;
      }

      setIsAuthenticated(true);
      setIsLoading(false);
      return true;
    };

    // Add browser history protection
    const handlePopState = () => {
      // Check authentication whenever user tries to navigate back
      const userId = localStorage.getItem("userId");
      const role = localStorage.getItem("role");
      
      if (!userId || !role || role !== requiredRole) {
        // User is not authenticated, redirect to login
        console.log("Back navigation detected without proper auth, redirecting to login");
        navigate("/user-login", { replace: true });
      }
    };

    // Check authentication
    if (!checkAuth()) {
      setIsLoading(false);
      return;
    }

    // Listen for browser back/forward button clicks
    window.addEventListener('popstate', handlePopState);

    // Cleanup
    return () => {
      window.removeEventListener('popstate', handlePopState);
    };
  }, [navigate, requiredRole]);

  // Additional effect to check for storage changes (like logout from another tab)
  useEffect(() => {
    const handleStorageChange = (e) => {
      if (e.key === 'userId' && e.newValue === null) {
        // User logged out from another tab
        console.log("Logout detected from another tab, redirecting to login");
        navigate("/user-login", { replace: true });
      }
    };

    // Add protection against browser navigation to unauthorized areas
    const handleBeforeUnload = (e) => {
      const userId = localStorage.getItem("userId");
      const role = localStorage.getItem("role");
      
      if (!userId || !role) {
        // Clear any remaining history
        window.history.replaceState(null, null, '/user-login');
      }
    };

    window.addEventListener('storage', handleStorageChange);
    window.addEventListener('beforeunload', handleBeforeUnload);

    return () => {
      window.removeEventListener('storage', handleStorageChange);
      window.removeEventListener('beforeunload', handleBeforeUnload);
    };
  }, [navigate]);

  return {
    isAuthenticated,
    isLoading
  };
};

export default useAuthGuard;

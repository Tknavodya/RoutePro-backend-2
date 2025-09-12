import React, { useState, useEffect } from "react";
import Container from "react-bootstrap/Container";
import Nav from "react-bootstrap/Nav";
import Navbar from "react-bootstrap/Navbar";
import Button from "react-bootstrap/Button";
import Modal from "react-bootstrap/Modal";
import { FaUserCircle } from "react-icons/fa";
import { Link, useNavigate, useLocation } from "react-router-dom";

// ✅ Import your custom CSS
import "./Header.css";

export default function Header() {
  const navigate = useNavigate();
  const location = useLocation();
  const [showModal, setShowModal] = useState(false);
  const [userInfo, setUserInfo] = useState(null);
  const [isLoggedIn, setIsLoggedIn] = useState(false);

  // Clear stale/invalid localStorage data on startup
  useEffect(() => {
    const userEmail = localStorage.getItem('userEmail') || localStorage.getItem('email');
    const userRole = localStorage.getItem('userRole') || localStorage.getItem('role');
    const userName = localStorage.getItem('userName') || localStorage.getItem('name');
    const sessionTime = localStorage.getItem('sessionStartTime');
    
    // Check if session is too old (more than 24 hours) or data is incomplete
    const twentyFourHours = 24 * 60 * 60 * 1000;
    const now = Date.now();
    const isSessionExpired = sessionTime && (now - parseInt(sessionTime)) > twentyFourHours;
    const isIncomplete = (userEmail && !userRole) || (userEmail && !userName) || (!userEmail && (userRole || userName));
    
    // Clear invalid/expired data to prevent showing stale names
    if (isSessionExpired || isIncomplete) {
      localStorage.removeItem('userEmail');
      localStorage.removeItem('email');
      localStorage.removeItem('userRole');
      localStorage.removeItem('role');
      localStorage.removeItem('userId');
      localStorage.removeItem('userName');
      localStorage.removeItem('name');
      localStorage.removeItem('userRating');
      localStorage.removeItem('userProfile');
      localStorage.removeItem('sessionStartTime');
    }
  }, []);

  // Check user login status
  const checkUserLogin = async () => {
    const userEmail = localStorage.getItem('userEmail') || localStorage.getItem('email');
    const userRole = localStorage.getItem('userRole') || localStorage.getItem('role');
    const userName = localStorage.getItem('userName') || localStorage.getItem('name');
    
    if (userEmail && userRole && userName) {
      setIsLoggedIn(true);
      setUserInfo({
        name: userName,
        photo: null,
        role: userRole
      });
      
      // Try to get profile photo from backend
      try {
        let profileEndpoint = '';
        if (userRole === 'driver') {
          profileEndpoint = `http://localhost/RoutePro-backend(02)/public/driver/profile?email=${encodeURIComponent(userEmail)}`;
        } else if (userRole === 'guide') {
          profileEndpoint = `http://localhost/RoutePro-backend(02)/public/guide/profile?email=${encodeURIComponent(userEmail)}`;
        } else if (userRole === 'traveller') {
          profileEndpoint = `http://localhost/RoutePro-backend(02)/public/traveller/profile?email=${encodeURIComponent(userEmail)}`;
        }
        
        if (profileEndpoint) {
          const response = await fetch(profileEndpoint);
          const data = await response.json();
          
          if (data.success && data.data) {
            setUserInfo({
              name: data.data.name || userName,
              photo: data.data.photo || null,
              role: userRole
            });
          }
        }
      } catch (error) {
        // Keep localStorage data even if backend fails
      }
    } else {
      setIsLoggedIn(false);
      setUserInfo(null);
    }
  };

  // Check on mount and route changes
  useEffect(() => {
    checkUserLogin();
  }, [location.pathname]);

  // Listen for storage changes (logout events)
  useEffect(() => {
    const handleStorageChange = () => {
      const userEmail = localStorage.getItem('userEmail') || localStorage.getItem('email');
      const userRole = localStorage.getItem('userRole') || localStorage.getItem('role');
      const userName = localStorage.getItem('userName') || localStorage.getItem('name');
      
      if (!(userEmail && userRole && userName)) {
        setIsLoggedIn(false);
        setUserInfo(null);
      } else {
        checkUserLogin();
      }
    };

    window.addEventListener('storage', handleStorageChange);
    return () => window.removeEventListener('storage', handleStorageChange);
  }, []);

  const handleJoinClick = () => setShowModal(true);
  const handleClose = () => setShowModal(false);

  const handleUserProfileClick = () => {
    // Navigate to dashboard based on user role
    if (userInfo?.role === 'driver') {
      navigate('/driver-dashboard');
    } else if (userInfo?.role === 'guide') {
      navigate('/guide-dashboard');
    } else if (userInfo?.role === 'traveller') {
      navigate('/traveller-dashboard');
    }
  };

  const handleJoinAs = (role) => {
    if (role === "traveler") navigate("/traveler-register");
    else if (role === "driver") navigate("/driver-registration");
    else if (role === "guider") navigate("/guide-registration");
    setShowModal(false);
  };

  return (
    <>
      <Navbar collapseOnSelect expand="lg" fixed="top" className="header-navbar">
        <Container>
          <Link to="/homepage">
            <img src="/images/new logo.png" alt="Logo" className="routeprologo" />
          </Link>

          <Navbar.Toggle aria-controls="responsive-navbar-nav" />
          <Navbar.Collapse id="responsive-navbar-nav">
            <Nav className="me-auto"></Nav>
            <Nav>
              <img src="/images/home.png" alt="Home Icon" className="topnav-logo" />
              <Nav.Link as={Link} to="/homepage" className="nav-link-underline">Home</Nav.Link>

              <img src="/images/navigation.png" alt="Route Icon" className="topnav-logo" />
              <Nav.Link as={Link} to="/route" className="nav-link-underline">Route</Nav.Link>

              <img src="/images/budget.png" alt="Budget Icon" className="topnav-logo" />
              <Nav.Link as={Link} to="/budget" className="nav-link-underline">Budget</Nav.Link>

              <img src="/images/culture.png" alt="Culture Icon" className="topnav-logo" />
              <Nav.Link as={Link} to="/culture" className="nav-link-underline">Culture</Nav.Link>
            </Nav>

            <Nav className="topnav-right">
              {isLoggedIn ? (
                // Logged in user view - clickable name and photo to go to dashboard
                <div className="user-profile-section" onClick={handleUserProfileClick} style={{cursor: 'pointer'}}>
                  <div className="user-avatar-container">
                    {userInfo?.photo ? (
                      <img 
                        src={`http://localhost${userInfo.photo}?t=${Date.now()}`}
                        alt="Profile" 
                        className="user-avatar"
                        onError={(e) => {
                          e.target.style.display = 'none';
                          e.target.nextSibling.style.display = 'block';
                        }}
                      />
                    ) : null}
                    <FaUserCircle 
                      className="user-icon" 
                      style={{ display: userInfo?.photo ? 'none' : 'block' }}
                    />
                  </div>
                  <span className="user-name">{userInfo?.name}</span>
                </div>
              ) : (
                // Not logged in view
                <>
                  <Button
                    className="topnav-button custom-login-button"
                    onClick={() => navigate("/user-login")}
                  >
                    Login
                  </Button>

                  <Button
                    className="topnav-button custom-join-button"
                    onClick={handleJoinClick}
                  >
                    Join
                  </Button>

                  <FaUserCircle className="user-icon" />
                </>
              )}
            </Nav>
          </Navbar.Collapse>
        </Container>
      </Navbar>

      {/* Join Modal */}
      <Modal show={showModal} onHide={handleClose} centered>
        <Modal.Header closeButton>
          <Modal.Title>Join as</Modal.Title>
        </Modal.Header>
        <Modal.Body>
          <div className="join-options">
            <Button
              className="join-option-button traveler"
              onClick={() => handleJoinAs("traveler")}
            >
              Traveler
            </Button>
            <Button
              className="join-option-button driver"
              onClick={() => handleJoinAs("driver")}
            >
              Driver
            </Button>
            <Button
              className="join-option-button guider"
              onClick={() => handleJoinAs("guider")}
            >
              Guide
            </Button>
          </div>
        </Modal.Body>
      </Modal>
    </>
  );
}

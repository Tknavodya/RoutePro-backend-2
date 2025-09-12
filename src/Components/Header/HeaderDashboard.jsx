import React, { useState } from "react";
import Container from "react-bootstrap/Container";
import Nav from "react-bootstrap/Nav";
import Navbar from "react-bootstrap/Navbar";
import Button from "react-bootstrap/Button";
import Modal from "react-bootstrap/Modal";
import { FaUserCircle } from "react-icons/fa";
import { Link, useNavigate } from "react-router-dom";
import "./HeaderDashboard.css";

export default function HeaderDashboard() {
  const navigate = useNavigate();
  const [showModal, setShowModal] = useState(false);

  const handleJoinClick = () => setShowModal(true);
  const handleClose = () => setShowModal(false);

  const handleJoinAs = (role) => {
    if (role === "traveler") navigate("/traveler-register");
    else if (role === "driver") navigate("/driver-registration");
    else if (role === "guider") navigate("/guide-registration");
    setShowModal(false); // close modal after navigation
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
              <Nav.Link as={Link} to="/homepage">Home</Nav.Link>

                                                             <img src="/images/navigation.png" alt="Route Icon" className="topnav-logo" />
              <Nav.Link as={Link} to="/route">Route</Nav.Link>

                                                             <img src="/images/budget.png" alt="Budget Icon" className="topnav-logo" />
              <Nav.Link as={Link} to="/budget">Budget</Nav.Link>

                                                             <img src="/images/culture.png" alt="Culture Icon" className="topnav-logo" />
              <Nav.Link as={Link} to="/culture">Culture</Nav.Link>
            </Nav>

            <Nav className="topnav-right">
              {/* Keep dashboard header simple - focus on main Header.jsx */}
              
            </Nav>
          </Navbar.Collapse>
        </Container>
      </Navbar>

      {/* Join Modal */}
      <Modal show={showModal} onHide={handleClose}>
        <Modal.Header closeButton>
          <Modal.Title>Join RoutePro</Modal.Title>
        </Modal.Header>
        <Modal.Body>
          <p>Choose how you'd like to join RoutePro:</p>
          <div className="d-grid gap-2">
            <Button variant="primary" onClick={() => handleJoinAs("traveler")}>
              Join as Traveler
            </Button>
            <Button variant="success" onClick={() => handleJoinAs("driver")}>
              Join as Driver
            </Button>
            <Button variant="info" onClick={() => handleJoinAs("guider")}>
              Join as Guide
            </Button>
          </div>
        </Modal.Body>
      </Modal>
    
    </>
  );
}

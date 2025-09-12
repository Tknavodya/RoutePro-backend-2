
import React, { useState } from "react";
import useAuthGuard from "../../../hooks/useAuthGuard";
import Sidebar from "./Slidebar";
import Dashboard from "./Dashboard";
import TripManagement from "./TripManagement";
import UserManagement from "./UserManagement";
import Notifications from "./Notification";
import "./AdminDashboard.css";

const AdminDashboard = () => {
  // Possible values: "dashboard", "trips", "users", "notifications"
  const [currentPage, setCurrentPage] = useState("dashboard");
  const { isAuthenticated, isLoading } = useAuthGuard('admin');

  // Don't render dashboard if still loading or not authenticated
  if (isLoading) {
    return (
      <div className="admin-dashboard">
        <div className="loading-container">
          <h1>Loading...</h1>
        </div>
      </div>
    );
  }

  if (!isAuthenticated) {
    return null; // useAuthGuard will handle redirect
  }

  // Render the main content based on currentPage
  const renderContent = () => {
    switch (currentPage) {
      case "dashboard":
        return <Dashboard />;
      case "trips":
        return <TripManagement />;
      case "users":
        return <UserManagement />;
      case "notifications":
        return <Notifications />;
      default:
        return <Dashboard />;
    }
  };

  return (
    <div className="admin-dashboard-layout">
      <Sidebar currentPage={currentPage} setCurrentPage={setCurrentPage} />
      <div className="admin-dashboard-content">
        {renderContent()}
      </div>
    </div>
  );
};

export default AdminDashboard;
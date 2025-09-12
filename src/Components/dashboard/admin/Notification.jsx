"use client"

import { useState } from "react"
import "./Notification.css"

const Notifications = () => {
  const [notifications, setNotifications] = useState([
    {
      id: 1,
      type: "low-rating",
      title: "Low Rating Alert",
      message: "Driver Sunil Fernando's rating has dropped to 2.1. Immediate attention required.",
      time: "2 minutes ago",
      priority: "high",
      isRead: false,
      currentRating: 2.1,
      actions: ["View Profile", "Reset Rating"],
    },
    {
      id: 2,
      type: "new-booking",
      title: "New Trip Booking",
      message: "New trip booked by Sarah Wilson for Colombo to Kandy route.",
      time: "15 minutes ago",
      priority: "medium",
      isRead: false,
      revenue: 16000,
      actions: ["View Trip Details"],
    },
    {
      id: 3,
      type: "low-rating",
      title: "Low Rating Alert",
      message: "Guide Chamara Dias's rating has dropped to 2.3. Review required.",
      time: "1 hour ago",
      priority: "high",
      isRead: false,
      currentRating: 2.3,
      actions: ["View Profile", "Reset Rating"],
    },
    {
      id: 4,
      type: "payment",
      title: "Payment Received",
      message: "Payment of Rs. 15,000 received for trip TR001.",
      time: "3 hours ago",
      priority: "low",
      isRead: true,
      amount: 15000,
      actions: [],
    },
  ])

  const markAsRead = (id) => {
    setNotifications((prev) => prev.map((notif) => (notif.id === id ? { ...notif, isRead: true } : notif)))
  }

  const markAllAsRead = () => {
    setNotifications((prev) => prev.map((notif) => ({ ...notif, isRead: true })))
  }

  const resetRating = (id) => {
    // This would typically make an API call to reset the rating
    console.log(`Resetting rating for notification ${id}`)
    markAsRead(id)
  }

  const getNotificationIcon = (type) => {
    switch (type) {
      case "low-rating":
        return "⚠️"
      case "new-booking":
        return "🔔"
      case "payment":
        return "💰"
      default:
        return "📢"
    }
  }

  const getPriorityColor = (priority) => {
    switch (priority) {
      case "high":
        return "#ef4444"
      case "medium":
        return "#f59e0b"
      case "low":
        return "#10b981"
      default:
        return "#64748b"
    }
  }

  const unreadCount = notifications.filter((n) => !n.isRead).length

  return (
    <div className="notifications">
      <div className="page-header">
        <div className="header-left">
          <h2>Notifications</h2>
          <span className="unread-count">{unreadCount} unread notifications</span>
        </div>
        <button className="mark-all-read" onClick={markAllAsRead}>
          Mark All as Read
        </button>
      </div>

      <div className="notification-list">
        {notifications.map((notification) => (
          <div key={notification.id} className={`notification-card ${!notification.isRead ? "unread" : ""}`}>
            <div className="notification-header">
              <div className="notification-info">
                <span className="notification-icon">{getNotificationIcon(notification.type)}</span>
                <div className="notification-title">
                  <h3>{notification.title}</h3>
                  <span className="priority-badge" style={{ backgroundColor: getPriorityColor(notification.priority) }}>
                    {notification.priority.charAt(0).toUpperCase() + notification.priority.slice(1)} Priority
                  </span>
                </div>
              </div>
              <div className="notification-meta">
                <span className="notification-time">{notification.time}</span>
                {!notification.isRead && (
                  <button className="mark-read-btn" onClick={() => markAsRead(notification.id)}>
                    Mark as Read
                  </button>
                )}
              </div>
            </div>

            <div className="notification-content">
              <p className="notification-message">{notification.message}</p>

              {notification.currentRating && (
                <div className="rating-info">
                  <span className="current-rating">Current Rating: ⭐ {notification.currentRating}</span>
                </div>
              )}

              {notification.revenue && (
                <div className="revenue-info">
                  <span className="revenue">Revenue: Rs. {notification.revenue.toLocaleString()}</span>
                </div>
              )}

              {notification.amount && (
                <div className="amount-info">
                  <span className="amount">Amount: Rs. {notification.amount.toLocaleString()}</span>
                </div>
              )}
            </div>

            {notification.actions.length > 0 && (
              <div className="notification-actions">
                {notification.actions.map((action, index) => (
                  <button
                    key={index}
                    className={`action-btn ${action === "Reset Rating" ? "reset-btn" : ""}`}
                    onClick={() => {
                      if (action === "Reset Rating") {
                        resetRating(notification.id)
                      } else {
                        console.log(`Action: ${action}`)
                      }
                    }}
                  >
                    {action}
                  </button>
                ))}
              </div>
            )}
          </div>
        ))}
      </div>
    </div>
  )
}

export default Notifications

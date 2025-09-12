import React, { useState, useEffect } from "react";
import { createPortal } from "react-dom";
import { useNavigate } from "react-router-dom";
import "./DriverHeader.css";
import { apiMethods } from "../../../../utils/api-client";

const DriverHeader = ({ status, setStatus, userId, setUserName }) => {
  const [revenue, setRevenue] = useState(null);
  const [isLoadingRevenue, setIsLoadingRevenue] = useState(false);
  const [driverInfo, setDriverInfo] = useState({
    name: '',
    phone: '',
    email: '',
    status: '',
    license_no: '',
    vehicle_type: '',
    experience: '',
    location: '',
    photoUrl: '',
  });
  const [isEditing, setIsEditing] = useState(false);
  const [editData, setEditData] = useState(null);
  const [isSaving, setIsSaving] = useState(false);
  const [photoFile, setPhotoFile] = useState(null);
  const [photoPreview, setPhotoPreview] = useState(null);

  const navigate = useNavigate();

  useEffect(() => {
    // Get the logged-in user's email from localStorage (set during login)
    const userEmail = localStorage.getItem('userEmail') || 
                     localStorage.getItem('email') ||
                     'admin@gmail.com'; // Fallback for testing

    console.log('🚀 Fetching driver data for logged-in user:', userEmail);

    // Use proper DriverController endpoint with email parameter
    fetch(`http://localhost/RoutePro-backend(02)/public/driver/profile?email=${encodeURIComponent(userEmail)}`, {
      method: 'GET',
      headers: {
        'Content-Type': 'application/json',
      }
    })
      .then((res) => {
        if (!res.ok) {
          throw new Error(`HTTP error! status: ${res.status}`);
        }
        return res.json();
      })
      .then((data) => {
        if (data.success && data.data) {
          setDriverInfo({
            name: data.data.name || '',
            phone: data.data.phone || '',
            email: data.data.email || '',
            status: data.data.status || 'available',
            license_no: data.data.license_no || '',
            vehicle_type: data.data.vehicle_type || '',
            experience: data.data.experience || '',
            location: data.data.location || '',
            photoUrl: (data.data.photo && data.data.photo.trim() !== '') ? data.data.photo : null
          });
          setStatus(data.data.status || 'available'); // Update status display
          
          // Log photo info for debugging
          console.log('📸 Driver photo info:', {
            photoUrl: data.data.photoUrl,
            photo: data.data.photo,
            finalPhotoUrl: (data.data.photoUrl || data.data.photo || '')
          });
        } else {
          console.error('Error fetching driver info:', data.message || 'Unknown error');
        }
      })
      .catch((err) => {
        console.error('Fetch error:', err);
        // Handle network errors or authentication failures
        if (err.message.includes('401') || err.message.includes('403')) {
          navigate('/login'); // Redirect to login if unauthorized
        }
      });
  }, [navigate]);

  // Update local driverInfo status when the status prop changes
  useEffect(() => {
    setDriverInfo(prevInfo => ({
      ...prevInfo,
      status: status
    }));
  }, [status]);

  // Function to update status in backend (can be called from header if needed)
  const updateStatusInBackend = async (newStatus) => {
    try {
      const userEmail = localStorage.getItem('userEmail') || 
                       localStorage.getItem('email');
      
      if (!userEmail) {
        console.error('No user email found for status update');
        return;
      }

      console.log('🔄 Updating driver status from header to:', newStatus);

      const response = await fetch('http://localhost/RoutePro-backend(02)/public/driver/status', {
        method: 'PUT',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({
          email: userEmail,
          status: newStatus
        })
      });

      const result = await response.json();

      if (response.ok && result.success) {
        console.log('✅ Status updated successfully in backend from header');
        setStatus(newStatus); // Update local state after successful backend update
      } else {
        console.error('❌ Failed to update status in backend:', result.message);
        alert(`Failed to update status: ${result.message || 'Unknown error'}`);
      }
    } catch (error) {
      console.error('❌ Error updating status from header:', error);
      alert('Network error while updating status. Please try again.');
    }
  };

  useEffect(() => {
    // Fetch dynamic revenue for the logged-in driver
    const userEmail =
      localStorage.getItem('userEmail') ||
      localStorage.getItem('email') ||
      'admin@gmail.com';

    const controller = new AbortController();
    const signal = controller.signal;

    const fetchRevenue = async () => {
      setIsLoadingRevenue(true);
      try {
        // TODO: Update to your real backend endpoint
        const resp = await fetch(
          `http://localhost/RoutePro-backend(02)/public/driver/revenue?email=${encodeURIComponent(userEmail)}`,
          { method: 'GET', headers: { 'Content-Type': 'application/json' }, signal }
        );
        if (!resp.ok) {
          throw new Error(`HTTP error! status: ${resp.status}`);
        }
        const data = await resp.json();
        // Expecting: { success: true, data: { revenue: number } }
        const value = (data && data.data && (data.data.revenue ?? data.data.totalRevenue)) ?? null;
        setRevenue(value);
      } catch (err) {
        if (err.name !== 'AbortError') {
          console.error('Revenue fetch error:', err);
        }
        setRevenue(null);
      } finally {
        setIsLoadingRevenue(false);
      }
    };

    fetchRevenue();
    return () => controller.abort();
  }, []);

  const handleLogout = () => {
    // Optional: clear localStorage if needed
    navigate("/homepage");
  };

  // userId is received from parent; fallback to localStorage if not provided
  const effectiveUserId = userId || localStorage.getItem("userId");

  return (
    <div className="driver-header">
      <div className="photo-wrapper">
        <img src={(photoPreview || (driverInfo.photoUrl && driverInfo.photoUrl.trim() !== '' ? `http://localhost${driverInfo.photoUrl}?t=${Date.now()}` : 'http://localhost/RoutePro-backend(02)/public/images/defaults/default.png'))} alt="Driver" className="driver-photo" />
        <button
          type="button"
          className="edit-overlay"
          onClick={(e) => { e.stopPropagation(); setEditData(driverInfo); setIsEditing(true); }}
        >
          Edit Profile
        </button>
        <div className="photo-status">
          <span>Status:</span> <strong className={status.toLowerCase()}>{status === 'available' ? 'Available' : 'Unavailable'}</strong>
        </div>
      </div>

      <div className="driver-info">
        <h2>{driverInfo.name || "Driver"}</h2>
        <p>⭐ 4.7 (1247 rides)</p>
        <p>
          <strong>Vehicle:</strong> {driverInfo.vehicle_type || "Not specified"}
        </p>
        <p>
          <strong>License No:</strong> {driverInfo.license_no || "N/A"}
        </p>
        <p>
          <strong>Experience:</strong> {driverInfo.experience} years
        </p>
        <p>
          <strong>Location:</strong> {driverInfo.location || "Unknown"}
        </p>
        <p>
          <strong>Contact:</strong> {driverInfo.phone || "N/A"}
        </p>
        <p>
          <strong>Email:</strong> {driverInfo.email || "N/A"}
        </p>
        
        <div className="status-display" />
      </div>

      {/* 📊 Revenue Summary (single card) */}
      <div className="earnings-column">
        <div className="earning-card">
          <div className="earning-card-header">
            <h4>Revenue</h4>
            <span className="badge primary">All time</span>
          </div>
          <div className="amount">
            {isLoadingRevenue ? 'Loading…' : (revenue != null ? `$${revenue}` : '$0')}
          </div>
          <div className="subtext">Total earnings from completed trips</div>
        </div>
      </div>

      {isEditing && createPortal((
        <div className="dp-backdrop">
          <div className="dp-modal" onClick={(e) => e.stopPropagation()}>
            <div className="dp-modal-header">
              <h3>Edit Profile</h3>
              <button className="icon-btn" onClick={() => { if (!isSaving) { setIsEditing(false); setPhotoFile(null); } }}>&times;</button>
            </div>
            <div className="dp-modal-body">
              <div className="form-grid">
                <label>
                  <span>Name</span>
                  <input
                    type="text"
                    value={editData?.name || ''}
                    onChange={(e) => setEditData({ ...editData, name: e.target.value })}
                  />
                </label>
                <label>
                  <span>Phone</span>
                  <input
                    type="text"
                    value={editData?.phone || ''}
                    onChange={(e) => setEditData({ ...editData, phone: e.target.value })}
                  />
                </label>
                <label>
                  <span>Vehicle Type</span>
                  <input
                    type="text"
                    value={editData?.vehicle_type || ''}
                    onChange={(e) => setEditData({ ...editData, vehicle_type: e.target.value })}
                  />
                </label>
                <label>
                  <span>Experience (years)</span>
                  <input
                    type="number"
                    value={editData?.experience || ''}
                    onChange={(e) => setEditData({ ...editData, experience: e.target.value })}
                  />
                </label>
                <label className="wide">
                  <span>Location</span>
                  <input
                    type="text"
                    value={editData?.location || ''}
                    onChange={(e) => setEditData({ ...editData, location: e.target.value })}
                  />
                </label>
                <label className="wide">
                  <span>Profile Photo</span>
                  <input
                    type="file"
                    accept="image/*"
                    onChange={(e) => {
                      const file = e.target.files?.[0] || null;
                      setPhotoFile(file);
                      if (file) {
                        const reader = new FileReader();
                        reader.onload = (ev) => setPhotoPreview(ev.target?.result || null);
                        reader.readAsDataURL(file);
                      } else {
                        setPhotoPreview(null);
                      }
                    }}
                  />
                </label>
              </div>
            </div>
            <div className="dp-modal-footer">
              <button className="btn ghost" onClick={() => { setIsEditing(false); setPhotoFile(null); setPhotoPreview(null); }} disabled={isSaving}>Cancel</button>
              <button
                className="btn primary"
                disabled={isSaving}
                onClick={async () => {
                  try {
                    setIsSaving(true);
                    const payload = {
                      name: editData.name,
                      phone: editData.phone,
                      vehicle_type: editData.vehicle_type,
                      experience: editData.experience,
                      location: editData.location,
                      email: editData.email || (localStorage.getItem('userEmail') || localStorage.getItem('email'))
                    };
                    
                    console.log('🔄 Updating driver profile:', payload);
                    
                    // Update profile data
                    const response = await fetch('http://localhost/RoutePro-backend(02)/public/driver/profile', {
                      method: 'PUT',
                      headers: { 'Content-Type': 'application/json' },
                      body: JSON.stringify(payload)
                    });

                    const result = await response.json();
                    
                    if (!response.ok || !result.success) {
                      throw new Error(result.message || 'Failed to update profile');
                    }

                    console.log('✅ Profile updated successfully');

                    // Handle photo upload if provided
                    if (photoFile) {
                      console.log('📸 Uploading photo...');
                      const form = new FormData();
                      form.append('email', payload.email);
                      form.append('photo', photoFile);
                      
                      const photoResponse = await fetch('http://localhost/RoutePro-backend(02)/public/driver/photo', {
                        method: 'POST',
                        body: form
                      });
                      
                      if (photoResponse.ok) {
                        console.log('✅ Photo uploaded successfully');
                      } else {
                        console.warn('⚠️ Photo upload failed, but profile was updated');
                      }
                    }

                    // Refresh profile to get updated data from backend
                    console.log('🔄 Refreshing profile data...');
                    const refreshed = await fetch(`http://localhost/RoutePro-backend(02)/public/driver/profile?email=${encodeURIComponent(payload.email)}`, {
                      method: 'GET',
                      headers: { 'Content-Type': 'application/json' }
                    });
                    
                    if (refreshed.ok) {
                      const refreshedData = await refreshed.json();
                      if (refreshedData.success && refreshedData.data) {
                        setDriverInfo({
                          name: refreshedData.data.name || payload.name,
                          phone: refreshedData.data.phone || payload.phone,
                          email: refreshedData.data.email || payload.email,
                          status: refreshedData.data.status || 'available',
                          license_no: refreshedData.data.license_no || '',
                          vehicle_type: refreshedData.data.vehicle_type || payload.vehicle_type,
                          experience: refreshedData.data.experience || payload.experience,
                          location: refreshedData.data.location || payload.location,
                          photoUrl: (refreshedData.data.photo && refreshedData.data.photo.trim() !== '') ? refreshedData.data.photo : null
                        });
                        console.log('✅ Profile data refreshed successfully');
                        
                        // Update dashboard name if setUserName is provided
                        if (setUserName && refreshedData.data.name) {
                          setUserName(refreshedData.data.name);
                        }
                      } else {
                        // Fallback to local data if refresh fails
                        setDriverInfo({ ...driverInfo, ...payload });
                        
                        // Update dashboard name if setUserName is provided
                        if (setUserName && payload.name) {
                          setUserName(payload.name);
                        }
                      }
                    } else {
                      // Fallback to local data if refresh fails
                      setDriverInfo({ ...driverInfo, ...payload });
                      
                      // Update dashboard name if setUserName is provided
                      if (setUserName && payload.name) {
                        setUserName(payload.name);
                      }
                    }
                    
                    setIsEditing(false);
                    setPhotoFile(null);
                    setPhotoPreview(null);
                    
                    alert('Profile updated successfully!');
                    
                  } catch (err) {
                    console.error('❌ Failed to save profile:', err);
                    alert(`Could not save profile: ${err.message || 'Please try again.'}`);
                  } finally {
                    setIsSaving(false);
                  }
                }}
              >
                {isSaving ? 'Saving…' : 'Save Changes'}
              </button>
            </div>
          </div>
        </div>
      ), document.body)}
    </div>
  );
};

export default DriverHeader;
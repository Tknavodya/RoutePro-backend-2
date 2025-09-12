import React, { useState, useEffect } from "react";
import { useNavigate } from "react-router-dom";
import "./GuideHeader.css";
import { apiMethods } from "../../../../utils/api-client";

const GuideHeader = ({ status, setStatus, userId, setUserName }) => {
  const [revenue, setRevenue] = useState(null);
  const [isLoadingRevenue, setIsLoadingRevenue] = useState(false);
  const [guiderInfo, setGuiderInfo] = useState({
    name: '',
    phone: '',
    email: '',
    status: '',
    license_no: '',
    nic: '',
    languages: '',
    experience: '',
    location: '',
    photoUrl: ''
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
                     'priya@guide.com'; // Fallback for testing

    console.log('🚀 Fetching guide data for logged-in user:', userEmail);

    // Use proper GuideController endpoint with email parameter
    fetch(`http://localhost/RoutePro-backend(02)/public/guide/profile?email=${encodeURIComponent(userEmail)}`, {
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
          setGuiderInfo({
            name: data.data.name || '',
            phone: data.data.phone || '',
            email: data.data.email || '',
            status: data.data.status || 'available',
            license_no: data.data.license_no || '',
            nic: data.data.nic || '',
            languages: data.data.languages || '',
            experience: data.data.experience || '',
            location: data.data.location || '',
            photoUrl: (data.data.photo && data.data.photo.trim() !== '') ? data.data.photo : null
          });
          setStatus(data.data.status || 'available'); // Update status display
        } else {
          console.error('Error fetching guide info:', data.message || 'Unknown error');
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

  useEffect(() => {
    // Fetch guide revenue
    const userEmail =
      localStorage.getItem('userEmail') ||
      localStorage.getItem('email') ||
      'priya@guide.com';
    const controller = new AbortController();
    const signal = controller.signal;
    const fetchRevenue = async () => {
      setIsLoadingRevenue(true);
      try {
        const resp = await fetch(`http://localhost/RoutePro-backend(02)/public/guide/revenue?email=${encodeURIComponent(userEmail)}`, { method: 'GET', headers: { 'Content-Type': 'application/json' }, signal });
        if (!resp.ok) throw new Error(`HTTP ${resp.status}`);
        const data = await resp.json();
        setRevenue((data && data.data && (data.data.revenue ?? data.data.totalRevenue)) ?? null);
      } catch (e) {
        console.error('Guide revenue fetch error:', e);
        setRevenue(null);
      } finally {
        setIsLoadingRevenue(false);
      }
    };
    fetchRevenue();
    return () => controller.abort();
  }, []);

  // Update local guiderInfo status when the status prop changes
  useEffect(() => {
    setGuiderInfo(prevInfo => ({
      ...prevInfo,
      status: status
    }));
  }, [status]);

  const handleLogout = () => {
    navigate("/homepage");
  };

  const effectiveUserId = userId || localStorage.getItem("userId");

  return (
    <div className="driver-header">
      <div className="photo-wrapper">
        <img src={photoPreview || (guiderInfo.photoUrl && guiderInfo.photoUrl.trim() !== '' ? `http://localhost${guiderInfo.photoUrl}?t=${Date.now()}` : 'http://localhost/RoutePro-backend(02)/public/images/defaults/default.png')} alt="Guide" className="driver-photo" />
        <button type="button" className="edit-overlay" onClick={(e) => { e.stopPropagation(); setEditData(guiderInfo); setIsEditing(true); }}>Edit Profile</button>
        <div className="photo-status"><span>Status:</span> <strong className={status.toLowerCase()}>{status === 'available' ? 'Available' : 'Unavailable'}</strong></div>
      </div>
      <div className="guide-info">
        <h2>{guiderInfo.name}</h2>
        <p>⭐ 4.9 (362 tours)</p>
        <p>
          <strong>license_no:</strong>{" "}
          {guiderInfo.license_no || "Not specified"}
        </p>
        <p>
          <strong>location:</strong> {guiderInfo.location || "Unknown"}
        </p>
        <p>
          <strong>Guiding Experience:</strong> {guiderInfo.experience} years
        </p>
        <p>
          <strong>Contact:</strong> {guiderInfo.phone || "N/A"}
        </p>
        {/* <div className="status-display">
    <span>Status: </span>
    <span className={`status-label ${status.toLowerCase()}`}>{status}</span>
  </div> */}
      </div>

      {/* 📊 Revenue Summary (single card) */}
      <div className="earnings-column">
        <div className="earning-card">
          <div className="earning-card-header">
            <h4>Revenue</h4>
            <span className="badge primary">All time</span>
          </div>
          <div className="amount">{isLoadingRevenue ? 'Loading…' : (revenue != null ? `Rs ${revenue}` : 'Rs 0')}</div>
          <div className="subtext">Total earnings from completed tours</div>
        </div>
      </div>

      {isEditing && (
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
                  <input type="text" value={editData?.name || ''} onChange={(e) => setEditData({ ...editData, name: e.target.value })} />
                </label>
                <label>
                  <span>Phone</span>
                  <input type="text" value={editData?.phone || ''} onChange={(e) => setEditData({ ...editData, phone: e.target.value })} />
                </label>
                <label>
                  <span>Experience (years)</span>
                  <input type="number" value={editData?.experience || ''} onChange={(e) => setEditData({ ...editData, experience: e.target.value })} />
                </label>
                <label className="wide">
                  <span>Languages</span>
                  <input type="text" value={editData?.languages || ''} onChange={(e) => setEditData({ ...editData, languages: e.target.value })} />
                </label>
                <label className="wide">
                  <span>Location</span>
                  <input type="text" value={editData?.location || ''} onChange={(e) => setEditData({ ...editData, location: e.target.value })} />
                </label>
                <label className="wide">
                  <span>Profile Photo</span>
                  <input type="file" accept="image/*" onChange={(e) => {
                    const file = e.target.files?.[0] || null;
                    setPhotoFile(file);
                    if (file) {
                      const reader = new FileReader();
                      reader.onload = (ev) => setPhotoPreview(ev.target?.result || null);
                      reader.readAsDataURL(file);
                    } else {
                      setPhotoPreview(null);
                    }
                  }} />
                </label>
              </div>
            </div>
            <div className="dp-modal-footer">
              <button className="btn ghost" onClick={() => { setIsEditing(false); setPhotoFile(null); setPhotoPreview(null); }} disabled={isSaving}>Cancel</button>
              <button className="btn primary" disabled={isSaving} onClick={async () => {
                try {
                  setIsSaving(true);
                  
                  // Get user email for the API call
                  const userEmail = localStorage.getItem('userEmail') || 
                                   localStorage.getItem('email') ||
                                   'priya@guide.com';
                  
                  const payload = {
                    name: editData.name,
                    phone: editData.phone,
                    experience: editData.experience,
                    languages: editData.languages,
                    location: editData.location,
                    email: userEmail
                  };

                  console.log('🔄 Updating guide profile:', payload);

                  // Call the enhanced updateProfile endpoint
                  const response = await fetch('http://localhost/RoutePro-backend(02)/public/guide/profile', { 
                    method: 'PUT', 
                    headers: { 'Content-Type': 'application/json' }, 
                    body: JSON.stringify(payload) 
                  });

                  const result = await response.json();

                  if (!response.ok || !result.success) {
                    throw new Error(result.message || 'Failed to update profile');
                  }

                  console.log('✅ Guide profile updated successfully');

                  // Handle photo upload if a file was selected
                  if (photoFile) {
                    console.log('📸 Uploading guide photo...');
                    const form = new FormData();
                    form.append('email', userEmail);
                    form.append('photo', photoFile);
                    
                    try {
                      await fetch('http://localhost/RoutePro-backend(02)/public/guide/photo', { 
                        method: 'POST', 
                        body: form 
                      });
                      console.log('✅ Guide photo uploaded successfully');
                    } catch (photoError) {
                      console.error('❌ Photo upload failed:', photoError);
                    }
                  }

                  // Refresh guide data from backend
                  const refreshResponse = await fetch(`http://localhost/RoutePro-backend(02)/public/guide/profile?email=${encodeURIComponent(userEmail)}`, { 
                    method: 'GET', 
                    headers: { 'Content-Type': 'application/json' } 
                  });

                  if (refreshResponse.ok) {
                    const refreshedData = await refreshResponse.json();
                    if (refreshedData.success && refreshedData.data) {
                      const updatedInfo = {
                        name: refreshedData.data.name || payload.name,
                        phone: refreshedData.data.phone || payload.phone,
                        email: refreshedData.data.email || payload.email,
                        status: refreshedData.data.status || 'available',
                        license_no: refreshedData.data.license_no || '',
                        nic: refreshedData.data.nic || '',
                        languages: refreshedData.data.languages || payload.languages,
                        experience: refreshedData.data.experience || payload.experience,
                        location: refreshedData.data.location || payload.location,
                        photoUrl: (refreshedData.data.photo && refreshedData.data.photo.trim() !== '') ? refreshedData.data.photo : null
                      };
                      
                      setGuiderInfo(updatedInfo);
                      
                      // Update dashboard name if setUserName function is available
                      if (setUserName && updatedInfo.name) {
                        setUserName(updatedInfo.name);
                        console.log('✅ Dashboard name updated to:', updatedInfo.name);
                      }
                    }
                  }

                  setIsEditing(false);
                  setPhotoFile(null);
                  setPhotoPreview(null);
                  
                  alert('Profile updated successfully!');
                  
                } catch (err) {
                  console.error('❌ Failed to save guide profile:', err);
                  alert(`Could not save profile: ${err.message}. Please try again.`);
                } finally {
                  setIsSaving(false);
                }
              }}>Save Changes</button>
            </div>
          </div>
        </div>
      )}
    </div>
  );
};

export default GuideHeader;

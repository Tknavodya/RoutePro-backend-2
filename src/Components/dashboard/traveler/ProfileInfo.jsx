import React, { useState, useEffect } from 'react';
import { createPortal } from 'react-dom';
import styles from './TravelerDashboard.module.css';

const ProfileInfo = ({ onProfileUpdate }) => {
  const [travellerInfo, setTravellerInfo] = useState({
    name: '',
    email: '',
    phone: '',
    member_since: '',
    user_id: '',
    photoUrl: ''
  });
  const [showEditModal, setShowEditModal] = useState(false);
  const [editData, setEditData] = useState(null);
  const [isSaving, setIsSaving] = useState(false);
  const [photoFile, setPhotoFile] = useState(null);
  const [photoPreview, setPhotoPreview] = useState(null);

  useEffect(() => {
    loadProfileData();
  }, []);

  const loadProfileData = () => {
    // Get the logged-in user's email from localStorage (set during login)
    const userEmail = localStorage.getItem('userEmail') || 
                     localStorage.getItem('email') ||
                     'test.traveller@example.com'; // Using an email that exists in DB

    console.log('🚀 Fetching traveller profile data:', userEmail);

    // Use proper TravellerController endpoint with email parameter
    fetch(`http://localhost/RoutePro-backend(02)/public/traveller/profile?email=${encodeURIComponent(userEmail)}`, {
      method: 'GET',
      headers: {
        'Content-Type': 'application/json',
      }
    })
      .then((res) => {
        console.log('📡 Response status:', res.status);
        if (!res.ok) {
          throw new Error(`HTTP error! status: ${res.status}`);
        }
        return res.json();
      })
      .then((data) => {
        console.log('🔍 Full API Response:', data);
        if (data.success && data.data) {
          console.log('✅ Data received:', data.data);
          setTravellerInfo({
            name: data.data.name || 'Unknown',
            email: data.data.email || 'No email',
            phone: data.data.phone || 'Not provided',
            member_since: data.data.member_since ? new Date(data.data.member_since).getFullYear() : 'Unknown',
            user_id: data.data.user_id || '',
            photoUrl: (data.data.photo && data.data.photo.trim() !== '') ? data.data.photo : null
          });
        } else {
          console.error('❌ Error fetching traveller profile:', data.message || 'Unknown error');
          console.error('❌ Full response:', data);
          
          // Set fallback data
          setTravellerInfo({
            name: 'Unknown',
            email: 'No email',
            phone: 'Not provided',
            member_since: 'Unknown',
            user_id: '',
            photoUrl: null
          });
        }
      })
      .catch((err) => {
        console.error('❌ Fetch error:', err);
        console.error('❌ Error details:', err.message);
        
        // Set fallback data on error
        setTravellerInfo({
          name: 'Unknown',
          email: 'No email',
          phone: 'Not provided',
          member_since: 'Unknown',
          user_id: '',
          photoUrl: null
        });
      });
  };

  const handleEditProfile = () => {
    console.log('🔧 Opening edit modal with current traveller info:', travellerInfo);
    setEditData({
      ...travellerInfo,
      name: travellerInfo.name || '',
      phone: travellerInfo.phone || '',
      email: travellerInfo.email || ''
    });
    setShowEditModal(true);
  };

  const handleCloseEdit = () => {
    if (!isSaving) {
      setShowEditModal(false);
      setPhotoFile(null);
      setPhotoPreview(null);
    }
  };

  const handleProfileUpdated = () => {
    loadProfileData(); // Reload profile data after update
    if (onProfileUpdate) {
      onProfileUpdate(); // Also notify parent component
    }
  };

  return (
    <section className={styles.profileSection}>
      <div className={styles.profileContainer}>
        {/* Left: Avatar with Edit Overlay */}
        <div className={styles.photoWrapper}>
          <img
            src={photoPreview || (travellerInfo.photoUrl && travellerInfo.photoUrl.trim() !== '' 
              ? `http://localhost${travellerInfo.photoUrl}?t=${Date.now()}` 
              : 'http://localhost/RoutePro-backend(02)/public/images/defaults/default.png')}
            alt={travellerInfo.name}
            className={styles.profileImage}
          />
          <button
            type="button"
            className={styles.editOverlay}
            onClick={handleEditProfile}
          >
            Edit Profile
          </button>
        </div>

        {/* Middle: Profile Info */}
        <div className={styles.travelerInfo}>
          <h2>{travellerInfo.name || "Traveler"}</h2>
          <p>⭐ Member since {travellerInfo.member_since}</p>
          <p><strong>Email:</strong> {travellerInfo.email}</p>
          <p><strong>Phone:</strong> {travellerInfo.phone}</p>
        </div>

        {/* Right: Travel Stats */}
        <div className={styles.statsBlock}>
          <div>
            <strong>12</strong>
            <span>Trips Completed</span>
          </div>
        </div>
      </div>
      
      {showEditModal && createPortal((
        <div className={styles.modalBackdrop}>
          <div className={styles.modalContent} onClick={(e) => e.stopPropagation()}>
            <div className={styles.modalHeader}>
              <h3>Edit Profile</h3>
              <button 
                className={styles.iconBtn} 
                onClick={handleCloseEdit}
                disabled={isSaving}
              >
                &times;
              </button>
            </div>
            <div className={styles.modalBody}>
              <div className={styles.formGrid}>
                <label>
                  <span>Name *</span>
                  <input
                    type="text"
                    value={editData?.name || ''}
                    onChange={(e) => {
                      console.log('📝 Name changed to:', e.target.value);
                      setEditData({ ...editData, name: e.target.value });
                    }}
                    required
                    placeholder="Enter your full name"
                  />
                </label>
                <label>
                  <span>Phone *</span>
                  <input
                    type="tel"
                    value={editData?.phone || ''}
                    onChange={(e) => {
                      console.log('📞 Phone changed to:', e.target.value);
                      setEditData({ ...editData, phone: e.target.value });
                    }}
                    pattern="[0-9]{10,15}"
                    required
                    placeholder="Enter your phone number"
                  />
                </label>
                <label className={styles.wide}>
                  <span>Email Address</span>
                  <input
                    type="email"
                    value={editData?.email || ''}
                    disabled
                    className={styles.disabledInput}
                  />
                  <small className={styles.helpText}>Email cannot be changed</small>
                </label>
                <label className={styles.wide}>
                  <span>Profile Photo</span>
                  <input
                    type="file"
                    accept="image/*"
                    onChange={(e) => {
                      const file = e.target.files?.[0] || null;
                      console.log('📸 Photo selected:', file?.name);
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
                  {(photoPreview || travellerInfo.photoUrl) && (
                    <div style={{ marginTop: '8px' }}>
                      <img 
                        src={photoPreview || `http://localhost${travellerInfo.photoUrl}`}
                        alt="Preview" 
                        style={{ 
                          width: '100px', 
                          height: '100px', 
                          objectFit: 'cover', 
                          borderRadius: '8px',
                          border: '2px solid #ddd'
                        }} 
                      />
                    </div>
                  )}
                </label>
              </div>
            </div>
            <div className={styles.modalFooter}>
              <button 
                className={`${styles.btn} ${styles.ghost}`} 
                onClick={handleCloseEdit}
                disabled={isSaving}
              >
                Cancel
              </button>
              <button
                className={`${styles.btn} ${styles.primary}`}
                disabled={isSaving}
                onClick={async () => {
                  try {
                    setIsSaving(true);
                    
                    const userEmail = localStorage.getItem('userEmail') || 
                                     localStorage.getItem('email') ||
                                     'test.traveller@example.com';

                    // Validate required fields
                    if (!editData?.name || !editData?.phone) {
                      alert('Please fill in all required fields (Name and Phone)');
                      return;
                    }

                    const payload = {
                      name: editData.name.trim(),
                      phone: editData.phone.trim()
                    };
                    
                    console.log('🔄 Updating traveller profile:', payload);
                    console.log('📧 Using email:', userEmail);
                    
                    // Update profile data
                    const response = await fetch(`http://localhost/RoutePro-backend(02)/public/traveller/profile?email=${encodeURIComponent(userEmail)}`, {
                      method: 'PUT',
                      headers: { 
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                      },
                      body: JSON.stringify(payload)
                    });

                    console.log('📡 Update response status:', response.status);
                    const result = await response.json();
                    console.log('📊 Update response data:', result);
                    
                    if (!response.ok || !result.success) {
                      throw new Error(result.message || 'Failed to update profile');
                    }

                    console.log('✅ Profile updated successfully');

                    // Handle photo upload if provided
                    if (photoFile) {
                      console.log('📸 Uploading photo...');
                      const form = new FormData();
                      form.append('email', userEmail);
                      form.append('photo', photoFile);
                      
                      const photoResponse = await fetch('http://localhost/RoutePro-backend(02)/public/traveller/photo', {
                        method: 'POST',
                        body: form
                      });
                      
                      console.log('📡 Photo upload response status:', photoResponse.status);
                      const photoResult = await photoResponse.json();
                      console.log('📊 Photo upload response data:', photoResult);
                      
                      if (photoResponse.ok && photoResult.success) {
                        console.log('✅ Photo uploaded successfully');
                      } else {
                        console.warn('⚠️ Photo upload failed:', photoResult.message);
                        alert(`Profile updated but photo upload failed: ${photoResult.message || 'Unknown error'}`);
                      }
                    }

                    // Refresh profile data
                    console.log('🔄 Refreshing profile data...');
                    loadProfileData();
                    
                    if (onProfileUpdate) {
                      onProfileUpdate();
                    }
                    
                    setShowEditModal(false);
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
    </section>
  );
};

export default ProfileInfo;

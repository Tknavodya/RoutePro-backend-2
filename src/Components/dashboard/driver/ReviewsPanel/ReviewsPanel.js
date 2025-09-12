import React from 'react';
import './ReviewsPanel.css';

const reviews = [
  {
    id: 1,
    name: 'Alice Johnson',
    date: '2024-06-28',
    comment: 'Great ride, very polite driver!',
    status: 'Pending',
    response: ''
  },
  {
    id: 2,
    name: 'Bob Smith',
    date: '2024-06-27',
    comment: 'Smooth experience. Recommended!',
    status: 'Responded',
    response: 'Thanks Bob! Glad you enjoyed the ride.'
  }
];

const ReviewsPanel = () => (
  <div className="reviews-panel">
    <h2>Reviews</h2>
    {reviews.map(review => (
      <div key={review.id} className="review-card">
        <p><strong>{review.name}</strong> <span>{review.date}</span></p>
        <p>"{review.comment}"</p>
      </div>
    ))}
  </div>
);

export default ReviewsPanel;

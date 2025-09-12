import React, { useState } from "react";
import "./ReviewSection.css";


// Single review card
const ReviewCard = ({ name, rating, comment }) => {
  const renderStars = (rating) => {
    const totalStars = 5;
    let stars = [];
    for (let i = 1; i <= totalStars; i++) {
      stars.push(
        <span
          key={i}
          className={`star ${i <= rating ? "filled" : ""}`}
          aria-hidden="true"
        >
          &#9733;
        </span>
      );
    }
    return stars;
  };

  return (
    <div className="review-card">
      <div className="review-header">
        <h4>{name}</h4>
        <div className="rating">{renderStars(rating)}</div>
      </div>
      <p className="comment">"{comment}"</p>
    </div>
  );
};

// Form to add new review
const StarRatingInput = ({ rating, setRating }) => {
  const handleStarClick = (index) => {
    setRating(index);
  };

  return (
    <div className="star-input">
      {[1, 2, 3, 4, 5].map((star) => (
        <span
          key={star}
          className={`star ${star <= rating ? "filled" : ""}`}
          onClick={() => handleStarClick(star)}
        >
          &#9733;
        </span>
      ))}
    </div>
  );
};

// Form to add new review with clickable stars
const ReviewForm = ({ addReview }) => {
  const [name, setName] = useState("");
  const [rating, setRating] = useState(5);
  const [comment, setComment] = useState("");

  const handleSubmit = (e) => {
    e.preventDefault();
    if (name.trim() && comment.trim() && rating > 0) {
      addReview({ name, rating, comment });
      setName("");
      setRating(5);
      setComment("");
    }
  };

  return (
    <form className="review-form" onSubmit={handleSubmit}>
      <h3>Add Your Review</h3>
      <input
        type="text"
        placeholder="Your Name"
        value={name}
        onChange={(e) => setName(e.target.value)}
        required
      />

      <label>Your Rating:</label>
      <StarRatingInput rating={rating} setRating={setRating} />

      <textarea
        placeholder="Your Comment"
        value={comment}
        onChange={(e) => setComment(e.target.value)}
        rows="4"
        required
      ></textarea>
      <button type="submit">Submit Review</button>
    </form>
  );
};

// Reviews section with form
export const ReviewSection = ({ title, reviews: initialReviews }) => {
  const [reviews, setReviews] = useState(initialReviews || []);

  const addReview = (newReview) => {
    setReviews([newReview, ...reviews]);
  };

  return (
    <section className="reviews-section">
      <div className="reviews-container">
        <h2 className="reviews-title">{title}</h2>
        <ReviewForm addReview={addReview} />
        <div className="reviews-list">
          {reviews.length > 0 ? (
            reviews.map((review, index) => (
              <ReviewCard
                key={index}
                name={review.name}
                rating={review.rating}
                comment={review.comment}
              />
            ))
          ) : (
            <p className="no-reviews">No reviews yet.</p>
          )}
        </div>
      </div>
    </section>
  );
};

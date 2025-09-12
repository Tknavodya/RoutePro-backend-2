import React from "react";

const TestDriverDetails = () => {
  return (
    <div style={{ padding: '2rem', textAlign: 'center' }}>
      <h1>Driver Details Test Page</h1>
      <p>This is a test to check if the route works.</p>
      <button onClick={() => window.history.back()}>Go Back</button>
    </div>
  );
};

export default TestDriverDetails;

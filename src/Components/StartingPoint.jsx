export default function StartingPoint({ location, setLocation }) {
  return (
    <div className="card">
      <h2>Starting Point</h2>
      <input
        type="text"
        placeholder="Enter your starting location"
        value={location}
        onChange={(e) => setLocation(e.target.value)}
      />
    </div>
  );
}

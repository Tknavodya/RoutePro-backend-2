import React, { useEffect, useRef } from "react";

export default function MapComponent({ location, budget, setAttractions }) {
  const mapRef = useRef(null);
  const mapInstance = useRef(null);
  const markers = useRef([]);
  const circleRef = useRef(null);

 const budgetRadius = {
  1000: 2000,  // 2 km
  3000: 3000,  // 3 km
  5000: 5000,  // 5 km
};

  useEffect(() => {
    if (!window.google) {
      const script = document.createElement("script");
      script.src = `https://maps.googleapis.com/maps/api/js?key=${process.env.REACT_APP_GOOGLE_MAPS_API_KEY}&libraries=places`;
      script.async = true;
      script.onload = () => initMap();
      document.head.appendChild(script);
    } else {
      initMap();
    }
  }, []);

  useEffect(() => {
    if (mapInstance.current && location) {
      const geocoder = new window.google.maps.Geocoder();
      geocoder.geocode({ address: location }, (results, status) => {
        if (status === "OK") {
          const center = results[0].geometry.location;
          mapInstance.current.setCenter(center);
          mapInstance.current.setZoom(12);

          // Scroll into view
          if (mapRef.current) {
            mapRef.current.scrollIntoView({ behavior: "smooth", block: "center" });
          }

          // Draw or update circle
          if (circleRef.current) {
            circleRef.current.setMap(null);
          }
          circleRef.current = new window.google.maps.Circle({
            map: mapInstance.current,
            center,
            radius: budgetRadius[budget] || 20000,
            fillColor: "#2196f3",
            fillOpacity: 0.15,
            strokeColor: "#1976d2",
            strokeOpacity: 0.8,
            strokeWeight: 2,
          });

          const service = new window.google.maps.places.PlacesService(mapInstance.current);
          const types = ["hindu_temple", "buddhist_temple", "beach", "museum", "park"];
          let allResults = [];

          clearMarkers();

          types.forEach((type) => {
            const request = {
              location: center,
              radius: budgetRadius[budget] || 20000,
              type: type,
            };

            service.nearbySearch(request, (results, status) => {
              if (status === window.google.maps.places.PlacesServiceStatus.OK) {
                results.forEach((place) => {
                  if (place.rating && place.rating >= 4.0) {
                    createMarker(place);
                    allResults.push({
                      name: place.name,
                      type: type,
                      address: place.vicinity || "",
                      rating: place.rating,
                    });
                  }
                });
                // Update parent list
                setAttractions([...allResults]);
              }
            });
          });
        } else {
          console.log("Geocoding failed:", status);
        }
      });
    }
  }, [location, budget, setAttractions]);

  const initMap = () => {
    mapInstance.current = new window.google.maps.Map(mapRef.current, {
      center: { lat: 7.8731, lng: 80.7718 }, // Sri Lanka default
      zoom: 7,
    });
  };

  const createMarker = (place) => {
    const marker = new window.google.maps.Marker({
      map: mapInstance.current,
      position: place.geometry.location,
      title: place.name,
    });

    const infowindow = new window.google.maps.InfoWindow({
      content: `<strong>${place.name}</strong><br>
                Rating: ${place.rating || "N/A"}<br>
                ${place.vicinity || ""}`,
    });

    marker.addListener("click", () => {
      infowindow.open(mapInstance.current, marker);
    });

    markers.current.push(marker);
  };

  const clearMarkers = () => {
    for (let marker of markers.current) {
      marker.setMap(null);
    }
    markers.current = [];
  };

  return <div ref={mapRef} className="map" style={{ height: "500px", width: "100%" }}></div>;
}

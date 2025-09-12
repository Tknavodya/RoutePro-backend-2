import React, { useState, useEffect, useRef } from "react";
import { GoogleMap, LoadScript, DirectionsRenderer } from "@react-google-maps/api";

const containerStyle = {
  width: "100%",
  height: "550px",
  borderRadius: "10px",
};

const center = {
  lat: 7.8731,  // Sri Lanka latitude
  lng: 80.7718  // Sri Lanka longitude
};

const MapComponent = ({ origin, destination, setRouteDetails, setNearbyPlaces }) => {
  const [directions, setDirections] = useState(null);
  const mapRef = useRef(null);

  useEffect(() => {
    if (origin && destination) {
      const directionsService = new window.google.maps.DirectionsService();

      directionsService.route(
        {
          origin,
          destination,
          travelMode: window.google.maps.TravelMode.DRIVING
        },
        (result, status) => {
          if (status === "OK") {
            setDirections(result);

            const route = result.routes[0].legs[0];
            const bounds = result.routes[0].bounds;

            setRouteDetails({
              distance: route.distance.text,
              duration: route.duration.text,
              bounds: bounds,
            });

            // Calculate midpoint for nearby places
            const midLat = (route.start_location.lat() + route.end_location.lat()) / 2;
            const midLng = (route.start_location.lng() + route.end_location.lng()) / 2;

            const service = new window.google.maps.places.PlacesService(mapRef.current);

            const request = {
              location: { lat: midLat, lng: midLng },
              radius: 20000, // 20km
              type: ["tourist_attraction"],
            };

            // Uncomment to fetch places
            // try{
            //   service.nearbySearch(request, (results, placesStatus) => {
            //   if (placesStatus === window.google.maps.places.PlacesServiceStatus.OK) {
            //     setNearbyPlaces(results);
            //   } else {
            //     console.error("Places request failed: ", placesStatus);
            //     setNearbyPlaces([]);
            //   }
            // });
            // }catch(err){
            //   console.log(err);
            // }
          } else {
            console.error("Directions request failed due to: " + status);
            setRouteDetails({ distance: "", duration: "", bounds: null });
            setNearbyPlaces([]);
          }
        }
      );
    }
  }, [origin, destination, setRouteDetails, setNearbyPlaces]);
   const libraries = ["places"];


  return (
    <LoadScript googleMapsApiKey={process.env.REACT_APP_GOOGLE_MAPS_API_KEY} libraries={libraries}>
      <GoogleMap
        mapContainerStyle={containerStyle}
        center={center}
        zoom={7}
        onLoad={(map) => (mapRef.current = map)}
      >
        {directions && <DirectionsRenderer directions={directions} />}
      </GoogleMap>
    </LoadScript>
  );
};

export default MapComponent;
"use client"

import { useState, useRef } from "react"
import { MapPin, Plus, ChevronDown, MoreHorizontal, Square, List, ChevronLeft, Search } from "lucide-react"
import "./PlacesSelector.css";

const PlacesSelector = ({ nearbyPlaces = [], setNearbyPlaces }) => {
  const [selectedPlaces, setSelectedPlaces] = useState([])
  const [searchQuery, setSearchQuery] = useState("")
  const [searchResults, setSearchResults] = useState([])
  const [isRecommendedOpen, setIsRecommendedOpen] = useState(true)
  const [isSearching, setIsSearching] = useState(false)
  const scrollRef = useRef(null)

  // Default places to show when nearbyPlaces is empty
  const defaultPlaces = [
    {
      name: "Sigiriya Rock",
      photos: null,
    },
    {
      name: "Temple of Tooth",
      photos: null,
    },
    {
      name: "Ella Rock",
      photos: null,
    },
    {
      name: "Nine Arch Bridge",
      photos: null,
    },
  ]

  // Use nearbyPlaces if available, otherwise use default places
  const placesToShow = nearbyPlaces && nearbyPlaces.length > 0 ? nearbyPlaces : defaultPlaces

  // Mock Places API search function with images
  const searchPlaces = async (query) => {
    if (!query.trim()) {
      setSearchResults([])
      return
    }

    setIsSearching(true)

    try {
      // Simulate API call delay
      await new Promise((resolve) => setTimeout(resolve, 500))

      // Mock API response with realistic image URLs
      const mockResults = [
        {
          id: Date.now() + 1,
          name: `${query} Beach`,
          image: `https://picsum.photos/200/120?random=${Date.now() + 1}`,
          description: "Beautiful beach location",
        },
        {
          id: Date.now() + 2,
          name: `${query} Temple`,
          image: `https://picsum.photos/200/120?random=${Date.now() + 2}`,
          description: "Historic temple site",
        },
        {
          id: Date.now() + 3,
          name: `${query} Park`,
          image: `https://picsum.photos/200/120?random=${Date.now() + 3}`,
          description: "Natural park area",
        },
      ]

      setSearchResults(mockResults)
    } catch (error) {
      console.error("Error fetching places:", error)
      setSearchResults([])
    } finally {
      setIsSearching(false)
    }
  }

  const handleSearchChange = (e) => {
    const value = e.target.value
    setSearchQuery(value)
    searchPlaces(value)
  }

  const addPlace = (place) => {
    if (!selectedPlaces.find((p) => p.id === place.id)) {
      const newPlace = {
        ...place,
        notes: "Add notes, links, etc. here",
      }
      setSelectedPlaces([...selectedPlaces, newPlace])
    }
    setSearchQuery("")
    setSearchResults([])
  }

  const removePlace = (placeId) => {
    setSelectedPlaces(selectedPlaces.filter((p) => p.id !== placeId))
  }

  const scrollLeft = () => {
    if (scrollRef.current) {
      scrollRef.current.scrollBy({ left: -200, behavior: "smooth" })
    }
  }

  const scrollRight = () => {
    if (scrollRef.current) {
      scrollRef.current.scrollBy({ left: 200, behavior: "smooth" })
    }
  }

  return (
    <div className="places-selector">
      {/* Header */}
      <div className="places-header">
        <div className="places-title">
          <ChevronDown className="chevron-icon" />
          <h2>Places to visit</h2>
        </div>
        <MoreHorizontal className="more-icon" />
      </div>

      {/* Selected Places */}
      {selectedPlaces.map((place, index) => (
        <div key={place.id} className="selected-place-card">
          <div className="place-content">
            <div className="place-info">
              <div className="place-header-info">
                <div className="place-number">{index + 1}</div>
                <h3 className="place-name">{place.name}</h3>
                <button onClick={() => removePlace(place.id)} className="remove-button">
                  ×
                </button>
              </div>
              <p className="place-notes">{place.notes}</p>
            </div>
            <div className="place-image-container">
              <img src={place.image || "/placeholder.svg"} alt={place.name} className="place-image" />
            </div>
          </div>
        </div>
      ))}

      {/* Add a place section */}
      <div className="add-place-section">
        <div className="add-place-container">
          <MapPin className="map-pin-icon" />
          <div className="search-container">
            <input
              value={searchQuery}
              onChange={handleSearchChange}
              placeholder="Add a place"
              className="search-input"
            />
            {isSearching && (
              <div className="search-loading">
                <div>Searching...</div>
              </div>
            )}
            {searchResults.length > 0 && (
              <div className="search-results">
                {searchResults.map((result) => (
                  <div key={result.id} onClick={() => addPlace(result)} className="search-result-item">
                    <img src={result.image || "/placeholder.svg"} alt={result.name} className="result-image" />
                    <span className="result-name">{result.name}</span>
                  </div>
                ))}
              </div>
            )}
          </div>
          <div className="action-buttons">
            <button className="action-button">
              <Square className="action-icon" />
            </button>
            <button className="action-button">
              <List className="action-icon" />
            </button>
          </div>
        </div>
      </div>

      {/* Recommended places */}
      <div className="recommended-section">
        <div className="recommended-header">
          <button onClick={() => setIsRecommendedOpen(!isRecommendedOpen)} className="recommended-toggle">
            <ChevronDown className={`chevron-icon ${!isRecommendedOpen ? "rotated" : ""}`} />
            <span>Recommended places</span>
          </button>
        </div>

        {isRecommendedOpen && (
          <div className="recommended-content">
            <div className="explore-container">
              <button onClick={scrollLeft} className="scroll-button">
                <ChevronLeft className="scroll-icon" />
              </button>

              <div className="explore-info">
                <MapPin className="explore-pin" />
                <span>Explore more</span>
              </div>

              <div ref={scrollRef} className="places-scroll">
                {placesToShow.map((place, index) => (
                  <div key={index} className="place-thumbnail">
                    <div
                      onClick={() =>
                        addPlace({
                          id: Date.now() + index,
                          name: place.name,
                          image:
                            place.photos && place.photos[0]
                              ? `https://maps.googleapis.com/maps/api/place/photo?maxwidth=80&photoreference=${place.photos[0].photo_reference}&key=YOUR_API_KEY`
                              : `https://picsum.photos/80/60?random=${index}`,
                        })
                      }
                      className="thumbnail-container"
                    >
                      <img
                        src={
                          place.photos && place.photos[0]
                            ? `https://maps.googleapis.com/maps/api/place/photo?maxwidth=80&photoreference=${place.photos[0].photo_reference}&key=YOUR_API_KEY`
                            : `https://picsum.photos/80/60?random=${index}`
                        }
                        alt={place.name}
                        className="thumbnail-image"
                      />
                      <div className="thumbnail-overlay">
                        <Plus className="plus-icon" />
                      </div>
                    </div>
                  </div>
                ))}
              </div>

              <button onClick={scrollRight} className="scroll-button">
                <Search className="scroll-icon" />
              </button>
            </div>
          </div>
        )}
      </div>
    </div>
  )
}

export default PlacesSelector

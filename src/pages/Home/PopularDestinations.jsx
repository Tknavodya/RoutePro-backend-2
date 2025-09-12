import React from 'react';
import './PopularDestinations.css';
import { FaMapMarkerAlt } from 'react-icons/fa';
import { Swiper, SwiperSlide } from 'swiper/react';
import 'swiper/css';

import 'swiper/css/pagination';
import { Pagination } from 'swiper/modules';

const destinations = [
  {
    name: "Sigiriya Rock Fortress",
    province: "Central Province",
    image: "/images/sigiriya.jpg",
    description: "Ancient palace built on a massive rock formation with stunning views and historical significance.",
    tags: ["UNESCO World Heritage", "Ancient Frescoes", "Lion's Gate"]
  },
  {
    name: "Ella Rock",
    province: "Uva Province",
    image: "/images/ellarock.jpg",
    description: "Breathtaking hiking destination with panoramic views of tea plantations and valleys.",
    tags: ["Hiking Trails", "Tea Plantations", "Nine Arch Bridge"]
  },
  {
    name: "Yala National Park",
    province: "Southern Province",
    image: "/images/yala.jpg",
    description: "Premier wildlife sanctuary famous for leopards, elephants, and diverse bird species.",
    tags: ["Safari", "Leopard Spotting", "Bird Watching"]
  },
  {
    name: "Temple of the Tooth",
    province: "Central Province",
    image: "/images/daladamaligawa.jpg",
    description: "Sacred Buddhist temple housing the relic of Buddha's tooth, a UNESCO World Heritage site.",
    tags: ["Buddhist Temple", "world Heritage", "Sacred Relic"]
  },
  {
    name: "Mirissa Beach",
    province: "Southern Province",
    image: "/images/mirissa.jpg",
    description: "Stunning tropical beach ideal for surfing, whale watching, and relaxing under the sun.",
    tags: ["Beach", "Surfing", "Whale Watching"]
  },
  {
    name: "Horton Plains",
    province: "Central Province",
    image: "/images/hortonplains.jpg",
    description: "Scenic national park featuring montane grasslands and dramatic drop-offs like World's End.",
    tags: ["Nature Trails", "World's End", "Baker's Falls"]
  }
];

const PopularDestinations = () => {
  return (
    <div className="destinations-section">
      <h1 >
        <span className="highlight">Populer Destinations</span>
      </h1>
      <p>
        Explore the most beloved destinations in Sri Lanka, carefully curated for unforgettable experiences and cultural immersion.
      </p>

      <Swiper
        modules={[ Pagination]}
        slidesPerView={4}
        spaceBetween={20}
        
        pagination={{ clickable: true }}
        breakpoints={{
          1024: { slidesPerView: 4 },
          768: { slidesPerView: 2 },
          0: { slidesPerView: 1 },
        }}
      >
        {destinations.map((item, index) => (
          <SwiperSlide key={index}>
            <div className="destination-card">
              <div
                className="destination-image"
                style={{ backgroundImage: `url(${item.image})` }}
              >
                <div className="destination-title">
                  <h2>{item.name}</h2>
                  <p>
                    <FaMapMarkerAlt />{" "}
                    <strong className="province-highlight">{item.province}</strong>
                  </p>
                </div>
              </div>
              <div className="destination-body">
                <p>{item.description}</p>
                <div className="tags">
                  {item.tags.map((tag, idx) => (
                    <span className="tag" key={idx}>{tag}</span>
                  ))}
                </div>
              </div>
            </div>
          </SwiperSlide>
        ))}
      </Swiper>
    </div>
  );
};

export default PopularDestinations;

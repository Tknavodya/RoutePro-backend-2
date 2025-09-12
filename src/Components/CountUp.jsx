import React, { useState, useEffect } from 'react';

const CountUp = ({ from, to, duration, separator }) => {
  const [count, setCount] = useState(from);

  useEffect(() => {
    let start = from;
    const end = to;
    const increment = (end - start) / (duration * 60);
    let frame = 0;

    const timer = setInterval(() => {
      frame++;
      start += increment;
      if (frame >= duration * 60) {
        clearInterval(timer);
        start = end;
      }
      setCount(Math.floor(start));
    }, 1000 / 60);

    return () => clearInterval(timer);
  }, [from, to, duration]);

  const formatted = separator
    ? count.toString().replace(/\B(?=(\d{3})+(?!\d))/g, separator)
    : count;

  return <span>{formatted}</span>;
};

export default CountUp;

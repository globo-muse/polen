import React from "react";
import { Spinner } from "react-bootstrap";

export default function PolPreloader({local = false}) {
  return (
    <div className={`pol-preloader${local ? " local" : ""}`}>
      <div className="pol-preloader__wrapp">
        <Spinner animation="border" variant="secondary" />
      </div>
    </div>
  );
}

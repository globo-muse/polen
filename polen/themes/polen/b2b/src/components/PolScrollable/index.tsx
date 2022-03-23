import React from "react";
import "./styles.scss";

export default function PolScrollable({ id, children }) {
  return (
    <section id={id} className="banner-scrollable">
      <div className="banner-scrollable__content">{children}</div>
      <div className="banner-scrollable__nav d-none">nav</div>
    </section>
  );
}

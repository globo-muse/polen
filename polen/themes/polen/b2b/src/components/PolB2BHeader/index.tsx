import React from "react";
import { ChevronLeft } from "react-feather";
import { Row, Col, Button } from "react-bootstrap";
import "./styles.scss";
import logo from "images/logo-b2b.png";

const PolB2bHeader = () => {
  return (
    <Row className="py-4 px-3 px-md-5 g-0 mb-md-4">
      <Col xs={6} className="d-flex align-items-center">
        <a href="http://polen.me/">
          <ChevronLeft />
          <img src={logo} alt="Logo B2B" className="logo-b2b" width={147} height={34} />
        </a>
      </Col>
      <Col xs={6} className="d-flex align-items-center justify-content-end">
        <Button variant="outline-light" size="sm" href="#faleconosco">
          Fale Conosco
        </Button>
      </Col>
    </Row>
  );
};

export default PolB2bHeader;

import React from "react";
import { Row, Col, Button } from "react-bootstrap";
import { Mail } from "react-feather";
import logo from "images/logo-b2b.png";

export default function () {
  return (
    <section className="pb-sm-4" style={{ backgroundColor: "var(--bs-low-medium)" }}>
      <Row className="p-3 px-md-5 mt-5 g-0">
        <Col md={4}>
          <p>
            <img src={logo} alt="Logo Polen B2B" width={182} height={44} className="mb-sm-3" />
          </p>
          <p>
            <Button variant="outline-light" size="lg" href="http://polen.me/">
              Voltar ao site principal
            </Button>
          </p>
        </Col>
        <Col md={4} className="mt-2 mt-md-0">
          <p className="typo-xs text-md-center">
            <a href="mailto:polen.empresas@polen.me">
              <Mail /> polen.empresas@polen.me
            </a>
          </p>
        </Col>
        <Col md={4} className="mt-2 mt-md-0 text-md-end">
          <p className="typo-xs">
            <a href="https://polen.me/termos-de-uso/" target="_blank">
              Termos de uso
            </a>
          </p>
          <p className="typo-xs">
            <a href="https://polen.me/politica-de-privacidade/" target="_blank">
              Política de privacidade
            </a>
          </p>
        </Col>
      </Row>
    </section>
  );
}

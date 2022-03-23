import React from "react";
import { Row, Col } from "react-bootstrap";
import IconMenu from 'images/icon-menu.png';
import IconProfile from 'images/icon-profile.png';
import IconCalendar from 'images/icon-calendar.png';
import "./styles.scss";

export default function () {
  return (
    <section>
      <Row className="g-0 p-3 p-md-5 mt-5">
        <Col md={12}>
          <h2 className="typo-xl text-center">
            Como a  Polen pode te ajudar:
          </h2>
        </Col>
        <Col md={12} className='mt-5'>
          <Row>
            <Col md={4}>
              <div className={"border-bottom-gradient mb-5"}>
                <img src={IconMenu} alt="Menu" width={104} height={82} />
                <p className="typo-md mt-4">Emocione e motive sua equipe</p>
                <p className="typo-xs mb-5">Use os vídeos da Polen para promover cargos, celebrar uma data especial, ou parabenizar sua equipe por resultados. Mantenha seus funcionários engajados criando momentos marcantes.</p>
              </div>
            </Col>
            <Col md={4}>
              <div className={"border-bottom-gradient mb-5"}>
                <img src={IconProfile} alt="Profile" width={84} height={83} />
                <p className="typo-md mt-4">Surpreenda seus clientes</p>
                <p className="typo-xs mb-4">Encontre a celebridade certa para ser porta-voz de sua marca, engajando seu público com um conteúdo personalizado para as suas redes sociais. </p>
              </div>
            </Col>
            <Col md={4}>
              <div className={"border-bottom-gradient mb-5"}>
                <img src={IconCalendar} alt="Calendário" width={86} height={88} />
                <p className="typo-md mt-4">Crie momentos memoráveis</p>
                <p className="typo-xs mb-4">Mande uma mensagem especial para sua audiência durante seus eventos, seja para agradecer, emocionar ou criar uma dinâmica criativa.</p>
              </div>
            </Col>
          </Row>
        </Col>
      </Row>
    </section>
  );
}

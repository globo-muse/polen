import React from "react";
import { Row, Col } from "react-bootstrap";
import Slider from "react-slick";
import { ArrowLeft, ArrowRight } from "react-feather";
import endered from "images/logos/logo-edenred.png";
import kovi from "images/logos/logo-kovi.png";
import tecmundo from "images/logos/logo-tecmundo.png";
import ceo from "images/ceo.png";
import "./styles.scss";
import "slick-carousel/slick/slick.css";
import "slick-carousel/slick/slick-theme.css";

const partners = [
  { logo: endered, message: 'Amet minim mollit non deserunt ullamco est sit aliqua dolor do amet sint. Velit officia consequat duis enim velit mollit. Exercitation veniam consequat sunt nostrud amet.', name: "Jane Cooper", position: "CEO - Edenred", avatar: ceo },
  { logo: kovi, message: 'Amet minim mollit non deserunt ullamco est sit aliqua dolor do amet sint. Velit officia consequat duis enim velit mollit. Exercitation veniam consequat sunt nostrud amet.', name: "Jane Cooper", position: "CEO - Edenred", avatar: ceo },
  { logo: tecmundo, message: 'Amet minim mollit non deserunt ullamco est sit aliqua dolor do amet sint. Velit officia consequat duis enim velit mollit. Exercitation veniam consequat sunt nostrud amet.', name: "Jane Cooper", position: "CEO - Edenred", avatar: ceo },
];

function SampleNextArrow(props) {
  const { onClick } = props;
  return (
    <div className="arrow next-arrow" onClick={onClick}>
      <ArrowRight />
    </div>
  );
}

function SamplePrevArrow(props) {
  const { onClick } = props;
  return (
    <div className="arrow prev-arrow me-3" onClick={onClick}>
      <ArrowLeft />
    </div>

  );
}

const settings = {
  dots: false,
  infinite: true,
  speed: 500,
  slidesToShow: 3,
  slidesToScroll: 1,
  nextArrow: <SampleNextArrow />,
  prevArrow: <SamplePrevArrow />,
  responsive: [
    {
      breakpoint: 900,
      settings: {
        slidesToShow: 1,
        slidesToScroll: 1,
      }
    }
  ]
};

export default function () {
  return (
    <section>
      <Row className="p-3 p-md-5 g-0">
        <Col md={12} className="d-block d-md-none">
          <h2 className="typo-xl text-center mb-4 mt-5">
            Palavras dos nossos parceiros
          </h2>
        </Col>
        <Col md={12}>
          <Slider {...settings}>
            {partners.map((item, key) => (
              <div key={key}>
                <CardPartner data={item} key={key} />
              </div>
            ))}
          </Slider>
        </Col>
      </Row>
    </section>
  );
}

function CardPartner({ data }) {
  return (
    <section className="me-md-3">
      <Row className="g-0">
        <Col xs={12}>
          <div className="box-color p-4 mb-4">
            <Row>
              <Col sm={12} className="d-flex justify-content-center mb-3">
                <img src={data.logo} alt="Logo B2B" height={91} />
              </Col>
              <Col sm={12}>
                <p className="mb-4 typo-xs">{data.message}</p>
              </Col>
              <Col sm={12} className="d-flex justify-content-center mb-3">
                <img src={data.avatar} alt={data.name} width={48} className="rounded-circle" />
              </Col>
              <Col sm={12} className="mb-3">
                <h5 className={"text-center typo-xs mb-1"}>{data.name}</h5>
                <h5 className={"text-center typo-xs"}>{data.position}</h5>
              </Col>
            </Row>
          </div>
        </Col>
      </Row>
    </section>
  );
}

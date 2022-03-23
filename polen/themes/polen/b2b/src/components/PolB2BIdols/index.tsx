import React from "react";
import { Row, Col, Form, Spinner } from "react-bootstrap";
import Slider from "react-slick";
import { ArrowLeft, ArrowRight } from "react-feather";
import { getB2BTalents } from 'services';
import "./styles.scss";
import "slick-carousel/slick/slick.css";
import "slick-carousel/slick/slick-theme.css";

function SampleNextArrow(props) {
  const { onClick } = props;
  return (
    <div className="arrow next-arrow me-3" onClick={onClick}>
      <ArrowRight />
    </div>
  );
}

function SamplePrevArrow(props) {
  const { onClick } = props;
  return (
    <div className="arrow prev-arrow me-4" onClick={onClick}>
      <ArrowLeft />
    </div>

  );
}

const settings = {
  dots: false,
  infinite: true,
  speed: 500,
  centerMode: false,
  variableWidth: true,
  slidesToScroll: 1,
  nextArrow: <SampleNextArrow />,
  prevArrow: <SamplePrevArrow />,
  responsive: [
    {
      breakpoint: 900,
      settings: {
        arrows: false,
      }
    }
  ]
};

export default function () {
  const [idols, setIdols] = React.useState(null);
  const [categorie, setCategorie] = React.useState('');

  const handleChange = (evt) => {
    let name = evt.target.name;
    setCategorie(name);
  };

  React.useEffect(() => {
    getB2BTalents(categorie).then((res) => {
      setIdols(res.data);
    });
  }, []);

  React.useEffect(() => {
    setIdols(null);
    getB2BTalents(categorie).then((res) => {
      setIdols(res.data);
    });
  }, [categorie]);

  return (
    <section>
      <Row className="py-3 g-0 my-5">
        <Col md={12}>
          <h2 className="typo-xl text-center mb-4">
            Ídolos da Polen
          </h2>
        </Col>
        <Col md={12}>
          <Form>
            <div key={'inline-checkbox'} className="categories-list mb-5 p-4">
              <Form.Check
                inline
                label="Todos"
                name=""
                type={'checkbox'}
                onChange={handleChange}
                checked={categorie === '' ? true : false}
                id={'inline-checkbox-1'}
              />
              <Form.Check
                inline
                label="Esporte"
                name="[esporte]"
                type={'checkbox'}
                onChange={handleChange}
                checked={categorie === '[esporte]' ? true : false}
                id={'inline-checkbox-2'}
              />
              <Form.Check
                inline
                label="Apresentadores"
                name="[apresentadores]"
                type={'checkbox'}
                onChange={handleChange}
                checked={categorie === '[apresentadores]' ? true : false}
                id={'inline-checkbox-3'}
              />
              <Form.Check
                inline
                label="Música"
                name="[musica]"
                type={'checkbox'}
                onChange={handleChange}
                checked={categorie === '[musica]' ? true : false}
                id={'inline-checkbox-4'}
              />
              <Form.Check
                inline
                label="Atrizes e Atores"
                name="[atrizes-e-atores]"
                type={'checkbox'}
                onChange={handleChange}
                checked={categorie === '[atrizes-e-atores]' ? true : false}
                id={'inline-checkbox-5'}
              />
            </div>
          </Form>
        </Col>
        <Col>
          {idols ? (
            <Slider {...settings} className="idols-list">
              {
                idols.map((item, key) => (
                  <div key={key}>
                    <CardIdol data={item} key={key} />
                  </div>
                ))
              }
            </Slider>
          ) : (
            <Slider {...settings} className="idols-list">
              <CardLoading />
              <CardLoading />
              <CardLoading />
              <CardLoading />
              <CardLoading />
              <CardLoading />
            </Slider>
          )}
        </Col>
      </Row>
    </section>
  );
}

function CardIdol({ data }) {
  return (
    <section>
      <a href={data.permalink} target="_blank">
        <div
          className="super-banner-item me-2"
        >
          <figure className="video-card">
            <img
              src={data.thumbnail}
              alt={data.name}
              className="poster"
            />
          </figure>
          <div className="idol-name">
            <h3 className={"mb-0 typo-md"}><strong>{data.name}</strong></h3>
          </div>
        </div>
      </a>
    </section>
  );
}

function CardLoading() {
  return (
    <section>
      <div
        className="super-banner-item me-2"
      >
        <div className="loading-item">
          <Spinner animation="border" variant="primary" />
        </div>
      </div>
    </section>
  );
}

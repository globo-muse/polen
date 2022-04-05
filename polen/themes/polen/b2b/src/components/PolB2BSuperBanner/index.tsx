import React from "react";
import { Row, Col, Button } from "react-bootstrap";
import { playVideo } from "services";
import kovi from "images/logos/rounded/kovi.png";
import tecmundo from "images/logos/rounded/tecmundo.png";
import prestex from "images/logos/rounded/prestex.png";
import Slider from "react-slick";
import { ArrowLeft, ArrowRight } from "react-feather";
import "./styles.scss";
import "slick-carousel/slick/slick.css";
import "slick-carousel/slick/slick-theme.css";

const videosData = [
  {
    image: `${process.env.ASSETS_URI}/covers/cid.webp`,
    video:
      "https://player.vimeo.com/progressive_redirect/playback/684263761/rendition/360p?loc=external&oauth2_token_id=1511981636&signature=246855c0ccbb10e626b83c414d6719e7541da5971ad093d33f1534a82b6529f8",
    logo: prestex,
    name: "Prestex",
    paused: true,
  },
  {
    image: `${process.env.ASSETS_URI}/covers/supla.webp`,
    video:
      "https://player.vimeo.com/progressive_redirect/playback/684259555/rendition/720p?loc=external&oauth2_token_id=1511981636&signature=ffc1af403024ddd44faa35242ada977e9f90b779b812027c5b5a8778ba54d6e0",
    logo: tecmundo,
    name: "Tecmundo",
    paused: true,
  },
  {
    image: `${process.env.ASSETS_URI}/covers/vampeta.webp`,
    video:
      "https://player.vimeo.com/progressive_redirect/playback/684261255/rendition/540p?loc=external&oauth2_token_id=1511981636&signature=791c8c070bbc178b4052c152459ead787ee2d1695a7fbb08c129d51cece4d7cb",
    logo: kovi,
    name: "Kovi",
    paused: true,
  },
  {
    image: `${process.env.ASSETS_URI}/covers/falcao.webp`,
    video:
      "https://player.vimeo.com/progressive_redirect/playback/683914281/rendition/540p?loc=external&oauth2_token_id=1511981636&signature=ae937716f2b2a6098ee778ac9d8ec4aababae6e755af71cb9c14e1f6887622b3",
    logo: tecmundo,
    name: "Tecmundo",
    paused: true,
  },
];

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
      },
    },
  ],
};

export default function () {
  const [videos, setVideos] = React.useState(videosData);

  const handleClick = (evt, key) => {
    if (!videos[key].paused) {
      evt.target.pause();
      setVideos((current) => {
        return current.map((item, index) => ({
          ...item,
          paused: true,
        }));
      });
      return;
    }

    setVideos((current) => {
      return current.map((item, index) => ({
        ...item,
        paused: key == index ? false : true,
      }));
    });

    playVideo(evt.target);
  };

  return (
    <section>
      <Row className="g-0 p-3">
        <Col md={12} lg={6} className="p-md-5 mt-4 mt-md-4 order-1 order-md-0">
          <h1 className="typo-xl title-b2b">
            Use os vídeos personalizados dos ídolos da Polen para impulsionar o
            seu <em className="title-b2b-highlight">negócio</em>
          </h1>
          <p className="typo-xs">
            Crie autoridade para sua marca, aumente suas vendas, e crie mais
            engajamento com seus clientes e colaboradores.
          </p>
          <Row>
            <Col lg={8} className="m-auto m-md-0">
              <div className="d-grid gap-2 mt-4">
                <Button href="#faleconosco" size="lg" className={"cta-button"}>
                  Fale com a equipe de vendas
                </Button>
              </div>
            </Col>
          </Row>
        </Col>
        <Col md={12} lg={6}>
          <Slider {...settings} className="videos-list">
            {videos.map((item, key) => (
              <div key={key}>
                <section key={`item-${key}`}>
                  <div
                    id={`super-banner-item-${key}`}
                    className={`super-banner-item${
                      key == videos.length ? "" : " me-3"
                    }`}
                    onClick={(evt) => handleClick(evt, key)}
                  >
                    <figure className="video-card">
                      <img
                        src={item.image}
                        alt={item.name}
                        className="poster rounded"
                        loading="lazy"
                      />
                      <video
                        id={`super-banner-video-${key}`}
                        src={item.video}
                        className={`rounded video-player${
                          !videos[key].paused ? " active" : ""
                        }`}
                        playsInline
                      ></video>
                    </figure>
                    {videos[key].paused ? (
                      <figure className="logo">
                        <img
                          src={item.logo}
                          alt={item.name}
                          className="image"
                          width={70}
                          height={70}
                          loading="lazy"
                        />
                        <figcaption className="name">{item.name}</figcaption>
                      </figure>
                    ) : null}
                  </div>
                </section>
              </div>
            ))}
          </Slider>
        </Col>
      </Row>
    </section>
  );
}

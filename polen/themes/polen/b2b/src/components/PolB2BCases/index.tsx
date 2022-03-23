import React from "react";
import { Row, Col } from "react-bootstrap";
import Slider from "react-slick";
import { ArrowLeft, ArrowRight } from "react-feather";
import { Calendar } from "react-feather";
import { playVideo } from "services";
import "./styles.scss";
import "slick-carousel/slick/slick.css";
import "slick-carousel/slick/slick-theme.css";

const videosData = [
  {
    image:
      "https://i.vimeocdn.com/video/1364305989-55d3b1bef407347f5c2c527cf9db5b00bf9e80daf4d5dfe26081140cb47999ad-d",
    video:
      "https://player.vimeo.com/progressive_redirect/playback/634757715/rendition/720p?loc=external&oauth2_token_id=1511985459&signature=11f36c28ddac629de6b3f726d073bca4c9a4a3847530345c6eb53258bfdbaaf6",
    name: "Edenred",
    text: `<p>
            Com a nossa solução para <strong>eventos</strong>, a Edenred escolheu 3 ídolos para
            anunciar premiações internas da empresa durante seu evento anual
            Celebre 2021.
          </p>
          <p>
            Rafael Infante, Nelson Freitas e Supla foram as celebridades
            escolhidas para apresentar os prêmios, parabenizar os funcionários
            de longa data e anunciar o vencedor do concurso de
            intraempreendedorismo.
          </p>`,
    paused: true,
  },
  {
    image:
      "https://i.vimeocdn.com/video/1364305989-55d3b1bef407347f5c2c527cf9db5b00bf9e80daf4d5dfe26081140cb47999ad-d",
    video:
      "https://player.vimeo.com/progressive_redirect/playback/634757715/rendition/720p?loc=external&oauth2_token_id=1511985459&signature=11f36c28ddac629de6b3f726d073bca4c9a4a3847530345c6eb53258bfdbaaf6",
    name: "Tecmundo",
    text: `<p>Para gerar engajamento durante as lives de promoção da Black Friday, a Tecmundo chamou Supla, Falcão e Gustavo Mendes para interagir com o seu público nas redes sociais e promover mais vendas durante essa importante época do calendário comercial. Também foram feitos vídeos para motivar seus colaboradores durante essa mesma época.</p>`,
    paused: true,
  },
  {
    image:
      "https://i.vimeocdn.com/video/1364305989-55d3b1bef407347f5c2c527cf9db5b00bf9e80daf4d5dfe26081140cb47999ad-d",
    video:
      "https://player.vimeo.com/progressive_redirect/playback/634757715/rendition/720p?loc=external&oauth2_token_id=1511985459&signature=11f36c28ddac629de6b3f726d073bca4c9a4a3847530345c6eb53258bfdbaaf6",
    name: "Doutor Sofá",
    text: `<p>A Doutor Sofá que possui franquias em todo Brasil, se juntou à Polen para realizar materiais de engajamento nas suas redes sociais de forma descontraída. Os ídolos que participaram dessa ação foram Joel Santana e o nosso Papai Noel da Polen durante a época de Natal.</p>`,
    paused: true,
  },
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
  dots: true,
  infinite: false,
  speed: 500,
  slidesToShow: 1,
  slidesToScroll: 1,
  fade: true,
  nextArrow: <SampleNextArrow />,
  prevArrow: <SamplePrevArrow />,
  responsive: [
    {
      breakpoint: 900,
      settings: {
        dots: false,
      },
    },
  ],
};

export default function () {
  const [videos, setVideos] = React.useState(videosData);

  const handleClick = (evt, key) => {
    const video: HTMLVideoElement = document.querySelector(
      `#cases-video-${key}`
    );
    if (!video.paused) {
      video.pause();
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

    playVideo(video);
  };
  return (
    <section className="cases-b2b mb-5">
      <Row className="g-0 p-3 px-md-5">
        <Col md={12} className="m-md-auto">
          <h2 className="typo-xl text-center mb-5">Histórias de Sucesso</h2>
        </Col>
        <Col md={12}>
          <Slider {...settings}>
            {videosData.map((item, key) => (
              <div key={key}>
                <section className="card-case col-xs-12 col-sm-10 mx-auto">
                  <div
                    className="card-case__wrapp"
                    onClick={(evt) => handleClick(evt, key)}
                  >
                    <Row className="g-0">
                      <Col md={5} className="d-flex align-items-center">
                        <figure
                          className={`video-card${
                            videos[key].paused ? " -paused" : ""
                          }`}
                        >
                          <img
                            src={item.image}
                            alt={item.name}
                            className="poster"
                          />
                          <video
                            id={`cases-video-${key}`}
                            src={item.video}
                            width={"100%"}
                            className={`video-player${
                              !videos[key].paused ? " -active" : ""
                            }`}
                            playsInline
                          ></video>
                        </figure>
                      </Col>
                      <Col md={7}>
                        <div className="p-md-4">
                          <p className="typo-md d-flex align-items-center mb-4">
                            <Calendar
                              color="var(--bs-primary)"
                              className="me-2"
                            />
                            Evento
                          </p>
                          <h4 className="typo-lg mb-4">{item.name}</h4>
                          <span
                            dangerouslySetInnerHTML={{ __html: item.text }}
                            className="typo-xs"
                          />
                        </div>
                      </Col>
                    </Row>
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

import React from "react";
import { Row, Col, Accordion } from "react-bootstrap";
import "./styles.scss";

export default function () {
  return (
    <section>
      <Row className="p-3 p-md-5 g-0 my-5">
        <Col md={12}>
          <h2 className="typo-xl text-center">
            Perguntas frequentes
          </h2>
        </Col>
        <Col md={10} className='mt-5 mx-auto'>
          <Accordion className={"faq-accordion"}>
            <Accordion.Item eventKey="0">
              <Accordion.Header className="typo-xs"><strong>O que são os vídeos Polen para Empresas ?</strong></Accordion.Header>
              <Accordion.Body className="typo-xs py-3">
                Os vídeo Polen para Empresas são uma maneira de conectar os nossos ídolos com o público alvo da sua marca, seja para clientes, colaboradores ou eventos. Você pode pedir para nossos ídolos gravar uma mensagem para presentear ou elogiar sua equipe, fazer campanhas, lançamentos ou até para dar um conselho e aquele discurso motivacional que seus colaboradores tanto precisam de forma criativa, inovadora e exclusiva
              </Accordion.Body>
            </Accordion.Item>
            <Accordion.Item eventKey="1">
              <Accordion.Header className="typo-xs"><strong>Quanto tempo leva para receber o vídeo Polen para Empresas ?</strong></Accordion.Header>
              <Accordion.Body className="typo-xs py-3">
                Após efetivar a negociação, os nossos ídolos têm o prazo máximo de até 5 dias para concluir a solicitação recebida. Após a entrega do vídeo, a Polen tem o prazo de 1 dia para validação e entrega do material pronto.
              </Accordion.Body>
            </Accordion.Item>
            <Accordion.Item eventKey="2">
              <Accordion.Header className="typo-xs"><strong>Consigo um vídeo Polen para Empresas no mesmo dia ?</strong></Accordion.Header>
              <Accordion.Body className="typo-xs py-3">
                Sim, conseguimos entregar o vídeo Polen para Empresas no mesmo dia. Para isso será cobrado um valor adicional para atender a sua urgência.
              </Accordion.Body>
            </Accordion.Item>
            <Accordion.Item eventKey="3">
              <Accordion.Header className="typo-xs"><strong>Posso usar vários vídeos Polen para Empresas para uma mesma campanha ?</strong></Accordion.Header>
              <Accordion.Body className="typo-xs py-3">
                Sim, inclusive podemos criar um compilado de vídeos através do nosso serviço Colab for Business, disponível apenas para clientes corporativos.
              </Accordion.Body>
            </Accordion.Item>
            <Accordion.Item eventKey="4">
              <Accordion.Header className="typo-xs"><strong>Qual o prazo de licença para utilização do  vídeo Polen para Empresas ?</strong></Accordion.Header>
              <Accordion.Body className="typo-xs py-3">
                O prazo de utilização padrão é de 30 dias, porém temos flexibilidade e oferecemos extensões de licença relacionadas ao prazo e uso com custo adicional.
              </Accordion.Body>
            </Accordion.Item>
            <Accordion.Item eventKey="5">
              <Accordion.Header className="typo-xs"><strong>Em quais canais posso veicular os vídeo Polen para Empresas ? </strong></Accordion.Header>
              <Accordion.Body className="typo-xs py-3">
                Podem ser divulgados em redes sociais e comunicação interna da empresa. Existe também a possibilidade de ajudarmos na sua divulgação incluindo o seu vídeo vídeo Polen para Empresas na programação da TV Globo em abrangências regionais ou em todo Brasil por uma taxa adicional.
              </Accordion.Body>
            </Accordion.Item>
            <Accordion.Item eventKey="6">
              <Accordion.Header className="typo-xs"><strong>O ídolo também vai postar o vídeo em seus canais?</strong></Accordion.Header>
              <Accordion.Body className="typo-xs py-3">
                Não, nossos ídolos não têm nenhuma responsabilidade de divulgação do vídeo em seus canais, apenas gravá-lo seguindo suas instruções.
              </Accordion.Body>
            </Accordion.Item>
            <Accordion.Item eventKey="7">
              <Accordion.Header className="typo-xs"><strong>Posso editar o vídeo Polen para Empresas após recebê-lo?</strong></Accordion.Header>
              <Accordion.Body className="typo-xs py-3">
                Infelizmente não é permitido alteração no vídeo após a entrega. Qualquer tipo de edição deve ser solicitada à Polen e, se necessário, validada com o ídolo.
              </Accordion.Body>
            </Accordion.Item>
            <Accordion.Item eventKey="8">
              <Accordion.Header className="typo-xs"><strong>Posso remover a marca d'água Polen ou adicionar o logotipo da minha empresa ao vídeo Polen para empresas ?</strong></Accordion.Header>
              <Accordion.Body className="typo-xs py-3">
                A marca d'água Polen não pode ser removida, no entanto, oferecemos marcas d'água co-branded por um custo adicional.
              </Accordion.Body>
            </Accordion.Item>
            {/* <Accordion.Item eventKey="9">
              <Accordion.Header className="typo-xs"><strong>Onde posso encontrar mais informações sobre as Políticas de Privacidade e Uso da plataforma?</strong></Accordion.Header>
              <Accordion.Body className="typo-xs py-3">
                Você pode encontrar todas essas informações no nosso <a href='https://polen.me/termos-de-uso/' target="_blank" rel="noreferrer"> Termo de Uso</a> e na nossa <a href='https://polen.me/politica-de-privacidade/' target="_blank" rel="noreferrer"> Política de Privacidade</a>.
              </Accordion.Body>
            </Accordion.Item> */}
            {/* <Accordion.Item eventKey="10">
              <Accordion.Header className="typo-xs no-border-bottom"><strong>Quanto custa pedir um vídeo Polen para Empresas ?</strong></Accordion.Header>
              <Accordion.Body className="typo-xs py-3">
                Não trabalhamos com um preço fixo. O custo varia de acordo com o ídolo escolhido, por onde a campanha será divulgada e qual mensagem será gravada.
              </Accordion.Body>
            </Accordion.Item> */}
          </Accordion>
        </Col>
      </Row>
    </section>
  );
}

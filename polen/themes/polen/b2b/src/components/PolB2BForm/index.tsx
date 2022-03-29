import React from "react";
import { Row, Col, Form, Button } from "react-bootstrap";
import { useAppContext } from "context";
import { showMessage, MESSAGE_TYPES } from "components/PolMessage";
import { PolNonce, PolPreloader } from "components";
import { getError, getURLParam } from "utils";
import { ContactB2B } from "interfaces";
import { contactFormB2B, getCitiesByState } from "services";

export default function () {
  const modelContact = {
    company: "",
    email: "",
    form_id: "1",
    name: "",
    phone: "",
    city: "",
    state: "",
    security: "",
    product_name: getURLParam("talent"),
    utm_source: getURLParam("utm_source"),
    utm_campaign: getURLParam("utm_campaign"),
    utm_term: getURLParam("utm_term"),
    utm_medium: getURLParam("utm_medium"),
    utm_content: getURLParam("utm_content")
  };
  const context = useAppContext();
  const [formData, setFormData] = React.useState<ContactB2B | undefined>(
    modelContact
  );
  const [states, setStates] = React.useState([
    { uf: '', name: 'Estado*' },
    { uf: 'AC', name: 'Acre' },
    { uf: 'AL', name: 'Alagoas' },
    { uf: 'AP', name: 'Amapá' },
    { uf: 'AM', name: 'Amazonas' },
    { uf: 'BA', name: 'Bahia' },
    { uf: 'CE', name: 'Ceará' },
    { uf: 'DF', name: 'Distrito Federal' },
    { uf: 'ES', name: 'Espírito Santo' },
    { uf: 'GO', name: 'Goiás' },
    { uf: 'MA', name: 'Maranhão' },
    { uf: 'MT', name: 'Mato Grosso' },
    { uf: 'MS', name: 'Mato Grosso do Sul' },
    { uf: 'MG', name: 'Minas Gerais' },
    { uf: 'PA', name: 'Pará' },
    { uf: 'PB', name: 'Paraíba' },
    { uf: 'PR', name: 'Paraná' },
    { uf: 'PE', name: 'Pernambuco' },
    { uf: 'PI', name: 'Piauí' },
    { uf: 'RJ', name: 'Rio de Janeiro' },
    { uf: 'RN', name: 'Rio Grande do Norte' },
    { uf: 'RS', name: 'Rio Grande do Sul' },
    { uf: 'RO', name: 'Rondônia' },
    { uf: 'RR', name: 'Roraíma' },
    { uf: 'SC', name: 'Santa Catarina' },
    { uf: 'SP', name: 'São Paulo' },
    { uf: 'SE', name: 'Sergipe' },
    { uf: 'TO', name: 'Tocantins' },
  ]);
  const formIsCompleted =
    formData.company !== "" &&
    formData.email !== "" &&
    formData.phone !== "" &&
    formData.city !== "" &&
    formData.state !== "";
  const [cities, setCities] = React.useState(null)
  const [preload, setPreload] = React.useState(false);

  const polMaskPhone = (v) => {
    if (v) {
      v = v.replace(/\D/g, ""); //Remove tudo o que não é dígito
      v = v.replace(/^(\d{2})(\d)/g, "($1) $2"); //Coloca parênteses em volta dos dois primeiros dígitos
      v = v.replace(/(\d)(\d{4})$/, "$1-$2"); //Coloca hífen entre o quarto e o quinto dígitos
    }
    return v;
  };

  const handleChange = (evt) => {
    const target = evt.target;
    const name = target.name;
    const value =
      target.type === "tel" ? polMaskPhone(target.value) : target.value;
    setFormData((current) => ({
      ...current,
      [name]: value,
    }));
  };

  const resetForm = () => {
    setFormData(modelContact);
  };

  const handleSubmit = (evt) => {
    evt.preventDefault();
    setPreload(true);
    contactFormB2B(formData)
      .then((res) => {
        resetForm();
        showMessage(
          context,
          MESSAGE_TYPES.MESSAGE,
          "Enviado",
          "Mensagem enviada com sucesso. Em breve entraremos em contato"
        );
      })
      .catch((err) => {
        showMessage(context, MESSAGE_TYPES.ERROR, "", getError(err));
      })
      .finally(() => {
        setPreload(false);
      });
  };

  React.useEffect(() => {
    if (formData.state) {
      getCitiesByState(formData.state)
        .then((res) => {
          const citiesList = res;
          citiesList.unshift({nome: 'Selecione a cidade...*'})
          setCities(citiesList);
        })
        .catch((err) => {
          showMessage(context, MESSAGE_TYPES.ERROR, "", 'Não foram encontradas cidades');
        })
    }
  }, [formData.state]);

  return (
    <section
      id="faleconosco"
      className="form-b2b"
      style={{ position: "relative" }}
    >
      <Row className="px-3 px-md-5 g-0">
        <Col md={8} lg={6} className="m-md-auto">
          <h2 className="typo-xl text-center">Fale Conosco</h2>
          <p className="typo-xs text-left text-md-center">
            Nosso time está ansioso para falar com você!
          </p>
          <Form onSubmit={handleSubmit}>
            <input
              type="text"
              name="name"
              placeholder="Nome Completo*"
              className="form-control mt-4"
              value={formData.name}
              onChange={handleChange}
              required
            />
            <input
              type="text"
              name="company"
              placeholder="Empresa*"
              className="form-control mt-4"
              value={formData.company}
              onChange={handleChange}
              required
            />
            <input
              type="email"
              name="email"
              placeholder="E-mail de trabalho*"
              className="form-control mt-4"
              value={formData.email}
              onChange={handleChange}
              required
            />
            <input
              type="tel"
              name="phone"
              placeholder="Número de telefone*"
              className="form-control mt-4"
              value={formData.phone}
              onChange={handleChange}
              maxLength={15}
              required
            />
            <select
              className={"form-select mt-4"}
              name="state"
              value={formData.state}
              onChange={handleChange}
              required
            >
              {states.map((item) => {
                return (
                  <option
                    key={item.uf}
                    value={item.uf}
                  >
                    {item.name}
                  </option>
                );
              })}
            </select>
            {
              cities ? (
                <select
                  className={"form-select mt-4"}
                  name="city"
                  value={formData.city}
                  onChange={handleChange}
                  required
                >
                  {cities.map((item, i) => {
                    return (
                      <option
                        key={i}
                        value={item.nome === 'Selecione a cidade...*' ? '' : item.nome}
                      >
                        {item.nome}
                      </option>
                    );
                  })}
                </select>
              ) : ''
            }
            <div className="d-grid gap-2 mt-4 pt-1">
              <Button type="submit" size="lg" disabled={!formIsCompleted ? true : false}>
                Enviar
              </Button>
            </div>
            <PolNonce onChange={handleChange} />
          </Form>
        </Col>
      </Row>
      {preload ? <PolPreloader local={true} /> : null}
    </section>
  );
}

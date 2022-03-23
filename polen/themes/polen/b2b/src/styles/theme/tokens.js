const fs = require("fs");
const data = require("./tokens.json");

function adjustData(text) {
  return text.replace(/,/g, "\n");
}

function getObject(obj) {
  return Object.keys(obj).map((item) => {
    return `${item}: ${obj[item].value};`;
  });
}

const brand_colors = getObject(data.Brand_colors);

const neutral_colors = Object.keys(data.Neutral_colors).map((item) =>
  getObject(data.Neutral_colors[item])
);

const feedback_colors = Object.keys(data.Feedback_colors).map((item) =>
  getObject(data.Feedback_colors[item])
);

// RENDER ---------------------------------------------------------
const render = `// SCSS gerado automaticamente
${brand_colors.map((item) => item)}

${neutral_colors.map((item) => item)}

${feedback_colors.map((item) => item)}

`;

// SALVAR ARQUIVO --------------------------------------------------
fs.writeFile("_colors.scss", adjustData(render), function (err) {
  if (err) return console.log(err);
  console.log("Gravado com sucesso");
});

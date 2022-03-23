const isBrowser = typeof window !== "undefined";

export const getError = (obj) => {
  return (
    obj?.data?.message ||
    obj?.data?.data?.message ||
    obj?.data?.data ||
    "Ocorreu um Erro"
  );
};

export const polToCurrency = (val) => {
  return parseFloat(val).toLocaleString("pt-br", {
    style: "currency",
    currency: "BRL",
  });
};

export const polCaptalize = (string) => {
  return string.charAt(0).toUpperCase() + string.slice(1);
};

export const getURLParam = (param) => {
  if (isBrowser) {
    const queryString = window?.location.search;
    const urlParams = new URLSearchParams(queryString);
    return urlParams.get(param);
  }

  return null;
};

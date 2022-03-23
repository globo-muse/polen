"use strict";

const MESSAGES = {
  INITIAL: "Criar",
  WAIT: "Enviando...",
  DONE: "Enviado!",
};

const DISCOUNT_TYPES = [
  {
    NAME: "Fixo de carrinho",
    TYPE: "fixed_cart",
    SYMBOL: "R$",
  },
  {
    NAME: "Percentual de carrinho",
    TYPE: "percent",
    SYMBOL: "%",
  },
  {
    NAME: "Fixo de produto",
    TYPE: "fixed_product",
    SYMBOL: "R$",
  },
  {
    NAME: "Percentual de produto",
    TYPE: "percent_product",
    SYMBOL: "%",
  },
];

function getSymbol(type) {
  const item = DISCOUNT_TYPES.filter((ITEM) => ITEM.TYPE === type);
  return item[0].SYMBOL;
}

const maskDate = (value) => {
  let v = value.replace(/\D/g, "").slice(0, 10);
  if (v.length >= 5) {
    return `${v.slice(0, 2)}/${v.slice(2, 4)}/${v.slice(4)}`;
  } else if (v.length >= 3) {
    return `${v.slice(0, 2)}/${v.slice(2)}`;
  }
  return v;
};

function getProducts(el) {
  return jQuery(el).val().join();
}

const createCoupon = (data) => {
  const send_data = {
    prefix_name: data.prefix_name,
    amount: data.amount,
    discount_type: data.discount_type,
    description: data.description,
    expiry_date: data.expiry_date,
    product_ids: getProducts(".wc-product-search"),
    usage_limit: data.usage_limit,
  };
  return new Promise((resolve, reject) => {
    jQuery
      .post(
        "/wp-admin/admin-ajax.php?action=polen_create_cupom",
        send_data,
        function (data) {
          resolve(data);
        }
      )
      .fail(function (xhr, status, error) {
        reject(error);
      });
  });
};

const batch_coupon = new Vue({
  el: "#batch-coupon",
  data: {
    prefix_name: "",
    amount: 0,
    product_ids: [],
    discount_type: "",
    distount_type_list: DISCOUNT_TYPES,
    description: "",
    expiry_date: "",
    usage_limit: "1",
    symbol: "",
    loading: false,
    message: MESSAGES.INITIAL,
  },
  methods: {
    validateDate: function () {
      this.expiry_date = maskDate(this.expiry_date);
    },
    handleChangeSelect: function () {
      this.symbol = getSymbol(this.discount_type);
    },
    createCoupon: function () {
      this.message = MESSAGES.WAIT;
      this.loading = true;
      createCoupon(this).then((res) => {
        console.log(res);
        this.loading = false;
        this.message = MESSAGES.INITIAL;
      });
    },
  },
});

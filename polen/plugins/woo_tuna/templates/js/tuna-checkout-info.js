var currencyTypes = {
    'BRL': {
      language: 'pt-BR',
      symbol: 'R$',
      decimalDivisor: ',',
      decimalSeparator: '.',
      moneyPattern: /\d{1,},\d{2}/,
      installmentPattern: /\(R\$.*\)/,
    },
    'USD': {
      language: 'en-US',
      symbol: '$',
      decimalDivisor: '.',
      decimalSeparator: ',',
      moneyPattern: /\d{1,}\.\d{2}/,
      installmentPattern: /\(\$.*\)/,
    },
};

function refreshOrderInfo() {
    var oldOrderTotal = getOldOrderTotal();
    var newOrderTotal = getNewOrderTotal('BRL');
    insertOrderFeesHtml(oldOrderTotal, newOrderTotal);
    insertNewOrderTotalHtml(newOrderTotal);
}

function resetOrderInfo() {
    var oldOrderTotal = getOldOrderTotal();
    var newOrderTotal = oldOrderTotal;
    insertOrderFeesHtml(oldOrderTotal, newOrderTotal);
    insertNewOrderTotalHtml(newOrderTotal);
}

function getSystemCurrency() {
    var defaultCurrency = 'USD';
    var currencySymbol = $(".woocommerce-checkout-review-order-table .cart-subtotal .woocommerce-Price-currencySymbol").html();
    for (var currency in currencyTypes) {
      if (currencyTypes[currency].symbol === currencySymbol) {
        return currency;
      }
    }

    return defaultCurrency;
}

function formatCurrency(value, currency = 'BRL') {
    return new Intl.NumberFormat(currencyTypes[currency].language, { currency, minimumFractionDigits: 2 }).format(value);
}

function getOldOrderTotal() {
    var systemCurrency = getSystemCurrency();
    var pattern = currencyTypes[systemCurrency].moneyPattern;
    var decimalSeparator = currencyTypes[systemCurrency].decimalSeparator;
    var oldOrderTotal = $(".woocommerce-checkout-review-order-table .cart-subtotal").html().replaceAll(decimalSeparator, '').match(pattern)[0];

    return getFloatNumber(oldOrderTotal, systemCurrency);
}

function getFloatNumber(value, currency) {
    var currencyFormat = currencyTypes[currency];
    var floatNumber = value.replaceAll(currencyFormat.decimalSeparator, '').replace(currencyFormat.decimalDivisor, '.');

    return parseFloat(floatNumber, 10);
}

function getNewOrderTotal(currency) {
    var installmentText = $("#INSTALLMENT option:selected").text();
    var pattern = currencyTypes[currency].installmentPattern;
    var decimalSeparator = currencyTypes[currency].decimalSeparator;
    var newOrderTotal = installmentText.match(pattern)[0].substring(3).trim();
    newOrderTotal = newOrderTotal.replaceAll(decimalSeparator, '').substring(0, newOrderTotal.length - 1);

    return getFloatNumber(newOrderTotal, currency);
}

function insertOrderFeesHtml(oldOrderTotal, newOrderTotal) {
    var feeAmount = newOrderTotal - oldOrderTotal;

    var feesHtmlElement = $('.tuna-order-fees');

    if (feesHtmlElement.length) {
      feesHtmlElement.remove();
    }

    if (feeAmount === 0) return;

    var systemCurrency = getSystemCurrency();
    var systemSymbol = currencyTypes[systemCurrency].symbol;

    var feeDescription = feeAmount > 0 ? 'Acréscimo de Juros' : 'Desconto';
    var feeOrderHtml = `<tr class="tuna-order-fees"><th scope="row">${feeDescription}:</th><td><span class="woocommerce-Price-amount amount">
  <span class="woocommerce-Price-currencySymbol">${systemSymbol}</span>${formatCurrency(feeAmount, systemCurrency)}</span></td></tr>`;

    $(".woocommerce-checkout-review-order-table .cart-subtotal").after(feeOrderHtml);
}

function insertNewOrderTotalHtml(newOrderTotal) {
    $('.order-total').remove();

    var systemCurrency = getSystemCurrency();
    var systemSymbol = currencyTypes[systemCurrency].symbol;
    var orderTotalHtml = `<tr class="order-total"><th>Total</th><td><strong><span class="woocommerce-Price-amount amount"><bdi>
  <span class="woocommerce-Price-currencySymbol">${systemSymbol}</span>${formatCurrency(newOrderTotal, systemCurrency)}</bdi></span></strong></td></tr>`;

    var feesHtmlElement = $(".tuna-order-fees");
    if (feesHtmlElement.length) {
      feesHtmlElement.after(orderTotalHtml);
    } else {
      $(".woocommerce-checkout-review-order-table .cart-subtotal").after(orderTotalHtml);
    }
}

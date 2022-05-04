var moneyPattern = /\d{1,},\d{2}/;

var currencyTypes = {
    'BRL': {
      language: 'pt-BR',
      symbol: 'R$',
      decimalDivisor: ',',
      decimalSeparator: '.',
      moneyPattern: moneyPattern,
      installmentPattern: /\(R\$.*\)/,
    },
    'USD': {
      language: 'en-US',
      symbol: '$',
      decimalDivisor: '.',
      decimalSeparator: ',',
      moneyPattern: moneyPattern,
      installmentPattern: /\(\$.*\)/,
    },
};

function refreshOrderInfo(tunaOrderUpdate) {
    var newOrderTotal;
    var { oldOrderTotal } = tunaOrderUpdate;
    try {
        newOrderTotal = getNewOrderTotal('BRL');
    } catch {
        newOrderTotal = oldOrderTotal;
    }
    var installmentAmount = getInstallmentAmount(newOrderTotal, oldOrderTotal);
    insertNewOrderTotalHtml(newOrderTotal);
    insertOrderFeesHtml(installmentAmount);
}

function resetOrderInfo(tunaOrderUpdate) {
    var { oldOrderTotal } = tunaOrderUpdate;
    var newOrderTotal = oldOrderTotal;
    var installmentAmount =getInstallmentAmount(newOrderTotal, oldOrderTotal);    
    insertNewOrderTotalHtml(newOrderTotal);
    insertOrderFeesHtml(installmentAmount);
}

function getNewOrderTotal(currency) {
  var installmentText = $("#INSTALLMENT option:selected").text();
  var symbol = currencyTypes[currency].symbol;
  var pattern = currencyTypes[currency].installmentPattern;
  var decimalSeparator = currencyTypes[currency].decimalSeparator;
  var newOrderTotal = installmentText.match(pattern)[0].substring(symbol.length + 1).trim().replaceAll(decimalSeparator, '');
  newOrderTotal = newOrderTotal.substring(0, newOrderTotal.length - 1);

  return getFloatNumber(newOrderTotal, currency);
}

function getInstallmentAmount(newOrderTotal, oldOrderTotal) {
  var installmentAmount = newOrderTotal - oldOrderTotal;
  if (installmentAmount < 0.01 && installmentAmount > -0.01) {
    installmentAmount = 0;
  }

  return installmentAmount;
}

function getSystemCurrency() {
    var defaultCurrency = 'BRL';
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

function getFloatNumber(value, currency) {
    var currencyFormat = currencyTypes[currency];
    var floatNumber = value.replaceAll(currencyFormat.decimalSeparator, '').replace(currencyFormat.decimalDivisor, '.');

    return parseFloat(floatNumber, 10);
}

function insertOrderFeesHtml(feeAmount) {
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
    if ($(".woocommerce-checkout-review-order-table .cart-subtotal").length){
        $(".woocommerce-checkout-review-order-table .cart-subtotal").after(feeOrderHtml);
    }else
    {
        feeOrderHtml = `<tr class="tuna-order-fees"><th scope="row" colspan="2">${feeDescription}:</th><td class="product-total"><span class="woocommerce-Price-amount amount">
        <bdi><span class="woocommerce-Price-currencySymbol">${systemSymbol}</span>${formatCurrency(feeAmount, systemCurrency)}</span></bdi></td></tr>`;
        $(".product-total").last().parent().before(feeOrderHtml);
    }
}

function insertNewOrderTotalHtml(newOrderTotal) {
    var hasTotal = $('.order-total').length;
    $('.order-total').remove();
    var systemCurrency = getSystemCurrency();
    var systemSymbol = currencyTypes[systemCurrency].symbol;
    var orderTotalHtml = `<tr class="order-total"><th>Total</th><td><strong><span class="woocommerce-Price-amount amount"><bdi>
  <span class="woocommerce-Price-currencySymbol">${systemSymbol}</span>${formatCurrency(newOrderTotal, systemCurrency)}</bdi></span></strong></td></tr>`;

    var feesHtmlElement = $(".tuna-order-fees");
    var shippingHtmlElement = $('.woocommerce-shipping-totals');
    var orderSubTotalHtmlElement = $(".woocommerce-checkout-review-order-table .cart-subtotal");
    if (shippingHtmlElement.length && hasTotal) {
        shippingHtmlElement.after(orderTotalHtml);
    } else if (feesHtmlElement.length && hasTotal) {
        feesHtmlElement.after(orderTotalHtml);
    } else {
        if (orderSubTotalHtmlElement.length && hasTotal){
            orderSubTotalHtmlElement.after(orderTotalHtml);
        }else
        {
            //Order Pay Page
            $('.woocommerce-Price-amount').last().html('');
            var subtotal = $('.woocommerce-Price-amount').last();
            orderTotalHtml = `<span class="woocommerce-Price-amount amount"><bdi>
            <span class="woocommerce-Price-currencySymbol">${systemSymbol}</span>${formatCurrency(newOrderTotal, systemCurrency)}</bdi></span>`;
           
            subtotal.html(orderTotalHtml);
        }
    }
}

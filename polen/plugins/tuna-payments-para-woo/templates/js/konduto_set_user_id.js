var customerID = document.getElementById("tuna_tmpuser_id").value; // define o ID do cliente 
(function () {
    var period = 300;
    var limit = 20 * 1e3;
    var nTry = 0;
    var intervalID = setInterval(function () { // loop para retentar o envio         
        var clear = limit / period <= ++nTry;
        if ((typeof (Konduto) !== "undefined") &&
            (typeof (Konduto.setCustomerID) !== "undefined")) {
            window.Konduto.setCustomerID(customerID); // envia o ID para a Konduto      
            clear = true;
        }
        if (clear) {
            clearInterval(intervalID);
        }
    }, period);
})(customerID);

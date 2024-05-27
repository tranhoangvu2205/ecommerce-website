(function ($, window, document) {
    "use strict";

    var endpoint = $("#endpointUrl").val();
    var nonce = $("#momononce").val();
    var sandbox = $("#isSandbox").val();

    var paymentCheck = {
        id: $("#paymentId").val(), //'9be3c2fc-1111-4466-973b-e617a9c82481'
        status: "",
        counter: 1,
        maxCounter: 60,
        requestInterval: 1, //in 5 seconds
    };

    function checkPayment() {
        timer();
    }

    function showError(text) {
        console.log(text);
        $("#momopay_errorBox").html(text);
    }

    function hideCancelBtn() {
        $("#cancelCurrentOrder").hide();
    }

    function updPaymentProcessDesc(text) {
        $("#momopay_processDescBox").html(text);
    }

    function timer() {
        if (sandbox === "1" && paymentCheck.counter < 7) {
            paymentCheck.counter++;
            setTimeout(function () {
                timer();
            }, paymentCheck.requestInterval * 1000);
            return;
        }

        const requestBody = {
            action: "momopay_ajax",
            _ajax_nonce: nonce,
            data: {
                mode: "check_payment",
                payment_id: paymentCheck.id,
            },
        };
        $.ajax({
            method: "post",
            url: endpoint,
            data: requestBody,
            dataType: "json",
            success: function (res) {
                if (!res.code || res.code == 423) {
                    if (res.message) {
                        updPaymentProcessDesc(res.message);
                    }
                    hideCancelBtn();
                    return;
                }

                if (res.status) paymentCheck.status = res.status;

                if (paymentCheck.status === "paid") {
                    location.href = $("#orderReceivedUrl").val();
                    return;
                } else if (paymentCheck.status === "failed") {
                    location.href = $("#cartPaymentWithIdUrl").val();
                    return;
                } else {
                    if (res.desc) {
                        showError(desc);
                    }

                    if (paymentCheck.counter <= paymentCheck.maxCounter) {
                        var timeToFire = paymentCheck.requestInterval * 5000;
                        if (paymentCheck.counter < 3) {
                            timeToFire = paymentCheck.requestInterval * 1000;
                        }
                        paymentCheck.counter++;
                        setTimeout(function () {
                            timer();
                        }, timeToFire);
                    } else {
                        location.href = $("#cartPaymentWithIdUrl").val();
                    }
                }
            },
            error: function () {
                if (paymentCheck.counter <= paymentCheck.maxCounter) {
                    var timeToFire = paymentCheck.requestInterval * 5000;
                    if (paymentCheck.counter < 3) {
                        timeToFire = paymentCheck.requestInterval * 1000;
                    }
                    paymentCheck.counter++;
                    setTimeout(function () {
                        timer();
                    }, timeToFire);
                } else {
                    location.href = $("#cartPaymentWithIdUrl").val();
                }
            },
        });
    }

    function isPaymentPage() {
        return location.pathname.indexOf("order-pay") == -1 ? false : true;
    }

    function isFailedPayment() {
        return !$("#paymentId").val() ? true : false;
    }

    $(document).ready(function () {
        if (!isPaymentPage()) return;

        if (!isFailedPayment()) checkPayment();

        $("body").on("click", "#cancelCurrentOrder", function () {
            location.href = $("#orderCancelUrl").val();
        });
    });
})(jQuery, window, document);

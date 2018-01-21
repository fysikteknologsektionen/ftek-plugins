function setCookie(key, value, days) {
    var expires = new Date();
    expires.setTime(expires.getTime() + (days * 24 * 60 * 60 * 1000));
    document.cookie = key + '=' + value +';path=/'+ ';expires=' + expires.toUTCString();
}

function getCookie(key) {
    var keyValue = document.cookie.match('(^|;) ?' + key + '=([^;]*)(;|$)');
    return keyValue ? keyValue[2] : null;
}

function fetchCardData() {
    $("p#error-message").text("");
    $("div#card-message").show();
    $("p#fetch-message").show();
    $("div#card-input").hide();
    var data = {
        'action': 'card_action',
        'card-number-encrypted': getCookie('chalmers-card')
    };
    $.post(ajax_object.ajax_url, data, function(cardNumber) {
        $.ajax({
            url: "https://ftek.se/api/card-balance/v1/" + cardNumber,
            success: function(cardData){
                fillCardData(cardData);
            },
            timeout: 3000,
            error: function(e) {
                resetCard();
                $("div#card-message").show();
                if ("responseJSON" in e) { 
                    $("p#error-message").text(e.responseJSON.error);
                } else {
                    $("p#error-message").text("Request timed out.");
                }
                $("p#error-message").show();
            }
        });
    });
}

function fillCardData(cardData) {
    $("div#card-message").hide();
    $("#card-holder").text(cardData.cardHolder);
    $("#card-balance").text(cardData.cardBalance.value + " " + ajax_object.currency + " ");
    $("div#card-info").show();
}

function resetCard() {
    setCookie('chalmers-card', "", -30 * 6); // Remove cookie by settings negative time
    $("p#error-message").text("");
    $("div#card-info").hide();
    $("div#card-message").hide();
    $("p#fetch-message").hide();
    $("div#card-input").show();
}

jQuery(document).ready(function($){
    if (getCookie('chalmers-card') !== null) {
        fetchCardData();
    }

    $("form#card-form").submit(function() {
        var cardNumber = $("input#card-number").val().replace(/\s/g, '');
        $("input#card-number").val(cardNumber);
        if (/^\d{16}/.test(cardNumber)) {
            var data = {
                'action': 'card_action',
                'card-number': cardNumber
            };
            $.post(ajax_object.ajax_url, data, function(response) {
                setCookie('chalmers-card', response, 365 * 5);
                fetchCardData();
            });
        } else {
            resetCard();
            $("div#card-message").show();
            $("p#error-message").text(ajax_object.wrong_format);
            $("p#error-message").show();
        }
        return false; // Stop form from submitting
    });

    $("button#remove-card").click(function() {
        resetCard();
    });
});

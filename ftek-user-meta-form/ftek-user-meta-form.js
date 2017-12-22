jQuery(document).ready(function($){

    $("form#meta-form").submit(function(e) {

        e.preventDefault();
        if ($("#personal-number").length != 0) {
            var regexPersonalNumber = /[0-9]{2}((0[0-9])|(10|11|12))(([0-2][0-9])|(3[0-1]))-[0-9]{4}/;
            var personalNumber = $("#personal-number").val().replace(" ","");
            if (regexPersonalNumber.test(personalNumber)) {
                personalNumber = personalNumber.match(regexPersonalNumber)[0];
                $("#personal-number").val(personalNumber);
            } else {
                $("#personal-number-message").text(" YYMMDD-XXXX.");
                return false;
            }
        }
        if ($("#nickname").length != 0) {
            if ($("#nickname").val() === "") {
                $("#nickname-message").text(" Fyll i ditt smeknamn (eller förnamn om du inte har ett).");
                return false;
            }
        }
        if ($("#phone-number").length != 0) {
            if ($("#phone-number").val() === "") {
                $("#phone-number-message").text(" Fyll i ditt mobilnummer.");
                return false;
            } else {
                var phoneNumber = $("#phone-number").val().replace(" ","").replace("-","");
                $("#phone-number").val(phoneNumber);
            }
        }
        $.post( ftek_user_meta_obj.ajaxurl, {
            action : 'ftek_update_meta',
            post : $("form#meta-form").serialize()
        },
        function(response) {
            if (response === "Updated") {
                $("#personal-number-message").text("");
                $("#nickname-message").text("");
                $("#phone-number-message").text("");
                $("#form-message").text(" Sparat.");
            }
        });

        return false; // Prevent form from submitting
    });

});

(function ($) {
    $( document ).ready( function() {
        $(".send-message-button").click(function() {
            sendMessage();
        });
        $(".login-button").click(function() {
            loginUser();
        });
        $(".register-button").click(function() {
            registerUser();
        });

    });

    function loginUser() {
        var username = $(".username-textcontrol input").val();
        var password = $(".password-textcontrol input").val();

        var data = {
            'action' : 'matrix_login',
            'user' : username,
            'password': password
        };

        $.post(settings.ajaxurl, data, function(response) {
            console.log('login_user returned');
            console.log('response = ' + JSON.stringify(response));

            var result = JSON.parse(response.data);

            if (result["success"] === true) {
                markUserAsLoggedIn(result["user_id"], result["access_token"])
            }
        });
    }

    function registerUser() {

        var username = $(".username-textcontrol input").val();
        var password = $(".password-textcontrol input").val();

        var data = {
            'action' : 'matrix_register',
            'user' : username,
            'password': password
        };

        $.post(settings.ajaxurl, data, function(response) {

            console.log('register_user returned');
            console.log('response = ' + JSON.stringify(response));

            var result = JSON.parse(response.data);

            if (result["success"] === true) {
                markUserAsLoggedIn(result["user_id"], result["access_token"])
            }
        });
    }

    function markUserAsLoggedIn(userId, accessToken) {
        var matrixChatNamespace = window.matrixChatNamespace || {};
        matrixChatNamespace.loggedInUserId = userId;
        matrixChatNamespace.loggedInUserAccessToken = accessToken;
    }

    function sendMessage() {
        var message = $(".chat-message-textcontrol input").val();

        var matrixChatNamespace = window.matrixChatNamespace || {};
        if (matrixChatNamespace.loggedInUserId == null) {
            $("#matrix-chat-input-errors").text('Could not send message. User not logged in.');
            return;
        }

        var data = {
            'action' : 'submit_message',
            'user' : matrixChatNamespace.loggedInUser,
            'message': message
        };
        $.get(settings.ajaxurl, data, function(response) {
            console.log('ok');
            console.log('response = ' + JSON.stringify(response));
            //eventId = JSON.parse(response.data.eventId);

            //TODO: remove this XSS attack by cleansing.. Also, should I create <li> via Javascript rather than text?
            $('.matrix-chat-output-list').append('<li>' + matrixChatNamespace.loggedInUser + ": " + message + '</li>');
        });


    }
})(jQuery);

(function ($) {
    window.matrixChatNamespace= {};
    $( document ).ready( function() {
        $( '.matrix-chat-input' ).on( 'click', '.matrix-chat-submit', function( event ) {
            submitMessage();
        } );
        $( '.matrix-chat-login' ).on( 'click', '.matrix-chat-register', function( event ) {
            registerUser();
        } );
        $( '.matrix-chat-login' ).on( 'click', '.matrix-chat-login', function( event ) {
            loginUser();
        } );
    } );

    function submitMessage() {
        var message = $("#matrix-chat-textbox").val();

        var matrixChatNamespace = window.matrixChatNamespace || {};
        if (matrixChatNamespace.loggedInUser == null) {
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
            $('#matrix-chat-output-list').append('<li>' + matrixChatNamespace.loggedInUser + ": " + message + '</li>');
        });

    }
    function registerUser() {
        var username = $("#matrix-chat-username-textbox").val();
        var password = $("#matrix-chat-password-textbox").val();

        var matrixChatNamespace = window.matrixChatNamespace || {};
    }
    function loginUser() {
        var username = $("#matrix-chat-username-textbox").val();
        var password = $("#matrix-chat-password-textbox").val();

        var matrixChatNamespace = window.matrixChatNamespace || {};
    }

})(jQuery);

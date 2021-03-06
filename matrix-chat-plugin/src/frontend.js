(function ($) {
    window.matrixChatNamespace = {}

    $( document ).ready( function() {
        $(".send-message-button").click(function() {
            sendMessage();
        });
        $(".login-button").click(function() {
            loginUser();
            $(".join-room-textcontrol input").focus();
        });
        $(".register-button").click(function() {
            registerUser();
            $(".join-room-textcontrol input").focus();
        });
        $(".password-textcontrol input").keypress(function(e) {
            var keycode = (event.keyCode ? event.keyCode : event.which);
            if (keycode == "13") {
                loginUser();
                $(".join-room-textcontrol input").focus();
            }
        });

        $(".join-room-button").click(function() {
            joinRoom();
            $(".chat-message-textcontrol input").focus();
        });
        $(".create-room-button").click(function() {
            createRoom();
            $(".chat-message-textcontrol input").focus();
        });

        $(".chat-message-textcontrol input").keypress(function(e) {
            var keycode = (event.keyCode ? event.keyCode : event.which);
            if (keycode == "13") {
                sendMessage();
            }
        });
        $(".get-room-state-button").click(function() {
            getCurrentRoomState();
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

            if (response["success"] === true) {
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

            if (response["success"] === true) {
                markUserAsLoggedIn(result["user_id"], result["access_token"])
            }
        });
    }

    function createRoom() {
        $(".matrix-chat-room-input-errors").text("");
        var roomName = $(".room-name-textcontrol input").val();

        var matrixChatNamespace = window.matrixChatNamespace || {};
        var userAccessToken = matrixChatNamespace.loggedInUserAccessToken;
        if (userAccessToken == null) {
            $(".matrix-chat-room-input-errors").text('Could not create room. User not logged in.');
            return;
        }

        var data = {
            'action' : 'matrix_create_room',
            'room_alias_name' : roomName,
            'access_token' : userAccessToken
        };
        $.post(settings.ajaxurl, data, function(response) {

            console.log('create_room returned');
            console.log('response = ' + JSON.stringify(response));

            var result = JSON.parse(response.data);

            if (response["success"] !== true) {
                $(".matrix-chat-room-input-errors").text("Could not create room " + roomName + ". Unknown error (success !== true)");
                return
            }
            if (result["errcode"]) {
                $(".matrix-chat-room-input-errors").text("Could not create room " + roomName + ". Error: " + result["error"]);
                return
            }

            markRoomAsJoined(result["room_alias"], result["room_id"])
        });
    }
    function joinRoom() {
        $(".matrix-chat-room-input-errors").text("");
        var roomIdOrAlias = $(".room-name-textcontrol input").val();

        var matrixChatNamespace = window.matrixChatNamespace || {};
        var userAccessToken= matrixChatNamespace.loggedInUserAccessToken;
        if (userAccessToken == null) {
            $(".matrix-chat-room-input-errors").text('Could not join room. User not logged in.');
            return;
        }

        if (roomIdOrAlias.includes(":") === false) {
            var matrixServerName = getServerNameFromIdentifier(matrixChatNamespace.loggedInUserId);
            roomIdOrAlias = roomIdOrAlias + ":" + matrixServerName;
        }

        var data = {
            'action' : 'matrix_join_room',
            'room_id_or_alias' : roomIdOrAlias,
            'access_token' : userAccessToken 
        };
        $.post(settings.ajaxurl, data, function(response) {

            console.log('join_room returned');
            console.log('response = ' + JSON.stringify(response));

            var result = JSON.parse(response.data);

            if (response["success"] !== true) {
                $(".matrix-chat-room-input-errors").text("Could not join room " + roomIdOrAlias + ". Unknown error (success !== true)");
                return
            }
            if (result["errcode"]) {
                $(".matrix-chat-room-input-errors").text("Could not join room " + roomIdOrAlias + ". Error: " + result["error"]);
                return
            }

            markRoomAsJoined(result["room_alias"], result["room_id"])
            getCurrentRoomState();
            setInterval(function() {
                console.log("getting messages");
                getCurrentRoomState();
            }, 1000);
        });
    }

    function getServerNameFromIdentifier(id) {
        var arr = id.split(":");
        return arr[arr.length - 1];
    }

    function markUserAsLoggedIn(userId, accessToken) {
        var matrixChatNamespace = window.matrixChatNamespace || {};
        matrixChatNamespace.loggedInUserId = userId;
        matrixChatNamespace.loggedInUserAccessToken = accessToken;
        $(".matrix-chat-logged-in-user").text("Logged in user: " + userId);
    }
    function markRoomAsJoined(roomAlias, roomId) {
        var matrixChatNamespace = window.matrixChatNamespace || {};
        matrixChatNamespace.activeRoomId = roomId;
        matrixChatNamespace.activeRoomAlias = roomAlias;

        var roomIdentifier = (roomAlias) ? (roomAlias) : (roomId);

        $(".matrix-chat-active-room").text("In room: " + roomIdentifier);
    }

    function sendMessage() {
        $(".matrix-chat-input-errors").text('');
        var message = $(".chat-message-textcontrol input").val();

        var matrixChatNamespace = window.matrixChatNamespace || {};
        var userAccessToken = matrixChatNamespace.loggedInUserAccessToken;
        var activeRoomId = matrixChatNamespace.activeRoomId;
        var userId = matrixChatNamespace.loggedInUserId;

        if (userAccessToken == null) {
            $(".matrix-chat-input-errors").text('Could not send message. User not logged in.');
            return;
        }
        if (activeRoomId == null) {
            $(".matrix-chat-input-errors").text('Could not send message. User not in a room.');
            return;
        }

        var data = {
            'action' : 'send_message',
            'access_token' : userAccessToken,
            'room_id' : activeRoomId,
            'body': message
        };
        $.post(settings.ajaxurl, data, function(response) {
            console.log('ok');
            console.log('response = ' + JSON.stringify(response));

            var result = JSON.parse(response.data);

            $(".chat-message-textcontrol input").val("");
            if (response["success"] !== true) {
                $(".matrix-chat-input-errors").text("Could not send message. Unknown error (success !== true)");
                return
            }
            if (result["errcode"]) {
                $(".matrix-chat-input-errors").text("Could not send message. Error: " + result["error"]);
                return
            }

            //TODO: remove this XSS attack by cleansing.. Also, should I create <li> via Javascript rather than text?
            $('.matrix-chat-output-list').append('<li>' + userId + ": " + message + '</li>');
        });
    }
    function getCurrentRoomState() {
        var matrixChatNamespace = window.matrixChatNamespace || {};
        var userId = matrixChatNamespace.loggedInUserId;
        var userAccessToken = matrixChatNamespace.loggedInUserAccessToken;
        var activeRoomId = matrixChatNamespace.activeRoomId;

        if (userAccessToken == null) {
            $(".matrix-chat-get-state-errors").text('Could not get room state. User not logged in.');
            return;
        }
        if (activeRoomId == null) {
            $(".matrix-chat-get-state-errors").text('Could not get room state. User not in a room.');
            return;
        }

        var data = {
            'action': 'matrix_initial_get_messages',
            'access_token' : userAccessToken,
            'room_id' : activeRoomId,
        };
        $.get(settings.ajaxurl, data, function(response) {
            console.log('ok');
            console.log('response = ' + JSON.stringify(response));

            var result = JSON.parse(response.data);

            if (response["success"] !== true) {
                $(".matrix-chat-get-state-errors").text("Could not get state. Unknown error (success !== true)");
                return
            }
            if (result["errcode"]) {
                $(".matrix-chat-get-state-errors").text("Could not get state. Error: " + result["error"]);
                return
            }

            $('.matrix-chat-output-list').empty();
            var messages = result["messages"];
            messages.forEach(function(message) {
                if (message["type"] == "m.room.message" && message["content"]["msgtype"] == "m.text") {
                    $('.matrix-chat-output-list').append('<li>' + message['sender'] + ": " + message["content"]["body"] + '</li>');
                }
            });
        });
    }
})(jQuery);

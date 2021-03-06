import { __ } from '@wordpress/i18n';
import { registerBlockType } from '@wordpress/blocks';
import { RichText, BlockControls, AlignmentToolbar } from '@wordpress/block-editor';
import { Button, TextControl } from '@wordpress/components';

registerBlockType('matrix-chat-plugin/matrix-chat-plugin-block', {
    title: __('Matrix.org Chat', 'matrix-chat-plugin'),
    icon: 'index-card',
    category: 'widgets',
    attributes: {
        title: {
            type: "string",
            source: "children"
        },
        alignment: {
            type: "string"
        },
        defaultMessage: {
            type: "string"
        }

    },
    edit: props => {
        const {
            className,
            isSelected,
            attributes: { title, defaultMessage, alignment },
            setAttributes
        } = props;

        const onChangeTitle = value => {
            setAttributes({title: value});
        };

        const onChangeMessage = value => {
            setAttributes({defaultMessage: value});
        };

        return (
            <>
                {isSelected && (
                    <BlockControls key="controls">
                    <AlignmentToolbar
                    value={alignment}
                    onChange={nextAlign => {
                        setAttributes({ alignment: nextAlign });
                    }}
                    />
                </BlockControls>
                )}

                <div className={className}>
                    <RichText
                        tagName="h2"
                        className="callout-title"
                        placeholder={__("Write a title for the Matrix chat plugin")}
                        value={title}
                        onChange={onChangeTitle}
                    />

                    <TextControl
                        label="Username"
                        className="username-textcontrol"
                    />
                    <TextControl
                        label="Password"
                        className="password-textcontrol"
                    />
                    <Button isPrimary className="login-button">
                        Login
                    </Button>
                    <Button isSecondary className="register-button">
                        Register
                    </Button>

                    <TextControl
                        label="Room name"
                        className="room-name-textcontrol"
                    />
                    <Button isPrimary className="join-room-button">
                        Join
                    </Button>
                    <Button isPrimary className="create-room-button">
                        Create
                    </Button>


                    <TextControl
                        label="Chat message"
                        className="chat-message"
                        value={defaultMessage}
                        onChange={onChangeMessage}
                    />
                    <Button isPrimary>
                        {__("Send message")}
                    </Button>
                </div>
            </>
        );
    },
    save: props => {
        const {
            className,
            attributes: {title, defaultMessage, alignment}
        } = props;

        const onClickSendMessageButton = e => {
            e.preventDefault();
            console.log("Button clicked");
        };

        return (
            <div className={className}>
                <RichText.Content
                    tagName="h2"
                    className="callout-title"
                    value={title}
                />
                
                <TextControl
                    label="Username"
                    className="username-textcontrol"
                />
                <TextControl
                    label="Password"
                    className="password-textcontrol"
                />
                <Button isPrimary className="login-button">
                    Login
                </Button>
                <Button isSecondary className="register-button">
                    Register
                </Button>

                <TextControl
                    label="Room name"
                    className="room-name-textcontrol"
                />
                <span className="matrix-chat-room-input-errors"></span><br />
                <Button isPrimary className="join-room-button">
                    Join
                </Button>
                <Button isPrimary className="create-room-button">
                    Create
                </Button>

                <ul className="matrix-chat-output-list">
                </ul>

                <span className="matrix-chat-logged-in-user"></span><br />
                <span className="matrix-chat-active-room"></span><br />
                <TextControl
                    label="Chat message"
                    className="chat-message-textcontrol"
                    value={defaultMessage}
                />
                <span className="matrix-chat-input-errors"></span><br />
                <Button isPrimary className="send-message-button">
                    {__("Send message")}
                </Button>

                <Button isPrimary className="get-room-state-button">
                    Get state
                </Button>
                <span className="matrix-chat-get-state-errors"></span>
            </div>
        );
    }
});
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

#!/usr/bin/env bash
npm run build && cd .. && rm matrix-chat-plugin.zip && zip -r matrix-chat-plugin.zip matrix-chat-plugin -x '*node_modules*' && cd matrix-chat-plugin

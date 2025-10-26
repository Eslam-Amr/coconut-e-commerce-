const express = require('express');
const http = require('http');
const { Server } = require('socket.io');
const { createClient } = require('redis');
const { join } = require('node:path');

const app = express();
const server = http.createServer(app);
const io = new Server(server);

let lastMessage = null;

process.on('uncaughtException', (err) => {
    console.error('Uncaught Exception:', err);
});

process.on('unhandledRejection', (reason, promise) => {
    console.error('Unhandled Rejection at:', promise, 'reason:', reason);
});

const subscriber = createClient();
const publisher = createClient();

subscriber.on('error', (err) => {
    console.error('Redis subscriber error:', err);
});

publisher.on('error', (err) => {
    console.error('Redis publisher error:', err);
});

subscriber.connect();
publisher.connect();

subscriber.on('connect', () => {
    console.log('Redis subscriber connected');
});

publisher.on('connect', () => {
    console.log('Redis publisher connected');
});

app.get('/', (req, res) => {
    res.sendFile(join(__dirname, 'index.html'));
});
// Add middleware
app.use(express.json());
app.use(express.urlencoded({ extended: true }));

app.post('/send-to-laravel', async (req, res) => {
    try {
        // const { message, type = 'notification' } = req.body;
        const message = req.body.message;

        if (!message) {
            return res.status(400).json({ error: 'Message is required' });
        }

        const data = {
            source: 'NODEJS',
            message,
            timestamp: new Date().toISOString()
        };

        await publisher.publish('laravel-events', JSON.stringify(data));

        io.emit('laravel-message', {
            source: 'NODEJS',
            data: data,
            timestamp: new Date().toISOString()
        });

        res.json({
            success: true,
            message: 'Message sent to Laravel and Socket.IO clients',
            data
        });
    } catch (error) {
        console.error('Error sending message:', error);
        res.status(500).json({ error: 'Failed to send message' });
    }
});





subscriber.subscribe('laravel-events', (message) => {


    try {
        if (typeof message === 'string' && !message.trim().startsWith('{')) {
            const rawMsg = `[RAW] ${message}`;
            console.log(rawMsg);
            io.emit('chat message', rawMsg);
            return;
        }

        const parsedMessage = JSON.parse(message);
        console.log(parsedMessage);

        // console.log(parsedMessage);
        if (parsedMessage.user_id === 'NODEJS') {
            if (parsedMessage.fromBrowser) {
                const messageText = parsedMessage.message;
                console.log(`[NODEJS] ${messageText}`);
                return;
            }
            return;
        }

        let displayMessage = '';

        if (parsedMessage && parsedMessage.data && parsedMessage.data.message) {
            const sender = parsedMessage.data.sender || 'Unknown';
            const messageText = parsedMessage.data.message;
            displayMessage = `[${sender}] ${messageText}`;
        }
        else if (parsedMessage && parsedMessage.message) {
            let sender = 'LARAVEL';
            if (parsedMessage.source != null) {
                sender = parsedMessage.source;
            }
            else {
                sender = 'LARAVEL';
            }
            const messageText = parsedMessage.message;
            const messageKey = `${parsedMessage.user_id}-${messageText}-${parsedMessage.timestamp}`;
            if (lastMessage === messageKey) {
                return;
            }
            lastMessage = messageKey;

            displayMessage = `[${sender}] ${messageText}`;
        }


        console.log(displayMessage);
        io.emit('chat message', displayMessage);

    } catch (error) {
        console.error('Error parsing message:', error);
        console.log('Message is not valid JSON, treating as plain text');
        io.emit('chat message', message);
    }
});

// io.on('connection', (socket) => {
//     console.log('a user connected');
//     socket.on('chat message', async (msg) => {
//         console.log("publish");

//         await publisher.publish('laravel-events', JSON.stringify({
//             event: 'chat',
//             source: 'NODEJS',
//             message: msg,
//             user_id: 'NODEJS',
//             timestamp: new Date().toISOString(),
//             fromBrowser: true
//         }));
//     });
// });


server.listen(3000, () => {
    console.log('listening on *:3000');
});

const audioPattern = `
    <audio id="{ID}" class="hidden" controls>
        <source src="{SRC}" type="audio/mp3" />
        Your browser does not support the audio element.
    </audio>
`;
const topics = [
    'channel-points-channel-v1.{{ user.getTwitchId() }}',
];

let ws;
let htmlOutput = false;
let twitchOAuthToken = '{{ user.getTwitchOAuth() }}';

let sounds = [];
let queue = [];

function init() {
    getUserMedia({audio: true})
        .then(function (stream) {
            if (stream === null) {
                output('ERR', 'Audio permission required');
                console.error(`${stream} is null`);

                return;
            }

            if (stream.active === false) {
                output('ERR', 'Audio permission required');
                console.error(`${stream} is not active`);
            }
        })
        .catch(function (err) {
            output('ERR', err);

            console.error(err);
        });

    connect();

    setTimeout(
        () => {
            for (let topic of topics) {
                listen(topic);
            }
        },
        1000
    );


    setInterval(playQueue, 1000);
}

function connect() {
    const heartbeatInterval = 1000 * 60; //ms between PING's
    const reconnectInterval = 1000 * 3; //ms to wait before reconnect
    let heartbeatHandle;

    ws = new WebSocket('wss://pubsub-edge.twitch.tv');

    ws.onopen = function() {
        output('INFO', 'Socket Opened');
        heartbeat();
        heartbeatHandle = setInterval(heartbeat, heartbeatInterval);
    };

    ws.onerror = function(error) {
        output('ERR', JSON.stringify(error));
    };

    ws.onmessage = function(event) {
        let message = JSON.parse(event.data);

        output('RECV', JSON.stringify(message));

        if (message.type === 'MESSAGE') {
            addSoundByResponse(message);
        }

        if (message.type === 'RECONNECT') {
            output('INFO', 'Reconnecting...');
            setTimeout(connect, reconnectInterval);
        }
    };

    ws.onclose = function() {
        output('INFO', 'Socket Closed');
        clearInterval(heartbeatHandle);

        output('INFO', 'Reconnecting...');
        setTimeout(connect, reconnectInterval);
    };
}

function heartbeat() {
    let message = {
        type: 'PING'
    };

    output('SENT', JSON.stringify(message));
    ws.send(JSON.stringify(message));
}

function output(type, message) {
    if (htmlOutput) {
        let outputArea = document.getElementById('ws-output');
        outputArea.append(`${type}: ${message}\n`);
        outputArea.scrollTop = outputArea.scrollHeight;
    } else {
        console.log(`${type}: ${message}`);
    }
}

function showHtmlOutput() {
    htmlOutput = true;

    document.getElementById('socket').classList.toggle('hidden');

    document.getElementById('topic-form').onsubmit = function(event) {
        listen(document.getElementById('topic-text').value);
        event.preventDefault();
    };
}

function listen(topic) {
    let message = {
        type: 'LISTEN',
        nonce: nonce(15),
        data: {
            topics: [topic],
            auth_token: twitchOAuthToken
        }
    };

    output('SENT', JSON.stringify(message));
    ws.send(JSON.stringify(message));
}

function nonce(length) {
    let text = "";
    let possible = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";

    for (let i = 0; i < length; i++) {
        text += possible.charAt(Math.floor(Math.random() * possible.length));
    }

    return text;
}

function addSoundByResponse(response) {
    let data = JSON.parse(response.data.message);

    if (data.type === 'reward-redeemed') {
        let rewardTitle = data.data.redemption.reward.title;

        addToQueue(rewardTitle);
    }
}

function addToQueue(rewardTitle) {
    let soundElement = null;
    for (let i = 0; i < sounds.length; i++) {
        if (sounds[i].name === rewardTitle) {
            soundElement = sounds[i];
            break;
        }
    }

    if (soundElement === null) {
        return;
    }

    queue.push(soundElement);
}

function playQueue() {
    let players = document.getElementsByTagName('audio');
    if (players.length === 1) {

        let soundFinish = players[0].ended;
        if (soundFinish === false) {
            return;
        }
    }

    if (queue.length === 0) {
        return;
    }

    let nextSoundElement = queue[0];
    playSound(nextSoundElement);

    queue.shift();
}

function playSound(soundElement) {
    let audioWrapper = document.getElementById('js-audio-wrapper');
    let audioPlayer = audioPattern;

    if (soundElement.playerId === undefined || soundElement.src === undefined) {
        return;
    }

    audioPlayer = audioPlayer.replace('{ID}', soundElement.playerId);
    audioPlayer = audioPlayer.replace('{SRC}', soundElement.src);

    audioWrapper.innerHTML = audioPlayer;

    document.getElementById(soundElement.playerId).play();
}

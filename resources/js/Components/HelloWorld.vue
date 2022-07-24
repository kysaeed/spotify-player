<template>
    <div class="hello-box">
        <h1>やあ、世界！</h1>
        <div>hello world</div>
        <h2>カウンタ：{{ a }}</h2>
        <div>
            <button @click="add">add!</button>
        </div>
        <div style="padding: 30px;">
            <!-- token: {{token}} -->
            <h1>Spotify Web Playback SDK Quick Start</h1>
            <button id="togglePlay">Toggle Play</button>
            <button id="testlog">Show LOG!</button>
        </div>

    </div>
</template>

<script>


export default {
    name: 'hello-world',

    props: {
        token: {
            type: String,
            default: '',
        },
    },

    methods: {
        add() {
            this.a++
        },
    },
    mounted() {
            // https://developer.spotify.com/documentation/web-playback-sdk/reference/#event-player-state-changed

            window.onSpotifyWebPlaybackSDKReady = () => {
                const token = this.token
                const player = new Spotify.Player({
                    name: 'Web Playback SDK Quick Start Player',
                    getOAuthToken: cb => { cb(token); },
                    volume: 0.5
                });

                // Ready
                player.addListener('ready', ({ device_id }) => {
                    console.log('Ready with Device ID', device_id);
                });

                // Not Ready
                player.addListener('not_ready', ({ device_id }) => {
                    console.log('Device ID has gone offline', device_id);
                });

                player.addListener('initialization_error', ({ message }) => {
                    console.error(message);
                });

                player.addListener('authentication_error', ({ message }) => {
                    console.error(message);
                });

                player.addListener('account_error', ({ message }) => {
                    console.error(message);
                });

                //player_state_changed
                player.addListener('player_state_changed', (arg ) => {
                    console.log('player_state_changed', arg);
                });

                document.getElementById('togglePlay').onclick = function() {
                  player.togglePlay();
                };

                document.getElementById('testlog').onclick = function() {
                  player.getCurrentState().then((s) => {
                    console.log('***', s)

                  });
                };


                player.connect();
            }




    },
    data() {
        return {
            a: 0,
        }
    },
}
</script>

<style land="scss" scoped>
.hello-box {
    position: relative;
    margin: 20px;
    padding: 10px;
    border: 1px solid #101010;
    border-radius: 10px;
}
</style>
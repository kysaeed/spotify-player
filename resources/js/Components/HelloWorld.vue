<template>
    <div class="hello-box">
        <h1>やあ、世界！</h1>
        <div>hello world</div>
<!--
        <div class="hello-box-child">
            <h1>vue.js 動作サンプル</h1>
            <h2>カウンタ：{{ a }}</h2>
            <div>
                <button @click="add">add!</button>
            </div>
        </div>
 -->

        <div class="hello-box-child">
            <!-- token: {{token}} -->
            <h1>from Spotify Web Playback SDK Quick Start</h1>
            <button id="prev" class="ui-button" >◀ P</button>
            <button id="togglePlay" class="ui-button" >Toggle Play</button>
            <button id="next" class="ui-button" >N ▶</button>
        </div>

        <div>
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
                    name: 'Laravel Web Player',
                    getOAuthToken: cb => { cb(token); },
                    volume: 0.8
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


                document.getElementById('prev').onclick = function() {
                  player.previousTrack();
                };
                document.getElementById('next').onclick = function() {
                  player.nextTrack();
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

<style lang="scss" scoped>
.hello-box {
    position: relative;
    margin: 20px;
    padding: 10px;
    border: 1px solid #101010;
    border-radius: 10px;
}
.hello-box-child {
    position: relative;
    margin: 10px;
    padding: 20px;
    border: 1px solid #808080;
}

.ui-button {
    position: relative;
    padding: 8px 12px;
    margin: 4px;
    font-size: 1.4rem;
}
</style>
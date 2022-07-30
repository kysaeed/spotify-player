<template>
    <div class="hello-box">
        <div class="hello-box-child">
            <!-- token: {{token}} -->
            <h1>Laravel Web Player</h1>
            <button id="prev" class="ui-button" >← P</button>
            <button id="togglePlay" class="ui-button" >▶Toggle Play</button>
            <button id="next" class="ui-button" >N →</button>
        </div>
        <div v-if="item" class="hello-box-child hello-box-child">
            <div class="track-info">
                <div>
                    <h1>{{ item.name }}</h1>
                    <div v-for="a in item.album.artists" :key="a.id">
                        <h2>{{ a.name }}</h2>
                    </div>
                </div>

                <div v-if="item.album && item.album.images.length > 1">
                    <img
                        :src="item.album.images[1].url"
                    />
                </div>
            </div>

            <div v-if="state">
                <input
                    v-model="progress"
                    class="progress"
                    type="range"
                    min="0"
                    :max="state.duration"
                    @change="onSeek"
                />
                <div>{{ toTime(progress) }} : {{ toTime(state.duration) }}</div>
                <!-- <div>{{ (progress) }} : {{ (state.duration) }}</div> -->
            </div>
        </div>
        <div class="hello-box-child">
            <h1>state</h1>
            <div v-if="state">
            </div>
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
        startInterval() {
            const cb = () => {
                this.player.getCurrentState().then((s) => {
                    // debugger
                    // console.log('***', s)
                    window.setTimeout(cb, 1000);
                    this.state = s
                    this.progress = s.position
                });

            }

            cb()
        },
        onSeek(t) {
            console.log('onSeek ******', this.progress)
            this.player.seek(this.progress)
        },
        toTime(ms) {
            const zeroPad = (n) => {
                return `${n}`.padStart(2, '0')
            }
            let s = parseInt(ms / 1000) || 0
            const m = parseInt(s / 60) || 0
            s %= 60

            return `${zeroPad(m)}:${zeroPad(s)}`
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
            this.player = player

            // Ready
            player.addListener('ready', ({ device_id }) => {
                console.log('Ready with Device ID', device_id);

                this.axios.post('/device', {
                    device: device_id,
                }).then((res) => {
                    console.log(res)
                    this.startInterval()
                })
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

                this.axios.get('/track-info', {
                    // device: device_id,
                }).then((res) => {
                    console.log(res.data)
                    if (res.data) {
                        this.item = res.data.item
                    }
                })


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
    beforeDestroy() {
        if (this.player) {
            this.player.disconnect()
        }
    },
    data() {
        const axios = window.axios.create({
            responseType: 'json',
        });

        return {
            progress: 0,
            axios,
            item: null,
            state: null,
            player: null,
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

.track-info {
    position: relative;
    width: 100%;
    display: flex;
    flex-direction: row;
    justify-content: space-between;

}
.progress {
    position: relative;
    width: 100%;
}
</style>
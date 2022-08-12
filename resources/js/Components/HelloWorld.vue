<template>
    <div class="hello-box">
        <div class="hello-box-child">
            <!-- token: {{token}} -->
            <h1>Laravel Web Player</h1>
            <button @click="prev" class="ui-button" >← P</button>
            <button @click="play" class="ui-button" >
                <span v-if="isPause">▶ Play</span>
                <span v-if="!isPause"> ‖ Pause</span>
            </button>
            <button @click="next" class="ui-button" >N →</button>
        </div>

        <div class="hello-box-child">
            <vue-select
                v-model="selectedDeviceId"
                :options="options"
                :searchable="false"
                :clearable="false"
            >

            </vue-select>
        </div>

        <div v-if="item" class="hello-box-child">
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
                <div>{{ progress }}</div>
                <player-progress-bar
                    v-model="progressBar.cycles"
                    :duration="state.duration"
                    :is-pause="isPause"
                    @update="onSeekByBar"
                />
                <div>{{ toTime(progressBar.cycles) }} : {{ toTime(state.duration) }}</div>

                <input
                    v-model="progress"
                    class="progress"
                    type="range"
                    min="0"
                    :max="state.duration"
                    @change="onSeekDebug"
                />
            </div>
        </div>

<!--
        <div class="hello-box-child">
            <h1>state</h1>
            <div v-if="state">
                <pre>{{ state }}</pre>
            </div>
        </div>
 -->
        <div>
            <button @click="test">Show LOG!</button>
        </div>

    </div>
</template>

<script>
import PlayerProgressBar from './PlayerProgressBar.vue'
import {VueSelect} from 'vue-select'
import 'vue-select/dist/vue-select.css'

export default {
    name: 'hello-world',

    components: {
        PlayerProgressBar,
        VueSelect,
    },

    methods: {
        startInterval() {
            const cb = () => {
                this.player.getCurrentState().then((s) => {
                    window.setTimeout(cb, 1000);
                    if (s) {
                        this.progress = s.position
                    } else {
                        this.progress = 0
                    }
                });
            }
            cb()
        },
        onSeekByBar(t) {
            this.progress = t
            console.log('onSeekByBar ******', this.progress)
            this.player.seek(this.progress)
        },
        onSeekDebug(t) {
            console.log('onSeekDebug ******', this.progress)
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

        play() {
            this.player.togglePlay();
        },
        next() {
            // this.progress = 0
            this.player.nextTrack();
        },
        prev() {
            // this.progress = 0
            this.player.nextTrack();


        },

        test() {
            this.player.getCurrentState().then((s) => {
                console.log('***', s)
            });

        },

        onWindowActive() {
            this.player.getCurrentState().then((s) => {
                console.log('#onWindowActive', s)
                this.progressBar.cycles = s.position
            })
        },

        async onSearch() {

            // return {}
        },

    },
    mounted() {

// console.log(res)
// debugger

        // this.token = res.data.token

        const requestToken = (cb) => {
            this.axios.post('/access-token', {}).then((res) => {
                this.token = res.data.token
                cb(res.data.token)
            }).catch(() => {
                cb()
            })
        }

        window.onSpotifyWebPlaybackSDKReady = () => {
            const player = new Spotify.Player({
                name: 'Laravel Web Player',
                getOAuthToken: requestToken,
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
            player.addListener('player_state_changed', (arg) => {
                const s = arg
                if (s) {
                    this.progress = s.position

                    this.state = s
                    this.progressBar.cycles = s.position
                    this.isPause = s.paused

                    console.log('player_state_changed', arg);

                    this.axios.get('/track-info', {
                        // device: device_id,
                    }).then((res) => {
                        console.log(res.data)
                        if (res.data) {
                            this.item = res.data.item
                        }
                    })
                }

            });

            player.connect();
        }


        // https://developer.spotify.com/documentation/web-playback-sdk/reference/#event-player-state-changed


        window.addEventListener('focus', this.onWindowActive)

    },
    beforeDestroy() {
        if (this.player) {
            // this.player.disconnect()
        }
        window.removeEventListener('focus', this.onWindowActive)

    },
    data() {
        const axios = window.axios.create({
            responseType: 'json',
        });


        const progressBar = {
            charged: '0%',
            cycles: 0,
        }


        return {
            progress: 0,
            axios,
            item: null,
            state: null,
            player: null,
            isPause: true,
            p: {
                x:0,
                y:0,
            },
            progressBar,
            currrentAnimatin: null,
            toekn: null,

            selectedDeviceId: '',
            options: [
                {label: 'ここに再生デバイス選択を表示', value: '0'},
            ],

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
    height: 42px;
    position: relative;
    padding: 8px 12px;
    margin: 4px;
    // font-size: 1.4em;
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
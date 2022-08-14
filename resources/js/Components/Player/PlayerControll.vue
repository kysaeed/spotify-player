<template>
    <div >
        <div v-if="state">
            web player active: {{ isActive }} ({{state.playback_id}})
        </div>
        <button
            class="ui-button"
            @click="prev"
        >← P</button>
        <button
            class="ui-button"
            @click="play"
        >
            <span v-if="isPause">▶ Play</span>
            <span v-if="!isPause"> ‖ Pause</span>
        </button>
        <button
            class="ui-button"
            @click="next"
        >N →</button>

    </div>
</template>

<script>
import RemotePlayer from './RemotePlayer.js'

export default {
    name: 'player-controll',
    props: {
        title: {
            type: String,
            default: 'Web Player',
        },
    },

    created() {
    },
    mounted() {
        window.onSpotifyWebPlaybackSDKReady = () => {
            this.player = this.createWebPlayerObject()
        }
    },

    methods: {
        createWebPlayerObject() {
            /***
             * https://developer.spotify.com/documentation/web-playback-sdk/reference/#event-player-state-changed
             */

            const player = new Spotify.Player({
                name: this.title,
                getOAuthToken: this.requestToken,
                volume: 0.8
            });

            // Ready
            player.addListener('ready', (state) => {
                this.onReady(state)
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
            player.addListener('player_state_changed', (state) => {
                this.onStateChange(state)
            });

            player.connect();
            return player
        },

        play() {
            // this.player.togglePlay();
            this.remotePlayer.togglePlay()
        },
        next() {
            this.player.nextTrack();
        },
        prev() {
            this.player.nextTrack();
        },

        seek(position) {
            this.player.seek(position)
        },

        getCurrentState() {
            return this.player.getCurrentState()
        },
        onReady(state) {
            if (!state) {
                return
            }

            console.log('Ready with Device ID', state.device_id);
            this.idDevice = state.device_id
            this.axios.post('/device', {
                device: state.device_id,
            }).then((res) => {
                // console.log(res)
                this.axios.post('/state', {}).then((res) => {
                    console.log('/state', res.data)
                    this.$emit('state-change', res.data)
                })
                this.$emit('ready', this.idDevice)
                this.isReady = true
            })

        },
        onStateChange(s) {
            if (!s) {
                return
            }

            this.progress = s.position

            this.state = s
            // this.progressBar.position = s.position
            this.isPause = s.paused


            const idPlayback = s.playback_id ?? ''
            this.isActive = (idPlayback !== '')

            console.log('PlyaerController: player_state_changed', s);
            this.$emit('web-player-state-change', s)

            // this.axios.get('/track-info', {
            //     // device: device_id,
            // }).then((res) => {
            //     console.log(res.data)
            //     if (res.data) {
            //         this.item = res.data.item
            //         this.$emit('state-chage', s)
            //     }
            // })
        },

        requestToken(cb) {
            this.axios.post('/access-token', {}).then((res) => {
                this.token = res.data.token
                cb(res.data.token)
            }).catch(() => {
                cb()
            })
        },

    },

    data() {
        const axios = window.axios.create({
            responseType: 'json',
        });

        const remotePlayer = new RemotePlayer(axios)
        // remotePlayer.foo()

        return {
            axios,
            isReady: false,
            idDevice: null,
            player: null,
            isPause: true,
            item: null,
            state: null,
            isActive: false,
            remotePlayer,
        }
    },
}
</script>

<style lang="scss" scoped>
.ui-button {
    height: 42px;
    position: relative;
    padding: 8px 12px;
    margin: 4px;
    // font-size: 1.4em;
}

</style>
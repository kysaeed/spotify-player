<template>
    <div >
        <h1>{{isReady}}</h1>
        <button @click="prev" class="ui-button" >← P</button>
        <button @click="play" class="ui-button" >
            <span v-if="isPause">▶ Play</span>
            <span v-if="!isPause"> ‖ Pause</span>
        </button>
        <button @click="next" class="ui-button" >N →</button>

    </div>
</template>

<script>
export default {
    name: 'player-controll',
    props: {
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
                name: 'Laravel Web Player',
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
            player.addListener('player_state_changed', (arg) => {
                this.onStateChange(arg)
            });

            player.connect();
            return player
        },

        play() {
            this.player.togglePlay();
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


        onReady(state) {
            if (!state) {
                return
            }

            console.log('Ready with Device ID', state.device_id);
            this.idDevice = state.device_id
            this.axios.post('/device', {
                device: state.device_id,
            }).then((res) => {
                console.log(res)
            })

            this.isReady = true

        },
        onStateChange(s) {
            if (s) {
                this.progress = s.position

                this.state = s
                // this.progressBar.position = s.position
                this.isPause = s.paused

                console.log('PlyaerController: player_state_changed', s);
                this.$emit('state-change', s)

                // this.axios.get('/track-info', {
                //     // device: device_id,
                // }).then((res) => {
                //     console.log(res.data)
                //     if (res.data) {
                //         this.item = res.data.item
                //         this.$emit('state-chage', s)
                //     }
                // })
            }
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

        return {
            axios,
            isReady: false,
            idDevice: null,
            player: null,
            isPause: true,
            item: null,
            state: null,
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
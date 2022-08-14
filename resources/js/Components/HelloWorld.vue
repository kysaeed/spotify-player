<template>
    <div class="hello-box">
        <div class="hello-box-child">
            <!-- token: {{token}} -->
            <h1>Laravel Web Player</h1>
        </div>

        <div class="hello-box-child">
            <player-device-list
                :device="device"
            />
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
        </div>
        <player-controll
            ref="playerConroll"
            :title="'Laravel Web Player'"
            @ready="onReady"
            @state-change="onStateChange"
            @web-player-state-change="onWebPlayerStateChange"
        />
        <div class="hello-box-child">

            <!-- <div v-if="state"> -->
                <player-progress-bar
                    v-model="progressBar.position"
                    :duration="duration"
                    :is-pause="isPause"
                    :is-enabled="!!item"
                    @update="onSeekByBar"
                />

            <!-- </div> -->
        </div>
    </div>
</template>

<script>
import PlayerProgressBar from './Player/PlayerProgressBar.vue'
import PlayerControll from './Player/PlayerControll.vue'
import PlayerDeviceList from './Player/PlayerDeviceList.vue'


export default {
    name: 'hello-world',

    components: {
        PlayerControll,
        PlayerProgressBar,
        PlayerDeviceList,
    },

    methods: {
        onReady(idWebPlayerDevice) {
            this.idWebPlayerDevice = idWebPlayerDevice
        },
        onStateChange(state) {
            console.log('onStateChange ****', state)
            if (!state) {
                return
            }
            if (state.device) {
                this.device = {
                    code: state.device.id,
                    label: state.device.name,
                    type: state.device.type,
                }
            }
        },
        onWebPlayerStateChange(s) {
            // console.log(s)
            this.progress = s.position

            this.state = s
            this.progressBar.position = s.position
            this.isPause = s.paused
            this.duration = s.duration

            console.log('player_state_changed', s);

            this.axios.get('/track-info', {
                // device: device_id,
            }).then((res) => {
                console.log(res.data)
                if (res.data) {
                    this.item = res.data.item
                    // this.$emit('state-chage', s)
                }
            })
        },

        onSeekByBar(t) {
            this.progress = t
            console.log('onSeekByBar ******', this.progress)
            this.$refs.playerConroll.seek(this.progress)
        },

        onWindowActive() {
            this.$refs.playerConroll.getCurrentState().then((s) => {
                console.log('#onWindowActive', s)
                this.progressBar.position = s.position
            })
        },

    },
    mounted() {

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
            position: 0,
        }


        return {
            progress: 0,
            axios,
            item: null,
            state: null,
            player: null,
            isPause: true,
            progressBar,
            token: null,
            duration: 0,
            idWebPlayerDevice: null,
            device: null,

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
<template>
    <div
        class="progress-bar"
        @mousedown.left.prevent="onMouseDown"
        @mouseup.left.prevent="onMouseUp"
        @mousemove="onMouseMove"
    >
        <div>
            {{ progress }} / {{this.trackInfo.duration}}
        </div>
        <div
            class="progress-bar-handle"
            :style="handleStyle"
        ></div>
    </div>
</template>

<script>
import anime from 'animejs'

export default {
    props: {
        progress: {
            type: Number,
            default: 0,
        },
        trackInfo: {
            type: Object,
            default: () => ({}),
        },
        isPause: {
            type: Boolean,
            default: false,
        },

    },

    name: 'player-progress-bar',

    computed: {
        handleStyle() {
            if (!this.trackInfo) {
                return {}
            }

            if (!this.trackInfo.duration) {
                return {}
            }

            let left = this.progress / this.trackInfo.duration * 100
            if (left > 100) {
                left = 100
            }
            return {
                width: `${left}%`
            }
        },
    },

    methods: {
        onMouseDown() {
            console.log('xxxs')
            this.isGrab = true
        },
        onMouseUp() {
            this.isGrab = false
        },

        onMouseMove(e) {
            if (!this.isGrab) {
                return
            }

            // console.log("touch start:%d,%d", e.offsetX, e.offsetY);
        },


    },

    data() {
        this.trackInfo

        return {
            isGrab: false,
            duration: 0,
        }
    },
}
</script>

<style lang="scss" scoped>
.progress-bar {
    position: relative;
    width: 100%;
    border: 1px solid black;
    border-radius: 4px;

    &-handle {
        position: absolute;
        // left: 0px;
        top: 0px;
        height: 100%;
        width: 0%;
        background: #0000e09f;
        border-radius: 4px;
    }
}
</style>
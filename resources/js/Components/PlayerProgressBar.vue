<template>
    <div
        ref="progress"
        class="progress-bar"
        @mousedown.left.prevent="onMouseDown"
        @mouseup.left="onMouseUp"
        @mousemove="onMouseMove"
        draggable
    >
        <div>
            {{ progress }} / {{this.trackInfo.duration}}
        </div>

        <div
            class="progress-bar-fill"
            :style="barStyle"
        ></div>

        <div
            v-if="isGrab"
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
        barStyle() {
            if (!this.trackInfo) {
                return {}
            }

            if (!this.trackInfo.duration) {
                return {}
            }

            let width = this.progress / this.trackInfo.duration * 100
            if (width > 100) {
                width = 100
            }
            return {
                width: `${width}%`
            }
        },
        handleStyle() {
            if (!this.trackInfo) {
                return {}
            }

            if (!this.trackInfo.duration) {
                return {}
            }
            let left = this.to
            if (left > 100) {
                left = 100
            }
            return {
                width: `${left}%`
            }
        },
    },

    watch: {
        trackInfo() {
            this.isGrab = false
        },
    },

    mounted() {
        // document.addEventListener('mouseup', this.onMouseUp)
        // document.addEventListener('mousemove', this.onMouseMove)
    },

    methods: {
        onMouseDown(e) {
            console.log('xxxs', e)
            this.isGrab = true

            this.currentPoint = {
                x: e.offsetX,
                y: e.offsetY,
            }
            this.upd(this.currentPoint.x)
        },
        onMouseUp() {
            if (!this.isGrab) {
                return
            }

            this.isGrab = false
            if (this.trackInfo) {
                if (this.trackInfo.duration) {

                    const d = this.trackInfo.duration * (this.to / 100)
                    this.$emit('seek', d)
                }
            }
        },

        onMouseMove(e) {
            if (!this.isGrab) {
                return
            }

            this.currentPoint = {
                x: e.offsetX,
                y: e.offsetY,
            }
            // console.log("touch start:%d,%d", e.offsetX, e.offsetY);

            this.upd(this.currentPoint.x)
        },

        upd(x) {
            const r = this.$refs.progress.getBoundingClientRect()
            // console.log(x, r)
            this.to = x / r.width * 100
        },

    },

    data() {
        this.trackInfo

        return {
            isGrab: false,
            duration: 0,
            currentPoint: null,
            to: 0,
        }
    },
}
</script>

<style lang="scss" scoped>
.progress-bar {
    position: relative;
    width: 100%;
    height: 30px;
    border: 1px solid black;
    border-radius: 4px;
    cursor: ew-resize;
    &:hover {
        background-color: #e9e9f6;
    }

    &-fill {
        position: absolute;
        // left: 0px;
        top: 0px;
        height: 100%;
        width: 0%;
        background: #2626ff9f;
        border-radius: 4px;
    }
    &-handle {
        position: absolute;
        left: 0px;
        top: 0px;
        height: 100%;
        width: 30px;
        background: #ff00004f;
        border-radius: 4px;
    }
}
</style>
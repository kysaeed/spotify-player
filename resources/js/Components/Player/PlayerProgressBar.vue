<template>
    <div
        ref="progress"
        class="progress-bar"
        @mousedown.left.prevent="onMouseDown"
    >
        <div
            ref="bar"
            class="progress-bar-fill"
        >
        </div>

        <div
            v-if="isGrab"
            class="progress-bar-handle"
            :style="handleStyle"
        ></div>

        <div
            v-if="isEnabled"
            class="progress-bar-time"
        >
            <div>{{ toTime(this.time.ms) }}</div>
            <div>{{ toTime(this.duration) }}</div>
        </div>
    </div>
</template>

<script>
import anime from 'animejs'

export default {
    name: 'player-progress-bar',
    props: {
        modelValue: {
            type: Number,
            default: 0,
        },
        duration: {
            type: Number,
            default: null,
        },
        isPause: {
            type: Boolean,
            default: false,
        },
        isEnabled: {
            type: Boolean,
            default: true,
        },
    },

    watch: {
        modelValue(value) {
            this.animation.pause()
            this.animation.seek(value)
            if (!this.isPause) {
                this.animation.play()
            }
        },
        duration() {
            this.createAnimation()
        },
        isPause(value) {
            if (value) {
                this.animation.pause()
            } else {
                this.animation.play()
            }
        },
    },

    computed: {
        handleStyle() {
            if (!this.duration) {
                return {
                    display: 'none',
                }
            }
            let left = this.to
            if (left > 100) {
                left = 100
            }
            return {
                width: `${left}%`
            }
        },
        progressTime() {
            return 'PT'
        },
        durationTime() {
            return 'DT'
        },
    },

    mounted() {
        anime.suspendWhenDocumentHidden = false

        // window.addEventListener('focus', this.adjustPorgress)

        document.addEventListener('mousemove', this.onMouseMove)
        document.addEventListener('mouseup', this.onMouseUp)

        this.createAnimation()
    },
    beforeDestroy() {
        document.removeEventListener('mouseup', this.onMouseUp)
        document.removeEventListener('mousemove', this.onMouseMove)
    },

    methods: {
        createAnimation() {
            if (this.animation) {
                this.animation.remove([this.$refs.bar, this.time])
            }
            this.time = { ms: 0, }

            this.animation = anime({
                targets: [this.$refs.bar, this.time],
                width: [`0%`, '100%'],
                ms: this.duration,
                // round: 1,
                duration: this.duration,
                easing: 'linear',
                autoplay: false,
            })
            this.animation.seek(this.modelValue)
            if (!this.isPause) {
                this.animation.play()
            }
        },

        onMouseDown(e) {
            if (!this.isEnabled) {
                this.isGrab = false
                return
            }

            this.isGrab = true

            const r = this.$refs.progress.getBoundingClientRect()
            this.currentPoint = {
                x: e.pageX - r.x + window.pageXOffset,
                y: e.pageY - r.y + window.pageYOffset,
            }

            this.setProgressPosition(this.currentPoint.x)
        },
        onMouseUp(e) {
            if (!this.isGrab) {
                return
            }

            if (!this.isEnabled) {
                this.isGrab = false
                return
            }

            e.preventDefault()
            this.isGrab = false
            const d = this.duration * (this.to / 100)
            this.$emit('update', d)
        },

        onMouseMove(e) {
            if (!this.isGrab) {
                return
            }

            if (!this.isEnabled) {
                this.isGrab = false
                return
            }

            e.preventDefault()
            const r = this.$refs.progress.getBoundingClientRect()
            this.currentPoint = {
                x: e.pageX - r.x + window.pageXOffset,
                y: e.pageY - r.y + window.pageYOffset,
            }
            this.setProgressPosition(this.currentPoint.x)
        },

        setProgressPosition(x) {
            const r = this.$refs.progress.getBoundingClientRect()
            this.to = x / r.width * 100
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

    data() {

        return {
            time: {
                ms: 0,
            },
            animation: null,
            isGrab: false,
            currentPoint: null,
            to: 0,
        }
    },
}
</script>

<style lang="scss" scoped>
* {
    box-sizing: border-box;
}
.progress-bar {
    position: relative;
    width: 100%;
    height: 30px;
    border: 1px solid black;
    border-radius: 4px;
    z-index: 1;
    cursor: ew-resize;
    &:hover {
        background-color: #e9e9f6;
    }

    &-time {
        position: absolute;
        top: 0px;
        height: 100%;
        width: 100%;
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 1px 8px;
        z-index: 0;
        // pointer-events: none;

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
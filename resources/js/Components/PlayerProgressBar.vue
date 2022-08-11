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
            {{ progress }} / {{this.duration}}
        </div>

        <div
            ref="bar"
            class="progress-bar-fill"
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
    name: 'player-progress-bar',
    props: {
        progress: {
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
    },

    watch: {
        progress(value) {
            console.log('!!!', value)
        },
        duration(value) {
            this.setDuration(value)
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
        barStyle() {

            if (!this.duration) {
                return {}
            }

            let width = 0
            if (this.duration) {
                width = this.progress / this.duration * 100
            }

            if (width > 100) {
                width = 100
            }
            return {
                width: `${width}%`
            }
        },
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
    },

    mounted() {
        anime.suspendWhenDocumentHidden = false

        // window.addEventListener('focus', this.adjustPorgress)

        // document.addEventListener('mouseup', this.onMouseUp)
        // document.addEventListener('mousemove', this.onMouseMove)
        this.animation = anime({
            targets: this.$refs.bar,
            width: [`0%`, '100%'],
            // round: 1,
            duration: this.duration,
            easing: 'linear',
        })
        this.seek(this.progress)
    },

    methods: {
        onMouseDown(e) {
            console.log('xxxs', e)
            this.isGrab = true

            this.currentPoint = {
                x: e.offsetX,
                y: e.offsetY,
            }
            this.setProgressPosition(this.currentPoint.x)
        },
        onMouseUp() {
            if (!this.isGrab) {
                return
            }

            this.isGrab = false
            if (this.duration) {
                const d = this.duration * (this.to / 100)

                // todo: メンバのanimeを更新
                this.seek(d)
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
            this.setProgressPosition(this.currentPoint.x)
        },

        setProgressPosition(x) {
            const r = this.$refs.progress.getBoundingClientRect()
            this.to = x / r.width * 100
        },
        setDuration() {
            this.animation.remove(this.$refs.bar)
            this.animation = anime({
                targets: this.$refs.bar,
                width: [`0%`, '100%'],
                // round: 1,
                duration: this.duration,
                easing: 'linear',
            })

            this.seek(this.progress)
        },

        seek(position) {
            this.animation.pause()
            this.animation.seek(position)
            this.animation.play()
            this.$emit('seek', position)
        },
    },

    data() {

        return {
            animation: null,
            isGrab: false,
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
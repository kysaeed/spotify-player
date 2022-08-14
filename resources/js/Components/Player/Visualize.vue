<template>
    <div class="main-container">
        <template v-if="animation">
            tl: {{ animation.duration }} ({{duration}})
        </template>

        <div>{{ position }} : {{ isPause }}</div>
        <div class="frame">
            <div ref="block" class="block">
                +
            </div>
        </div>
    </div>
</template>

<script>
import anime from 'animejs'

export default {
    name: 'visuzlize',
    props: {
        position: {
            type: Number,
            default: 0,
        },
        bars: {
            type: Array,
            default: () => [],
        },
        isPause: {
            type: Boolean,
            default: false,
        },
    },

    watch: {
        position(value) {
            if (this.animation) {
                this.animation.pause()
                this.animation.seek(value)
                if (!this.isPause) {
                    this.animation.play()
                }
            }
        },
        bars(value) {
            this.setBars(value)
        },
        isPause(value) {
            if (this.animation) {
                if (value) {
                    this.animation.pause()
                } else {
                    this.animation.play()
                }
            }
        },
    },

    mounted() {
        anime.suspendWhenDocumentHidden = false


    },
    methods: {
        setBars(bars) {
            if (this.animation) {
                this.animation.remove(this.$refs.block)
            }

            this.animation = anime.timeline({
                targets: this.$refs.block,
                autoplay: false,
            })

            let last = null
            bars.forEach((b, i) => {
                last = b
                const move = (i % 2) * 250
                if (i === 0) {
                    this.animation.add({
                        translateX: move,
                        duration: b.duration * 1000,
                    }, b.start)
                } else {
                    this.animation.add({
                        translateX: move,
                        duration: b.duration * 1000,
                    })
                }
            })

            if (last) {
                this.duration = last.start + last.duration
            }

            this.animation.seek(this.position)


            if (!this.isPause) {
                this.animation.play()
            }

        },
    },

    data() {
        return {
            animation: null,
            duration: 0,
        }
    },
}
</script>

<style lang="scss" scoped>
.main-container {
    position: relative;
    width: 100%;
}
.frame {
    width: 100%;
    position: relative;
    padding: 40px;
    box-sizing: border-box;
}
.block {

    position: relative;
    width: 80px;
    height: 80px;
    background: #808080;
    display: flex;
    flex-direction: row;
    justify-content: center;
    align-items: center;
}
</style>
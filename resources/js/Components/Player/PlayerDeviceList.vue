<template>
    <div>
        <vue-select
            v-model="selectedDevice"
            :loading="isLoading"
            :options="options"
            :searchable="false"
            :clearable="false"
            @open="onOpen"
            @option:selected="onSelect"
        >
            <template v-slot:no-options="{ search, searching }">
                なし
            </template>

            <template #option="{ label }">
                <h3 style="margin: 0">{{ label }}</h3>
            </template>

        </vue-select>
    </div>
</template>

<script>
import {VueSelect} from 'vue-select'
import 'vue-select/dist/vue-select.css'

export default {
    props: {
        device: {
            type: Object,
            defualt: null,
        },
    },
    name: 'player-device-list',
    components: {
        VueSelect,
    },
    watch: {
        device(value) {
            this.selectedDevice = _.cloneDeep(value)
        },
    },

    mounted() {

    },
    methods: {
        onOpen() {
            this.isLoading = true

            this.axios.post('/device/list', {
                device: this.idDevice,
            }).then((res) => {
                console.log(res)

                if (res.data.devices) {
                    this.options = []
                    res.data.devices.forEach((d) => {
                        this.options.push({
                            label: d.name,
                            type: d.type,
                            code: d.id,
                        })

                    })
                }

                // this.startInterval()
                this.isLoading = false
            }).catch(() => {
                this.isLoading = false
            })


            // window.setTimeout(() => {
            //     this.isLoading = false
            //     this.options = [
            //         {label: 'ここに再生デバイス選択を表示', value: '0'}
            //     ]
            // }, 2000)

        },

        onSelect(e) {
console.log('selected', this.selectedDevice.code)

            this.axios.post('/device', {
                device: this.selectedDevice.code,
            }).then((res) => {
                // console.log(res)
                this.axios.post('/state', {}).then((res) => {
                    console.log('/state', res.data)
                })
            })
        },
    },
    data() {
        const axios = window.axios.create({
            responseType: 'json',
        });

        return {
            isLoading: false,
            selectedDevice: _.cloneDeep(this.device),
            options: [],
            axios,
        }
    },
}
</script>

<style lang="scss" scoped>

</style>
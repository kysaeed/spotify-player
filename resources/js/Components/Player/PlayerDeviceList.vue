<template>
    <div>
        id: {{idDevice}} / {{selectedDeviceId}}
        <vue-select
            v-model="selectedDeviceId"
            :loading="isLoading"
            :options="options"
            :searchable="false"
            :clearable="false"
            @open="onOpen"
        >
            <template v-slot:no-options="{ search, searching }">
                {{search}}/{{searching}}
            </template>
        </vue-select>
    </div>
</template>

<script>
import {VueSelect} from 'vue-select'
import 'vue-select/dist/vue-select.css'

export default {
    props: {
        idDevice: {
            type: String,
            defualt: null,
        },
    },
    name: 'player-device-list',
    components: {
        VueSelect,
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
                            value: d.id,
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
    },
    data() {
        const axios = window.axios.create({
            responseType: 'json',
        });

        return {
            isLoading: false,
            selectedDeviceId: this.idDevice,
            options: [],
            axios,
        }
    },
}
</script>

<style lang="scss" scoped>

</style>
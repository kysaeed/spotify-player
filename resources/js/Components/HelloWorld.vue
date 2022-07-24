<template>
    <div class="hello-box">
        <h1>やあ、世界！</h1>
        <div>hello world</div>
        <h2>カウンタ：{{ a }}</h2>
        <div>
            <button @click="add">add!</button>
        </div>
    </div>
</template>

<script>
export default {
    name: 'hello-world',

    methods: {
        add() {
            this.a++
        },
    },
    data() {
        return {
            a: 0,
        }
    },
}
</script>

<style land="scss" scoped>
.hello-box {
    position: relative;
    margin: 20px;
    padding: 10px;
    border: 1px solid #101010;
    border-radius: 10px;
}
</style>
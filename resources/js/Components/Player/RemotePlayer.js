
class RemotePlayer {
    constructor(axios) {
        this.axios = axios;
    }

    togglePlay() {
        this.axios.post('player/play')
    }

    next() {}
    prev() {}
    seek(position) {}


    getCurrentState() {}

}

export default RemotePlayer;
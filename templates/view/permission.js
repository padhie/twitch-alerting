function getUserMedia(constraints) {
    console.log(constraints);
    console.log(navigator.mediaDevices);

    // if Promise-based API is available, use it
    if (navigator.mediaDevices) {
        return navigator.mediaDevices.getUserMedia(constraints);
    }

    // otherwise try falling back to old, possibly prefixed API...
    let legacyApi = navigator.getUserMedia
        || navigator.webkitGetUserMedia
        || navigator.mozGetUserMedia
        || navigator.msGetUserMedia;

    if (legacyApi) {
        // ...and promisify it
        return new Promise(function (resolve, reject) {
            legacyApi.bind(navigator)(constraints, resolve, reject);
        });
    }
}
import axios from "axios";

export const readDevices = (data = null) => {
    let request = axios({
        headers: { "Accept": "application/json" },
        method: "post", data: data, url: window.origin + '/biometric/devices/read'
    });
    return Promise.resolve(request);
}

export const createDevices = (data = null) => {
    let request = axios({
        headers: { "Accept": "application/json" },
        method: "post", data: data, url: window.origin + '/biometric/devices/create'
    });
    return Promise.resolve(request);
}

export const updateDevices = (data = null) => {
    let request = axios({
        headers: { "Accept": "application/json" },
        method: "post", data: data, url: window.origin + '/biometric/devices/update'
    });
    return Promise.resolve(request);
}

export const checkDevices = (data = null, id) => {
    let request = axios({
        headers: { "Accept": "application/json" },
        method: "post", data: data, url: window.origin + '/biometric/devices/' + id + '/check'
    });
    return Promise.resolve(request);
}
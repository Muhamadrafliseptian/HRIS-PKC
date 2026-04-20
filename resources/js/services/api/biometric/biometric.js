import axios from "axios";

export const readUsers = (data = null) => {
    let request = axios({
        headers: { "Accept": "application/json" },
        method: "post", data: data, url: window.origin + '/biometric/users/read'
    });
    return Promise.resolve(request);
}

export const createUsers = (data = null) => {
    let request = axios({
        headers: { "Accept": "application/json" },
        method: "post", data: data, url: window.origin + '/biometric/users/create'
    });
    return Promise.resolve(request);
}

export const syncUsers = (data = null) => {
    let request = axios({
        headers: { "Accept": "application/json" },
        method: "post", data: data, url: window.origin + '/biometric/users/sync'
    });
    return Promise.resolve(request);
}

export const destroyUsers = (data = null) => {
    let request = axios({
        headers: { "Accept": "application/json" },
        method: "post", data: data, url: window.origin + '/biometric/users/destroy'
    });
    return Promise.resolve(request);
}

export const transferUsers = (data = null) => {
    let request = axios({
        headers: { "Accept": "application/json" },
        method: "post", data: data, url: window.origin + '/biometric/users/transfer'
    });
    return Promise.resolve(request);
}
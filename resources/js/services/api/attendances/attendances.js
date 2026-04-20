import axios from "axios";

export const pullAttendances = (data = null) => {
    let request = axios({
        headers: { "Accept": "application/json" },
        method: "post", data: data, url: window.origin + '/attendance/pull'
    });
    return Promise.resolve(request);
}

export const readAttendances = (data = null) => {
    let request = axios({
        headers: { "Accept": "application/json" },
        method: "post", data: data, url: window.origin + '/attendance/read'
    });
    return Promise.resolve(request);
}

export const checkPullStatus = (data = null) => {
    let request = axios({
        headers: { "Accept": "application/json" },
        method: "get", data: data, url: window.origin + '/attendance/pull/status'
    });
    return Promise.resolve(request);
}

export const storeAttendanceException = (data = null) => {
    let request = axios({
        headers: { "Accept": "application/json" },
        method: "post", data: data, url: window.origin + '/attendance/change-status'
    });
    return Promise.resolve(request);
}
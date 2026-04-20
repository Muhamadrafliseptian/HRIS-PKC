import axios from "axios";

export const readEmployees = (data = null) => {
    let request = axios({
        headers: { "Accept": "application/json" },
        method: "post", data: data, url: window.origin + '/employee/read'
    });
    return Promise.resolve(request);
}

export const createEmployee = (data = null) => {
    let request = axios({
        headers: { "Accept": "application/json" },
        method: "post", data: data, url: window.origin + '/employee/create'
    });
    return Promise.resolve(request);
}

export const importEmployee = (data = null) => {
    let request = axios({
        headers: { "Accept": "application/json" },
        method: "post", data: data, url: window.origin + '/employee/import'
    });
    return Promise.resolve(request);
}
import axios from "axios";

export const getMenu = (data = null) => {
    let request = axios({
        headers: { "Accept": "application/json" },
        method: "get", data: data, url: window.origin + '/get-menu'
    });
    return Promise.resolve(request);
}
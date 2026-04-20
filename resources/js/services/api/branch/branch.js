import axios from "axios";

export const readBranch = (data = null) => {
    let request = axios({
        headers: { "Accept": "application/json" },
        method: "post", data: data, url: window.origin + '/master/branch/read'
    });
    return Promise.resolve(request);
}
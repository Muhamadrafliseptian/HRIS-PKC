import axios from "axios";

export const readUsers = (data = null) => {
    let request = axios({
        headers: { Accept: "application/json" },
        method: "post",
        data: data,
        url: window.origin + "/setting/users/get-users",
    });
    return Promise.resolve(request);
};

export const changePermission = (data = null) => {
    let request = axios({
        headers: { Accept: "application/json" },
        method: "post",
        data: data,
        url: window.origin + "/setting/users/change-permission",
    });
    return Promise.resolve(request);
};
import axios from "axios";

export const readAssignShift = (data = null) => {
    let request = axios({
        headers: { Accept: "application/json" },
        method: "post",
        data: data,
        url: window.origin + "/manage/shift/assignment/read",
    });
    return Promise.resolve(request);
};

export const createAssignShift = (data = null) => {
    let request = axios({
        headers: { Accept: "application/json" },
        method: "post",
        data: data,
        url: window.origin + "/manage/shift/assignment/create",
    });
    return Promise.resolve(request);
};
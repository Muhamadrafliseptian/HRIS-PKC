import axios from "axios";

export const readMasterShifts = (data = null) => {
    let request = axios({
        headers: { Accept: "application/json" },
        method: "post",
        data: data,
        url: window.origin + "/manage/shift/master/read",
    });
    return Promise.resolve(request);
};

export const createMasterShifts = (data = null) => {
    let request = axios({
        headers: { Accept: "application/json" },
        method: "post",
        data: data,
        url: window.origin + "/manage/shift/master/create",
    });
    return Promise.resolve(request);
};

export const updateMasterShifts = (data = null) => {
    let request = axios({
        headers: { Accept: "application/json" },
        method: "post",
        data: data,
        url: window.origin + "/shifts/master/master/update",
    });
    return Promise.resolve(request);
};

export const changeStatusMasterShifts = (data = null) => {
    let request = axios({
        headers: { Accept: "application/json" },
        method: "post",
        data: data,
        url: window.origin + "/shifts/master/change-status",
    });
    return Promise.resolve(request);
};

export const readJobtitles = (data = null) => {
    let request = axios({
        headers: { Accept: "application/json" },
        method: "post",
        data: data,
        url: window.origin + "/shifts/master/read-jobtitle",
    });
    return Promise.resolve(request);
};

export const readShiftDivision = (data = null) => {
    let request = axios({
        headers: { Accept: "application/json" },
        method: "post",
        data: data,
        url: window.origin + "/shifts/master/division",
    });
    return Promise.resolve(request);
};

export const readShiftEmployee = (data = null) => {
    let request = axios({
        headers: { Accept: "application/json" },
        method: "post",
        data: data,
        url: window.origin + "/shifts/employee/read",
    });
    return Promise.resolve(request);
};

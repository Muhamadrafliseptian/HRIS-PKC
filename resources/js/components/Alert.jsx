import { message, notification } from "antd";
import Swal from "sweetalert2";
const Toast = Swal.mixin({
    toast: true,
    position: 'top-end',
    showConfirmButton: false,
    timer: 3000,
    timerProgressBar: true
});

export const showSuccess = (msg) => {
    message.success(msg);
};

export const showError = (message, description = "") => {
    notification.error({
        message: message || "Terjadi Kesalahan",
        description: description,
        placement: "top",
    });
};

export const showInfo = (message, targetElement) => {
    Swal.fire({
        icon: 'info',
        title: 'Information',
        text: message,
        target: document.getElementById(targetElement),
        confirmButtonText: 'OK',
    });
};

import { showError } from "../components/Alert";

export const errorHandler = (e, setErrors = null, element = null) => {
    switch (e.response.data.status) {
        case 400:
            let errors = e.response.data.validation_errors;
            Object.keys(errors).forEach((key) => {
                setErrors((prev) => ({
                    ...prev,
                    [key]: errors[key],
                }));
            });
            break;
        default:
            showError(e.response.data.message, element);
            break;
    }
};

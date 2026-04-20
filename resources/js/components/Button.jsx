import { Button } from "antd";

export const PrimaryButton = ({
    label,
    disabled,
    loading = false,
    onClick,
    htmlType = "button",
    size = "default",
    block = false,
}) => {
    return (
        <>
            <Button
                className="primary-btn"
                disabled={disabled}
                loading={loading}
                type="primary"
                htmlType={htmlType}
                onClick={onClick}
                size={size}
                block={block}
            >{label}
            </Button>
        </>
    );
};

export const DangerButton = ({ label, disabled, loading, onClick, icon }) => {
    return (
        <>
            <Button
                disabled={disabled}
                loading={loading}
                type="primary"
                htmlType="button"
                onClick={onClick}
                danger={true}
            >{label}</Button>
        </>
    );
};

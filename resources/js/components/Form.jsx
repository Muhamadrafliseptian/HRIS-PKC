import React from "react";
import { Form, Input, Select, DatePicker, TimePicker, Upload } from "antd";
import { NumericFormat } from "react-number-format";
const { RangePicker } = DatePicker;
import { InboxOutlined } from "@ant-design/icons";

const { TextArea } = Input;
const { Dragger } = Upload;

export const FormText = ({
    label,
    type = "text",
    value,
    disabled = false,
    onChange,
    error,
    rules = [],
}) => {
    const isRequired = rules?.some((rule) => rule?.required);

    return (
        <Form.Item
            label={
                <span style={{ fontWeight: "600" }}>
                    {isRequired && <span style={{ color: "red" }}>* </span>}
                    {label}
                </span>
            }
            rules={rules}
        >
            <Input
                type={type}
                value={value}
                onChange={onChange}
                disabled={disabled}
            />
            <small style={{ color: "red" }}>{error}</small>
        </Form.Item>
    );
};

export const FormSelect = ({
    label,
    value = null,
    options,
    onChange,
    disabled = false,
    error = "",
    search = false,
    mode,
    isCleare = false,
    rules = [],
}) => {
    const isRequired = rules?.some((rule) => rule?.required);

    return (
        <Form.Item
            rules={rules}
            label={
                <span style={{ fontWeight: "600" }}>
                    {isRequired && <span style={{ color: "red" }}>* </span>}
                    {label}
                </span>
            }
        >
            <Select
                allowClear={isCleare}
                showSearch={search}
                options={options}
                value={value}
                onChange={onChange}
                disabled={disabled}
                optionFilterProp="label"
                mode={mode}
            />
            <small style={{ color: "red" }}>{error}</small>
        </Form.Item>
    );
};

export const FormUploadExcel = ({
    label,
    value = null,
    onChange,
    error = "",
    rules = [],
}) => {
    const isRequired = rules?.some((rule) => rule?.required);

    return (
        <Form.Item
            rules={rules}
            label={
                <span style={{ fontWeight: "600" }}>
                    {isRequired && <span style={{ color: "red" }}>* </span>}
                    {label}
                </span>
            }
        >
            <Dragger
                maxCount={1}
                fileList={value ? [value] : []}
                beforeUpload={(file) => {
                    onChange(file);
                    return false; // supaya tidak auto upload
                }}
                onRemove={() => onChange(null)}
                accept=".xlsx,.csv"
                style={{ padding: 20 }}
            >
                <p className="ant-upload-drag-icon">
                    <InboxOutlined style={{ fontSize: 45, color: "#1f4836" }} />
                </p>
                <p className="ant-upload-text">
                    Klik atau drag file ke area ini
                </p>
                <p className="ant-upload-hint">
                    Format yang didukung: .xlsx atau .csv
                </p>
            </Dragger>

            {error && <small style={{ color: "red" }}>{error}</small>}
        </Form.Item>
    );
};

export const FormNumber = ({
    label,
    value = null,
    onChange,
    disabled = false,
    error = "",
    prefix = "",
    negative = false,
    rules = [],
}) => {
    const isRequired = rules?.some((rule) => rule?.required);

    return (
        <Form.Item
            rules={rules}
            label={
                <span style={{ fontWeight: "600" }}>
                    {isRequired && <span style={{ color: "red" }}>* </span>}
                    {label}
                </span>
            }
        >
            <NumericFormat
                customInput={Input}
                allowNegative={negative}
                value={value}
                prefix={prefix}
                onValueChange={onChange}
                disabled={disabled}
            />
            <small style={{ color: "red" }}>{error}</small>
        </Form.Item>
    );
};

export const FormSearch = ({
    label,
    type = "text",
    value,
    disabled = false,
    onChange,
    onSearch,
    error,
}) => {
    return (
        <Form.Item
            label={
                <h5 style={{ fontWeight: "600", width: "100%" }}>{label}</h5>
            }
        >
            <Input.Search
                type={type}
                value={value}
                onChange={onChange}
                disabled={disabled}
                onSearch={onSearch}
            />
            <small style={{ color: "red" }}>{error}</small>
        </Form.Item>
    );
};

export const FormPassword = ({
    label,
    value,
    disabled = false,
    onChange,
    error,
    autoComplete = "",
    rules = [],
}) => {
    const isRequired = rules?.some((rule) => rule?.required);

    return (
        <Form.Item
            label={
                <span style={{ fontWeight: "600" }}>
                    {isRequired && <span style={{ color: "red" }}>* </span>}
                    {label}
                </span>
            }
            rules={rules}
        >
            <Input.Password
                autoComplete={autoComplete}
                value={value}
                onChange={onChange}
                disabled={disabled}
            />
            <small style={{ color: "red" }}>{error}</small>
        </Form.Item>
    );
};

export const FormCurrency = ({
    label,
    value = null,
    onChange,
    disabled = false,
    error = "",
    prefix = "",
    rules = [],
}) => {
    const isRequired = rules?.some((rule) => rule?.required);

    return (
        <Form.Item
            label={
                <span style={{ fontWeight: "600" }}>
                    {isRequired && <span style={{ color: "red" }}>* </span>}
                    {label}
                </span>
            }
            rules={rules}
        >
            <NumericFormat
                customInput={Input}
                thousandSeparator="."
                decimalSeparator=","
                allowNegative={false}
                value={value}
                prefix={prefix}
                onValueChange={onChange}
                disabled={disabled}
                decimalScale={0}
            />
            <small style={{ color: "red" }}>{error}</small>
        </Form.Item>
    );
};

export const FormTextArea = ({
    label,
    value,
    onChange,
    disabled = false,
    height = "100px",
    error = "",
    rules = [],
}) => {
    const isRequired = rules?.some((rule) => rule?.required);
    return (
        <Form.Item
            label={
                <span style={{ fontWeight: "600" }}>
                    {isRequired && <span style={{ color: "red" }}>* </span>}
                    {label}
                </span>
            }
            rules={rules}
        >
            <TextArea
                value={value}
                onChange={onChange}
                disabled={disabled}
                style={{ height: height, resize: "none" }}
            />
            <small style={{ color: "red" }}>{error}</small>
        </Form.Item>
    );
};

export const FormDatePicker = ({
    label = "",
    value,
    onChange,
    disabled = false,
    error = "",
    max_date = null,
    rules = [],
}) => {
    const isRequired = rules?.some((rule) => rule?.required);
    return (
        <>
            <Form.Item
                rules={rules}
                label={
                    <span style={{ fontWeight: "600" }}>
                        {isRequired && <span style={{ color: "red" }}>* </span>}
                        {label}
                    </span>
                }
            >
                <DatePicker
                    style={{ width: "100%" }}
                    format={"DD-MM-YYYY"}
                    disabledDate={max_date}
                    value={value}
                    disabled={disabled}
                    onChange={onChange}
                />
                <small style={{ color: "red" }}>{error}</small>
            </Form.Item>
        </>
    );
};

export const FormDateRangePicker = ({
    label = "",
    value,
    onChange,
    disabled = false,
    disabledDate = null,
    error = null,
    rules = [],
}) => {
    const isRequired = rules.some((rule) => rule?.required);
    return (
        <>
            <Form.Item
                label={
                    <span style={{ fontWeight: "600" }}>
                        {isRequired && <span style={{ color: "red" }}>* </span>}
                        {label}
                    </span>
                }
                rules={rules}
            >
                <RangePicker
                    disabledDate={disabledDate}
                    style={{ width: "100%" }}
                    format={"DD-MM-YYYY"}
                    value={value}
                    disabled={disabled}
                    onChange={onChange}
                    placement="topLeft"
                    getPopupContainer={(trigger) => trigger.parentNode}
                />

                {error?.start_date && (
                    <small style={{ color: "red" }}>{error.start_date}</small>
                )}
                {error?.end_date && (
                    <small style={{ color: "red" }}>{error.end_date}</small>
                )}
            </Form.Item>
        </>
    );
};

export const FormTimePicker = ({
    label = "",
    value,
    onChange,
    disabled = false,
    error = null,
    rules = [],
}) => {
    const isRequired = rules.some((rule) => rule?.required);
    return (
        <>
            <Form.Item
                label={
                    <span style={{ fontWeight: "600" }}>
                        {isRequired && <span style={{ color: "red" }}>* </span>}
                        {label}
                    </span>
                }
                rules={rules}
            >
                <TimePicker
                    style={{ width: "100%" }}
                    value={value}
                    disabled={disabled}
                    onChange={onChange}
                />

                <small style={{ color: "red" }}>{error}</small>
            </Form.Item>
        </>
    );
};

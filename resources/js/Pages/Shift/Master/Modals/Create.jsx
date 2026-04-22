import { showError, showSuccess } from "../../../../components/Alert";
import { DangerButton, PrimaryButton } from "../../../../components/Button";
import { FormSelect, FormText, FormTimePicker } from "../../../../components/Form";
import {
    createMasterShifts,
    readJobtitles,
} from "../../../../services/api/shift/shift";
import { usePage } from "@inertiajs/react";
import { Checkbox, Col, Form, List, Modal } from "antd";
import dayjs from "dayjs";
import React, { useEffect, useState } from "react";

function Create(props) {
    const pages = usePage().props;
    const [open, setOpen] = useState(false);
    const [loading, setLoading] = useState(false);
    const [utilities, setUtilities] = useState({
        branchs: [],
    });

    const [form, setForm] = useState({
        name: "",
        clock_in: "",
        clock_out: "",
        is_cross_day: "",
        is_default: "",
        is_active: "",
        branch: "",
        day: [],
        tolerance_before_in: "",
        tolerance_after_in: "",
        tolerance_before_out: "",
        tolerance_after_out: "",
    });

    const [errors, setErrors] = useState({
        name: "",
        clock_in: "",
        clock_out: "",
        is_cross_day: "",
        is_default: "",
        branch: "",
        is_active: "",
        day: [],
        tolerance_before_in: "",
        tolerance_after_in: "",
        tolerance_before_out: "",
        tolerance_after_out: "",
    });

    useEffect(() => {
        if (props.open) {
            setOpen(true);
            setUtilities({
                branchs: pages.branchs,
            });
        }
    }, [props]);

    const handleChangeForm = (field, value) => {
        setForm((prev) => ({
            ...prev,
            [field]: value ?? "",
        }));
    };

    const submit = async () => {
        try {
            let formData = new FormData();
            formData.append("branch", form.branch);
            formData.append("name", form.name);
            formData.append("days", form.day);
            formData.append(
                "tolerance_before_in",
                dayjs(form.tolerance_before_in).format("HH:mm")
            );
            formData.append(
                "tolerance_before_out",
                dayjs(form.tolerance_before_out).format("HH:mm")
            );
            formData.append(
                "tolerance_after_in",
                dayjs(form.tolerance_after_in).format("HH:mm")
            );
            formData.append(
                "tolerance_after_out",
                dayjs(form.tolerance_after_out).format("HH:mm")
            );
            if (form.clock_in != "") {
                formData.append(
                    "clock_in",
                    dayjs(form.clock_in).format("HH:mm")
                );
            }
            if (form.clock_out != "") {
                formData.append(
                    "clock_out",
                    dayjs(form.clock_out).format("HH:mm")
                );
            }
            formData.append("is_cross_day", form.is_cross_day == true ? 1 : 0);
            formData.append("is_default", form.is_default == true ? 1 : 0);
            setLoading(true);
            let response = await createMasterShifts(formData);

            setLoading(false);
            if (response.data.status) {
                showSuccess(response.data.message);
                props.handleUpdate();
                handleClose();
            } else {
                showError(response.data.message);
            }
        } catch (err) {
            setLoading(false);
            showError(err.response.data.message);
        }
    };

    const handleClose = () => {
        setForm({
            name: "",
            clock_in: "",
            clock_out: "",
            is_cross_day: "",
            is_default: "",
            is_active: "",
            branch: "",
            day: [],
        });
        setOpen(false);
        clearError();
        props.handleClose("create");
    };

    const clearError = () => {
        setErrors({
            name: "",
            clock_in: "",
            clock_out: "",
            is_cross_day: "",
            is_default: "",
            branch: "",
            is_active: "",
            day: [],
        });
    };
    return (
        <div>
            <Modal
                title="Create"
                centered={true}
                open={open}
                onCancel={handleClose}
                maskClosable={false}
                style={{ fontWeight: "600" }}
                footer={false}
                id="create"
            >
                <Form layout="vertical">
                    <FormText
                        label={"Nama Shift"}
                        value={form.name}
                        disabled={loading}
                        error={errors.name}
                        onChange={(e) =>
                            handleChangeForm("name", e.target.value)
                        }
                        rules={[{ required: true }]}
                    />
                    <FormSelect
                        label={"Branch"}
                        value={form.branch}
                        disabled={loading}
                        options={utilities.branchs}
                        error={errors.branch}
                        rules={[{ required: true }]}
                        onChange={(e) => handleChangeForm("branch", e)}
                    />

                    <FormTimePicker
                        label="Jam Masuk"
                        onChange={(e) => handleChangeForm("clock_in", e)}
                        value={form.clock_in}
                        error={errors.clock_in}
                        rules={[{ required: true }]}
                        disabled={loading}
                    />

                    <FormTimePicker
                        label="Jam Keluar"
                        onChange={(e) => handleChangeForm("clock_out", e)}
                        value={form.clock_out}
                        error={errors.clock_out}
                        rules={[{ required: true }]}
                        disabled={loading}
                    />

                    <div
                        style={{
                            background: "#f9fafb",
                            padding: "12px 16px",
                            borderRadius: "12px",
                            marginTop: 16,
                        }}
                    >
                        <h4
                            style={{
                                fontWeight: "600",
                                fontSize: "16px",
                                marginBottom: "12px",
                                borderBottom: "1px solid #e5e7eb",
                                paddingBottom: "4px",
                            }}
                        >
                            Pengaturan Toleransi Waktu
                        </h4>

                        <div
                            style={{
                                display: "grid",
                                gridTemplateColumns: "1fr 1fr",
                                gap: "12px 16px",
                            }}
                        >
                            <FormTimePicker
                                label="Absen Masuk Paling Awal"
                                onChange={(e) =>
                                    handleChangeForm("tolerance_before_in", e)
                                }
                                value={form.tolerance_before_in}
                                disabled={loading}
                            />
                            <FormTimePicker
                                label="Toleransi Terlambat"
                                onChange={(e) =>
                                    handleChangeForm("tolerance_after_in", e)
                                }
                                value={form.tolerance_after_in}
                                disabled={loading}
                            />
                            <FormTimePicker
                                label="Absen Pulang Lebih Awal"
                                onChange={(e) =>
                                    handleChangeForm("tolerance_before_out", e)
                                }
                                value={form.tolerance_before_out}
                                disabled={loading}
                            />
                            <FormTimePicker
                                label="Toleransi Setelah Jam Pulang"
                                onChange={(e) =>
                                    handleChangeForm("tolerance_after_out", e)
                                }
                                value={form.tolerance_after_out}
                                disabled={loading}
                            />
                        </div>
                    </div>

                    <List>
                        <List.Item>
                            <div
                                style={{
                                    display: "flex",
                                    justifyContent: "space-between",
                                    width: "100%",
                                }}
                            >
                                <span
                                    style={{
                                        fontWeight: "600",
                                        fontSize: "16px",
                                    }}
                                >
                                    Jam Kerja Default
                                </span>
                                <Checkbox
                                    checked={form.is_default}
                                    onChange={(e) =>
                                        handleChangeForm(
                                            "is_default",
                                            e.target.checked
                                        )
                                    }
                                />
                            </div>
                        </List.Item>

                        <List.Item>
                            <div
                                style={{
                                    display: "flex",
                                    justifyContent: "space-between",
                                    width: "100%",
                                }}
                            >
                                <span
                                    style={{
                                        fontWeight: "600",
                                        fontSize: "16px",
                                    }}
                                >
                                    Absen Beda Hari
                                </span>
                                <Checkbox
                                    checked={form.is_cross_day}
                                    onChange={(e) =>
                                        handleChangeForm(
                                            "is_cross_day",
                                            e.target.checked
                                        )
                                    }
                                />
                            </div>
                        </List.Item>
                    </List>

                    <Col
                        span={24}
                        style={{
                            display: "flex",
                            justifyContent: "end",
                            gap: 8,
                        }}
                    >
                        <DangerButton
                            type={"button"}
                            label={"Close"}
                            icon={"ti ti-x"}
                            onClick={handleClose}
                            disabled={loading}
                            isDanger={true}
                        />
                        <PrimaryButton
                            htmlType={"submit"}
                            label={"Submit"}
                            icon={"ti ti-device-floppy"}
                            onClick={submit}
                            disabled={loading}
                        />
                    </Col>
                </Form>
            </Modal>
        </div>
    );
}

export default Create;

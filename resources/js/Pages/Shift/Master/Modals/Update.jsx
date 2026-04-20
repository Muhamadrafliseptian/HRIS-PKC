import { showError, showSuccess } from "@/Components/Alert";
import { DangerButton, PrimaryButton } from "@/Components/Button";
import { FormSelect, FormText, FormTimePicker } from "@/Components/Form";
import { updateMasterShifts } from "../../../../services/api/shift/shift";
import { Modal, Checkbox, Col, Form, List } from "antd";
import { usePage } from "@inertiajs/react";
import React, { useEffect, useState } from "react";
import dayjs from "dayjs";

function Update(props) {
    const pages = usePage().props;
    const [open, setOpen] = useState(false);
    const [loading, setLoading] = useState(false);

    const [utilities, setUtilities] = useState({
        branchs: [],
    });

    const [form, setForm] = useState({
        id: "",
        name: "",
        clock_in: "",
        clock_out: "",
        is_cross_day: false,
        is_default: false,
        branch: "",
        day: [],
        tolerance_before_in: "",
        tolerance_after_in: "",
        tolerance_before_out: "",
        tolerance_after_out: "",
    });

    const [errors, setErrors] = useState({
        id: "",
        name: "",
        clock_in: "",
        clock_out: "",
        is_cross_day: false,
        is_default: false,
        branch: "",
        day: [],
        tolerance_before_in: "",
        tolerance_after_in: "",
        tolerance_before_out: "",
        tolerance_after_out: "",
    });

    useEffect(() => {
        if (props.open && props.data) {
            const d = props.data;
            setOpen(true);
            setUtilities({
                branchs: pages.branchs,
            });
            setForm({
                id: d.id,
                name: d.name || "",
                clock_in: d.clock_in ? dayjs(d.clock_in, "HH:mm:ss") : "",
                clock_out: d.clock_out ? dayjs(d.clock_out, "HH:mm:ss") : "",
                is_cross_day: d.is_cross_day === 1,
                is_default: d.is_default === 1,
                branch: d.branch.id || "",
                day: d.days || [],
                tolerance_before_in: d.tolerance_before_in
                    ? dayjs(d.tolerance_before_in, "HH:mm:ss")
                    : "",
                tolerance_after_in: d.tolerance_after_in
                    ? dayjs(d.tolerance_after_in, "HH:mm:ss")
                    : "",
                tolerance_before_out: d.tolerance_before_out
                    ? dayjs(d.tolerance_before_out, "HH:mm:ss")
                    : "",
                tolerance_after_out: d.tolerance_after_out
                    ? dayjs(d.tolerance_after_out, "HH:mm:ss")
                    : "",
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
            formData.append("id", form.id);
            formData.append("branch", form.branch);
            formData.append("name", form.name);
            formData.append("days", form.day);

            const timeFields = [
                "clock_in",
                "clock_out",
                "tolerance_before_in",
                "tolerance_after_in",
                "tolerance_before_out",
                "tolerance_after_out",
            ];
            timeFields.forEach((field) => {
                if (form[field]) {
                    formData.append(field, dayjs(form[field]).format("HH:mm"));
                }
            });

            formData.append("is_cross_day", form.is_cross_day ? 1 : 0);
            formData.append("is_default", form.is_default ? 1 : 0);

            setLoading(true);
            let response = await updateMasterShifts(formData);
            setLoading(false);

            if (response.data.status) {
                showSuccess(response.data.message);
                props.handleUpdate();
                handleClose();
            } else {
                showError(response.data.message);
                setErrors(response.data.message);
            }
        } catch (err) {
            setLoading(false);
            showError(err.response?.data?.message || "Terjadi kesalahan");
        }
    };

    const handleClose = () => {
        setForm({
            id: "",
            name: "",
            clock_in: "",
            clock_out: "",
            is_cross_day: false,
            is_default: false,
            branch: "",
            day: [],
            tolerance_before_in: "",
            tolerance_after_in: "",
            tolerance_before_out: "",
            tolerance_after_out: "",
        });
        setOpen(false);
        props.handleClose("update");
    };

    return (
        <Modal
            title="Update Shift"
            centered
            open={open}
            onCancel={handleClose}
            maskClosable={false}
            footer={false}
            style={{ fontWeight: "600" }}
            width={600}
        >
            <Form layout="vertical">
                <FormText
                    label={"Nama Shift"}
                    value={form.name}
                    disabled={loading}
                    onChange={(e) => handleChangeForm("name", e.target.value)}
                    error={errors.name}
                />
                <FormSelect
                    label={"Branch"}
                    value={form.branch}
                    disabled={loading}
                    options={utilities.branchs}
                    onChange={(e) => handleChangeForm("branch", e)}
                    error={errors.branch}
                />

                <FormTimePicker
                    label="Jam Masuk"
                    onChange={(e) => handleChangeForm("clock_in", e)}
                    value={form.clock_in}
                    disabled={loading}
                    error={errors.clock_in}
                />
                <FormTimePicker
                    label="Jam Keluar"
                    onChange={(e) => handleChangeForm("clock_out", e)}
                    value={form.clock_out}
                    disabled={loading}
                    error={errors.clock_out}
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

                {/* Checkbox section */}
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

                {/* Buttons */}
                <Col
                    span={24}
                    style={{
                        display: "flex",
                        justifyContent: "end",
                        gap: 8,
                    }}
                >
                    <DangerButton
                        type="button"
                        label="Close"
                        icon="ti ti-x"
                        onClick={handleClose}
                        disabled={loading}
                        isDanger={true}
                    />
                    <PrimaryButton
                        htmlType="submit"
                        label="Update"
                        icon="ti ti-device-floppy"
                        onClick={submit}
                        disabled={loading}
                    />
                </Col>
            </Form>
        </Modal>
    );
}

export default Update;

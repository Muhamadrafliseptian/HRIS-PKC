import { Col, Form, Modal, Row } from 'antd'
import React, { useEffect, useState } from 'react'
import { FormSelect, FormText, FormUploadExcel } from '../../../components/Form'
import { useResponsive } from '../../../Helpers/ResponsiveHelpers';
import { usePage } from '@inertiajs/react';
import { createDevices } from '../../../services/api/devices/devices';
import { PrimaryButton, DangerButton } from '../../../components/Button';
import { LoadingComponent } from '../../../components/Loading';
import { errorHandler } from '../../../components/Handler';
import { showSuccess } from '../../../components/Alert';
import { importEmployee } from '../../../services/api/employee/employee';

export default function Import(props) {
    const [open, setOpen] = useState(false);
    const [loading, setLoading] = useState(false);
    const [utils, setUtils] = useState({
        branchs: [],
        categories: [],
        services: [],
    });
    const [form, setForm] = useState({
        branch: "",
        service: "",
        category: "",
        file: ''
    });
    const [error, setError] = useState({
        branch: "",
        category: "",
        service: "",
        file: ''
    });
    const pages = usePage().props
    const { isDesktop, isMobile, isTablet } = useResponsive()
    useEffect(() => {
        if (props.open) {
            setOpen(true);
            setUtils({
                branchs: pages.branchs,
                categories: pages.categories,
                services: pages.services,
            });
        }
    }, [props]);

    const handleClose = () => {
        setOpen(false);
        setForm({
            branch: "",
            category: "",
            service: "",
        })
        props.handleClose("import");
    };

    const handleChangeForm = (field, value) => {
        setForm((prev) => ({
            ...prev,
            [field]: value,
        }));
    };

    const Submit = async () => {
        try {
            let formData = new FormData();
            formData.append("file", form.file);
            formData.append("branch", form.branch);
            formData.append("employee_status", form.category);
            formData.append("employee_services", form.service);

            setLoading(true);
            let response = await importEmployee(formData);
            setLoading(false);

            if (response.data.status) {
                showSuccess(response.data.message)
                props.handleUpdate();
                handleClose();
            }
        } catch (e) {
            setLoading(false);
            errorHandler(e, setError);
        }
    };

    return (
        <div>
            <Modal
                title="Import Employees"
                centered={true}
                width={isMobile || isTablet ? "100%" : "60%"}
                onClose={() => { }}
                open={open}
                onCancel={handleClose}
                style={{ fontWeight: "600" }}
                footer={false}
                id="create"
            >
                <Form layout='vertical'>
                    <Row gutter={12}>
                        <Col span={24}>
                            <FormSelect label={"Employee Services"}
                                options={utils.services}
                                rules={[{ required: true }]}
                                value={form.service}
                                onChange={(e) =>
                                    handleChangeForm(
                                        "service",
                                        e,
                                    )
                                }
                                disabled={loading}
                                search={true}
                                error={error.category}
                            />
                        </Col>
                        <Col span={24}>
                            <FormSelect label={"Employee Status"}
                                options={utils.categories}
                                rules={[{ required: true }]}
                                value={form.category}
                                onChange={(e) =>
                                    handleChangeForm(
                                        "category",
                                        e,
                                    )
                                }
                                disabled={loading}
                                search={true}
                                error={error.category}
                            />
                        </Col>
                        <Col span={24}>
                            <FormSelect label={"Branch"}
                                options={utils.branchs}
                                rules={[{ required: true }]}
                                value={form.branch}
                                onChange={(e) =>
                                    handleChangeForm(
                                        "branch",
                                        e,
                                    )
                                }
                                disabled={loading}
                                search={true}
                                error={error.branch}
                            />
                        </Col>
                        <Col span={24}>
                            <FormUploadExcel
                                label="Attach Employee Data"
                                disabled={loading}
                                value={form.file}
                                onChange={(file) => handleChangeForm("file", file)}
                                rules={[{ required: true }]}
                                error={error.file}
                            />
                        </Col>
                    </Row>
                    <Row gutter={24}>
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
                                onClick={handleClose}
                                disabled={loading}
                                isDanger={true}
                            />
                            <PrimaryButton
                                htmlType={"submit"}
                                label={"Submit"}
                                onClick={Submit}
                                disabled={loading}
                            />
                        </Col>
                    </Row>
                </Form>
            </Modal>
            {loading ? <LoadingComponent /> : null}
        </div>
    )
}

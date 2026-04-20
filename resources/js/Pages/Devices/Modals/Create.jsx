import { Col, Form, Modal, Row } from 'antd'
import React, { useEffect, useState } from 'react'
import { FormSelect, FormText } from '../../../components/Form'
import { useResponsive } from '../../../Helpers/ResponsiveHelpers';
import { usePage } from '@inertiajs/react';
import { createDevices } from '../../../services/api/devices/devices';
import { PrimaryButton, DangerButton } from '../../../components/Button';
import { LoadingComponent } from '../../../components/Loading';
import { errorHandler } from '../../../components/Handler';
import { showSuccess, showError } from '../../../components/Alert';

export default function Create(props) {
    const [open, setOpen] = useState(false);
    const [loading, setLoading] = useState(false);
    const [utils, setUtils] = useState({
        branchs: [],
        categories: []
    });
    const [form, setForm] = useState({
        branch: "",
        category: "",
        ip_address: "",
        port: "",
        name: ""
    });
    const [error, setError] = useState({
        branch: "",
        category: "",
        ip_address: "",
        port: "",
        name: ""
    });
    const pages = usePage().props
    const { isDesktop, isMobile, isTablet } = useResponsive()
    useEffect(() => {
        if (props.open) {
            setOpen(true);
            setUtils({
                branchs: pages.branchs,
                categories: pages.categories,
            });
        }
    }, [props]);

    const handleClose = () => {
        setOpen(false);
        setForm({
            branch: "",
            category: "",
            ip_address: "",
            port: "",
            name: ""
        })
        props.handleClose("create");
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
            formData.append("branch", form.branch);
            formData.append("name", form.name);
            formData.append("biometric_category_id", form.category);
            formData.append("ip_address", form.ip_address);
            formData.append("port", form.port);

            setLoading(true);
            let response = await createDevices(formData);
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
                title="Create Devices"
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
                            <FormText label={"Device Name"}
                                value={form.name}
                                disabled={loading}
                                onChange={(e) =>
                                    handleChangeForm("name", e.target.value)
                                }
                                error={error.name}
                                rules={[{ required: true }]}
                            />
                        </Col>
                        <Col span={24}>
                            <FormSelect label={"Devices Category"}
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
                            <FormText label={"IP Address"}
                                value={form.ip_address}
                                disabled={loading}
                                onChange={(e) =>
                                    handleChangeForm("ip_address", e.target.value)
                                }
                                error={error.ip_address}
                                rules={[{ required: true }]}
                            />
                        </Col>
                        <Col span={24}>
                            <FormText label={"Port"}
                                value={form.port}
                                disabled={loading}
                                onChange={(e) =>
                                    handleChangeForm("port", e.target.value)
                                }
                                error={error.port}
                                rules={[{ required: true }]}
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

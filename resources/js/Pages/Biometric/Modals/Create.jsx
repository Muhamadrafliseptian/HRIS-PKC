import { Col, Form, Modal, Row } from 'antd'
import React, { useEffect, useState } from 'react'
import { FormSelect, FormText } from '../../../components/Form'
import { useResponsive } from '../../../Helpers/ResponsiveHelpers';
import { usePage } from '@inertiajs/react';
import { PrimaryButton, DangerButton } from '../../../components/Button';
import { LoadingComponent } from '../../../components/Loading';
import { errorHandler } from '../../../components/Handler';
import { showSuccess } from '../../../components/Alert';
import { createUsers } from '../../../services/api/biometric/biometric';

export default function Create(props) {
    const [open, setOpen] = useState(false);
    const [loading, setLoading] = useState(false);
    const [utils, setUtils] = useState({
        branchs: [],
        devices: [],
        categories: []
    });
    const [form, setForm] = useState({
        device_id: "",
        name: "",
        user_id: ""
    });
    const [error, setError] = useState({
        device_id: "",
        name: "",
        user_id: ""
    });
    const pages = usePage().props
    const { isDesktop, isMobile, isTablet } = useResponsive()
    useEffect(() => {
        if (props.open) {
            setOpen(true);
            setUtils({
                branchs: pages.branchs,
                categories: pages.categories,
                devices: pages.devices
            });
        }
    }, [props]);

    const handleClose = () => {
        setOpen(false);
        setForm({
            device_id: "",
            name: "",
            user_id: ""
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
            formData.append("name", form.name);
            formData.append("user_id", form.user_id);
            formData.append("device_id", form.device_id);

            setLoading(true);
            let response = await createUsers(formData);
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
                title="Create User"
                centered={true}
                width={isMobile || isTablet ? "100%" : "60%"}
                open={open}
                onCancel={handleClose}
                style={{ fontWeight: "600" }}
                footer={false}
                id="create"
            >
                <Form layout='vertical'>
                    <Row gutter={12}>
                        <Col span={24}>
                            <FormText label={"Name"}
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
                            <FormText label={"User Id"}
                                value={form.user_id}
                                disabled={loading}
                                onChange={(e) =>
                                    handleChangeForm("user_id", e.target.value)
                                }
                                error={error.user_id}
                                rules={[{ required: true }]}
                            />
                        </Col>
                        <Col span={24}>
                            <FormSelect label={"Devices"}
                                options={utils.devices}
                                rules={[{ required: true }]}
                                value={form.device_id}
                                onChange={(e) =>
                                    handleChangeForm(
                                        "device_id",
                                        e,
                                    )
                                }
                                disabled={loading}
                                search={true}
                                error={error.device_id}
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

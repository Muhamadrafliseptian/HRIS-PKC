import React, { useEffect } from "react";
import { Head, useForm } from "@inertiajs/react";
import { Row, Col, Form, Input, Button, Card, Typography } from "antd";
import { MailOutlined, LockOutlined } from "@ant-design/icons";
import { useResponsive } from "../../Helpers/ResponsiveHelpers";
import Swal from "sweetalert2";

const { Title, Text } = Typography;

function Index() {
    const { isMobile } = useResponsive();

    const { data, setData, post, processing, errors } = useForm({
        email: "",
        password: "",
    });

    useEffect(() => {
        if (errors.global) {
            Swal.fire({
                icon: "error",
                title: "Login Gagal",
                text: errors.global,
            });
        }
    }, [errors]);

    const handleSubmit = () => {
        post(route("login"));
    };

    return (
        <>
            <Head title="Login - HRIS Puskesmas Kebon Jeruk" />

            <Row style={{ minHeight: "100vh" }}>
                {!isMobile && (
                    <Col
                        lg={14}
                        style={{
                            background: "linear-gradient(135deg, #0f172a, #1e3a8a)",
                            display: "flex",
                            alignItems: "center",
                            justifyContent: "center",
                            color: "#fff",
                            padding: "40px",
                        }}
                    >
                        <div style={{ maxWidth: 420, textAlign: "center" }}>

                            <div style={{ display: "flex", justifyContent: "center" }}>
                                <img
                                    src="/images/logo.png"
                                    alt="Logo Puskesmas"
                                    style={{
                                        height: 150,
                                        marginBottom: 20,
                                    }}
                                />
                            </div>

                            <Title style={{ color: "#fff", marginBottom: 8 }}>
                                HRIS Puskesmas
                            </Title>

                            <Text style={{ color: "#cbd5f5", fontSize: 16 }}>
                                Sistem Informasi Kepegawaian & Absensi
                                untuk mendukung pelayanan kesehatan yang lebih baik.
                            </Text>
                        </div>
                    </Col>
                )}

                <Col
                    xs={24}
                    lg={10}
                    style={{
                        display: "flex",
                        justifyContent: "center",
                        alignItems: "center",
                        padding: "24px",
                        background: "#f1f5f9",
                    }}
                >
                    <Card
                        style={{
                            width: "100%",
                            maxWidth: 380,
                            borderRadius: 16,
                            boxShadow: "0 15px 40px rgba(0,0,0,0.08)",
                            border: "none",
                        }}
                        bodyStyle={{ padding: "32px" }}
                    >
                        {/* HEADER */}
                        <div style={{ marginBottom: 24, textAlign: "center" }}>
                            <Title level={3} style={{ marginBottom: 0, color: "#1e3a8a" }}>
                                Welcome Back
                            </Title>

                            <Text type="secondary">
                                Silakan login ke sistem HRIS
                            </Text>
                        </div>

                        <Form layout="vertical" onFinish={handleSubmit}>
                            <Form.Item
                                label="Email"
                                validateStatus={errors.email ? "error" : ""}
                                help={errors.email}
                            >
                                <Input
                                    prefix={<MailOutlined />}
                                    placeholder="Masukkan email"
                                    value={data.email}
                                    onChange={(e) =>
                                        setData("email", e.target.value)
                                    }
                                    size="large"
                                    disabled={processing}
                                />
                            </Form.Item>

                            <Form.Item
                                label="Password"
                                validateStatus={errors.password ? "error" : ""}
                                help={errors.password}
                            >
                                <Input.Password
                                    prefix={<LockOutlined />}
                                    placeholder="Masukkan password"
                                    value={data.password}
                                    onChange={(e) =>
                                        setData("password", e.target.value)
                                    }
                                    size="large"
                                    disabled={processing}
                                />
                            </Form.Item>

                            <Button
                                htmlType="submit"
                                block
                                size="large"
                                loading={processing}
                                style={{
                                    marginTop: 8,
                                    borderRadius: 10,
                                    height: 45,
                                    background: "linear-gradient(135deg, #1e3a8a, #3b82f6)",
                                    border: "none",
                                    fontWeight: 600,
                                }}
                            >
                                Login
                            </Button>
                        </Form>
                    </Card>
                </Col>
            </Row>
        </>
    );
}

export default Index;
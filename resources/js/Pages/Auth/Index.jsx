import React, { useState } from "react";
import { Head, useForm, router } from "@inertiajs/react";
import {
    Row,
    Col,
    Form,
    Input,
    Button,
    Card,
    Typography,
    Spin
} from "antd";
import {
    MailOutlined,
    LockOutlined,
    LoadingOutlined
} from "@ant-design/icons";
import { Turnstile } from "@marsidev/react-turnstile";
import Swal from "sweetalert2";
import { useResponsive } from "../../Helpers/ResponsiveHelpers";

const { Title, Text } = Typography;

function Index() {
    const { isMobile } = useResponsive();

    const [captchaToken, setCaptchaToken] = useState("");
    const [loading, setLoading] = useState(false);

    const { data, setData, errors } = useForm({
        email: "",
        password: "",
        captcha: "",
    });

    const handleSubmit = () => {
        if (!captchaToken) {
            Swal.fire({
                icon: "warning",
                title: "Verifikasi dulu captcha",
            });
            return;
        }

        setLoading(true);

        router.post(
            route("login"),
            {
                email: data.email,
                password: data.password,
                captcha: captchaToken,
            },
            {
                onFinish: () => setLoading(false),
                onError: (err) => {
                    setLoading(false);

                    Swal.fire({
                        icon: "error",
                        title: err?.global || "Login gagal",
                    });
                },
            }
        );
    };

    return (
        <>
            <Head title="Login - HRIS" />

            <Row style={{ minHeight: "100dvh" }}>
                {/* LEFT SIDE */}
                {!isMobile && (
                    <Col
                        lg={14}
                        style={{
                            background: "linear-gradient(135deg, #0b1220, #1e3a8a)",
                            display: "flex",
                            alignItems: "center",
                            justifyContent: "center",
                            color: "#fff",
                            padding: 40,
                        }}
                    >
                        <div style={{ textAlign: "center", maxWidth: 700 }}>
                            <div style={{ display: "flex", justifyContent: "center" }}>
                                <img
                                    src="/images/logo.png"
                                    style={{
                                        height: 140,
                                        marginBottom: 24,
                                        filter: "drop-shadow(0 10px 20px rgba(0,0,0,0.3))",
                                    }}
                                />
                            </div>

                            <Title style={{ color: "#fff", marginBottom: 8 }}>
                                E-Absensi Puskesmas Kebon Jeruk
                            </Title>

                            <Text style={{ color: "#cbd5f5", fontSize: 15 }}>
                                Sistem manajemen absensi dan kepegawaian
                                terintegrasi untuk operasional yang lebih efisien.
                            </Text>
                        </div>
                    </Col>
                )}

                {/* RIGHT SIDE */}
                <Col
                    xs={24}
                    lg={10}
                    style={{
                        display: "flex",
                        alignItems: isMobile ? "flex-start" : "center",
                        justifyContent: "center",
                        background: isMobile
                            ? "#f1f5f9"
                            : "linear-gradient(180deg, #f1f5f9, #e2e8f0)",
                        padding: isMobile ? 16 : 24,
                        paddingTop: isMobile ? 40 : 24,
                    }}
                >
                    <Card
                        style={{
                            width: "100%",
                            maxWidth: 400,
                            borderRadius: isMobile ? 14 : 18,
                            border: "1px solid #e5e7eb",
                            boxShadow: isMobile
                                ? "0 10px 25px rgba(0,0,0,0.05)"
                                : "0 20px 50px rgba(0,0,0,0.08)",
                        }}
                        styles={{
                            body: {
                                padding: isMobile ? 20 : 32,
                            },
                        }}
                    >
                        {/* LOGO MOBILE */}
                        {isMobile && (
                            <div style={{ textAlign: "center", marginBottom: 16 }}>
                                <img src="/images/logo.png" style={{ height: 60 }} />
                            </div>
                        )}

                        {/* HEADER */}
                        <div
                            style={{
                                textAlign: "center",
                                marginBottom: isMobile ? 20 : 28,
                            }}
                        >
                            <Title level={isMobile ? 4 : 3} style={{ marginBottom: 4 }}>
                                Welcome Back
                            </Title>
                            <Text
                                type="secondary"
                                style={{ fontSize: isMobile ? 13 : 14 }}
                            >
                                Login untuk masuk ke dashboard
                            </Text>
                        </div>

                        <Form layout="vertical">
                            {/* EMAIL */}
                            <Form.Item
                                label="Email"
                                validateStatus={errors.email ? "error" : ""}
                                help={errors.email}
                            >
                                <Input
                                    prefix={<MailOutlined />}
                                    placeholder="you@example.com"
                                    value={data.email}
                                    onChange={(e) =>
                                        setData("email", e.target.value)
                                    }
                                    size={isMobile ? "middle" : "large"}
                                    disabled={loading}
                                    style={{ borderRadius: 10 }}
                                />
                            </Form.Item>

                            {/* PASSWORD */}
                            <Form.Item
                                label="Password"
                                validateStatus={errors.password ? "error" : ""}
                                help={errors.password}
                            >
                                <Input.Password
                                    prefix={<LockOutlined />}
                                    placeholder="••••••••"
                                    value={data.password}
                                    onChange={(e) =>
                                        setData("password", e.target.value)
                                    }
                                    size={isMobile ? "middle" : "large"}
                                    disabled={loading}
                                    style={{ borderRadius: 10 }}
                                />
                            </Form.Item>

                            {/* CAPTCHA */}
                            <div
                                style={{
                                    display: "flex",
                                    justifyContent: "center",
                                    marginTop: 6,
                                    marginBottom: 16,
                                }}
                            >
                                <Turnstile
                                    siteKey={import.meta.env.VITE_SITE_KEY_CAPTCHA}
                                    onSuccess={(token) =>
                                        setCaptchaToken(token)
                                    }
                                    onExpire={() => setCaptchaToken("")}
                                    options={{ theme: "light" }}
                                />
                            </div>

                            {/* BUTTON */}
                            <Button
                                onClick={handleSubmit}
                                block
                                size={isMobile ? "middle" : "large"}
                                disabled={!captchaToken || loading}
                                style={{
                                    height: isMobile ? 42 : 46,
                                    borderRadius: 12,
                                    fontWeight: 600,
                                    fontSize: isMobile ? 14 : 15,
                                    background: loading
                                        ? "#94a3b8"
                                        : "linear-gradient(135deg,#1e3a8a,#3b82f6)",
                                    border: "none",
                                    color: "#fff",
                                    boxShadow:
                                        "0 10px 25px rgba(59,130,246,0.3)",
                                }}
                            >
                                {loading ? (
                                    <>
                                        <Spin
                                            indicator={
                                                <LoadingOutlined
                                                    style={{ color: "#fff" }}
                                                    spin
                                                />
                                            }
                                            size="small"
                                            style={{ marginRight: 8 }}
                                        />
                                        Signing in...
                                    </>
                                ) : (
                                    "Login"
                                )}
                            </Button>
                        </Form>
                    </Card>
                </Col>
            </Row>
        </>
    );
}

export default Index;
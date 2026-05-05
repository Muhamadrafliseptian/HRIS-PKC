import { Head, Link } from "@inertiajs/react";
import React from "react";
import { Typography } from "antd";
import ImageError from "../../../../public/images/login/403.png";
import { PrimaryButton } from "@/Components/Button";

const { Title, Paragraph } = Typography;

function NotFound() {
    return (
        <>
            <Head title="403 - Forbidden" />
            <div
                style={{
                    display: "flex",
                    flexDirection: "column",
                    alignItems: "center",
                    justifyContent: "center",
                    height: "100vh",
                    textAlign: "center",
                    backgroundColor: "#f0f2f5",
                    padding: 24,
                }}
            >
                <img
                    src={ImageError}
                    alt="403 - Forbidden"
                    style={{
                        maxWidth: "300px",
                        marginBottom: "24px",
                        objectFit: "contain",
                    }}
                />
                <Title level={2} style={{ marginBottom: "12px" }}>
                    403 - Forbidden
                </Title>
                <Paragraph style={{ maxWidth: 480, color: "#595959" }}>
                    Anda tidak memiliki akses untuk memuat halaman ini
                </Paragraph>
                <Link href="/">
                    <PrimaryButton size="large" label="Kembali ke Beranda" />
                </Link>
            </div>
        </>
    );
}

export default NotFound;

import { Head } from "@inertiajs/react";
import React from "react";
import ImageError from "../../../../public/images/login/401.png";

function NotActive() {
    return (
        <>
            <Head title="401 - Unauthorized" />
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
                    alt="Unauthorized"
                    style={{
                        maxWidth: "300px",
                        marginBottom: "24px",
                        objectFit: "contain",
                    }}
                />
                <h1
                    style={{
                        fontSize: "32px",
                        marginBottom: "12px",
                        color: "#333",
                    }}
                >
                    401 - Akun Tidak Aktif
                </h1>
                <p
                    style={{
                        fontSize: "16px",
                        color: "#555",
                        maxWidth: "500px",
                    }}
                >
                    Akun Anda saat ini belum aktif. Silakan hubungi
                    administrator untuk mengaktifkan akun Anda sebelum bisa
                    mengakses sistem.
                </p>
            </div>
        </>
    );
}

export default NotActive;

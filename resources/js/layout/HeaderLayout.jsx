import React from "react";
import { Layout, Typography, Button, Dropdown, Avatar, Space } from "antd";
import { router, usePage } from "@inertiajs/react";
import Swal from "sweetalert2";
import axios from "axios";
import { UserOutlined } from "@ant-design/icons";

const { Header } = Layout;
const { Text } = Typography;

export default function HeaderLayout(props) {
  const { auth } = usePage().props;

  const handleLogout = () => {
    axios
      .post(route("logout"), {
        device: navigator.userAgent,
      })
      .then(() => {
        document.cookie =
          "XSRF-TOKEN=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";

        router.visit("/login");
      })
      .catch((e) => {
        Swal.fire({
          icon: "error",
          title: "Logout Failed",
          text: e.response?.data?.message || "Something went wrong",
        });
      });
  };

  const items = [
    {
      key: "logout",
      danger: true,
      icon: <i className="ti ti-logout"></i>,
      label: "Logout",
      onClick: handleLogout,
    },
  ];

  return (
    <Header
      style={{
        paddingInline: "16px",
        background: "#ffffff",
        display: "flex",
        alignItems: "center",
        justifyContent: "space-between",
        position: "sticky",
        top: 0,
        zIndex: 999,
        width: "100%",
        height: "70px",
        borderBottom: "1px solid #f1f5f9",
        boxShadow: "0 1px 6px rgba(15, 23, 42, 0.04)",
      }}
    >
      {/* LEFT */}
      <Space size={12}>
        <Button
          type="text"
          onClick={() => props.setCollapsed(!props.collapsed)}
          style={{
            width: 42,
            height: 42,
            borderRadius: "10px",
            fontSize: "18px",
            display: "flex",
            alignItems: "center",
            justifyContent: "center",
          }}
        >
          <i className="ti ti-menu-2"></i>
        </Button>

        {!props.isMobile && (
          <div>
            <Text
              strong
              style={{
                fontSize: "16px",
                color: "#0f172a",
              }}
            >
              Attendance Management
            </Text>
          </div>
        )}
      </Space>

      {/* RIGHT */}
      <Dropdown
        menu={{ items }}
        placement="bottomRight"
        trigger={["click"]}
      >
        <div
          style={{
            display: "flex",
            alignItems: "center",
            gap: "10px",
            cursor: "pointer",
            padding: "6px 10px",
            borderRadius: "12px",
            transition: "all .2s ease",
          }}
        >
          <div
            style={{
              display: "flex",
              flexDirection: "column",
              alignItems: "flex-end",
              lineHeight: 1.2,
            }}
          >
            <Text
              strong
              style={{
                fontSize: "14px",
                color: "#0f172a",
              }}
            >
              {auth?.user?.name || "Guest"}
            </Text>

            <Text
              style={{
                fontSize: "12px",
                color: "#64748b",
              }}
            >
              {auth?.user?.role == 0 ? "Super Admin" : "Karyawan"}
            </Text>
          </div>

          <Avatar
            size={42}
            icon={<UserOutlined />}
            style={{
              border: "2px solid #e2e8f0",
              backgroundColor: "#0f172a",
            }}
          />
        </div>
      </Dropdown>
    </Header>
  );
}
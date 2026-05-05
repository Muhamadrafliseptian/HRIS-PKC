import React from "react";
import { Layout, Typography, Space, Avatar, Button } from "antd";
import {
  MenuFoldOutlined,
  MenuUnfoldOutlined,
  UserOutlined,
  LogoutOutlined,
} from "@ant-design/icons";
import { router } from "@inertiajs/react";
import Swal from "sweetalert2";
import axios from "axios";
import { usePage } from "@inertiajs/react";
const { Header } = Layout;
const { Text } = Typography;

export default function HeaderLayout({ collapsed, setCollapsed, isMobile }) {
  const { auth } = usePage().props;
  const handleLogout = () => {
    axios
      .post(route("logout"), { device: navigator.userAgent })
      .then(() => {
        document.cookie =
          "XSRF-TOKEN=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";
        router.visit("/login");
      })
      .catch((e) => {
        Swal.fire({
          icon: "error",
          title: "Logout Failed",
          text: e.response.data.message,
        });
      });
  };

  return (
    <Header
      style={{
        padding: 0,
        backgroundColor: "#fff",
        display: "flex",
        justifyContent: "space-between",
        alignItems: "center",
        position: "sticky",
        top: 0,
        zIndex: 999,
        width: "100%",
        paddingInline: "16px",
      }}
    >
      <Space>
        <Button
          type="text"
          icon={collapsed ? <MenuUnfoldOutlined /> : <MenuFoldOutlined />}
          onClick={() => setCollapsed(!collapsed)}
          style={{ fontSize: 18 }}
        />

        {!isMobile && <Text strong>Dashboard</Text>}
      </Space>

      <Space>
        <Text>{auth?.user?.name || "Guest"}</Text>        
        <Avatar icon={<UserOutlined />} />

        <Button
          type="text"
          icon={<LogoutOutlined />}
          danger
          onClick={handleLogout}
        >
          {!isMobile && "Logout"}
        </Button>
      </Space>
    </Header>
  );
}
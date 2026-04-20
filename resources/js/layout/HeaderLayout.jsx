import React from "react";
import { Layout, Typography, Space, Avatar, Button, Drawer } from "antd";
import { MenuFoldOutlined, MenuUnfoldOutlined, UserOutlined } from "@ant-design/icons";

const { Header } = Layout;
const { Text } = Typography;

export default function HeaderLayout({ collapsed, setCollapsed, isMobile }) {
  return (
    <Header
      style={{
        padding: 0,
        backgroundColor: "#fff",
        display: "flex",
        flexDirection: "row",
        justifyContent: "space-between",
        position: "sticky",
        top: 0,
        zIndex: 999,
        width: "100%",
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
        <Text>Admin</Text>
        <Avatar icon={<UserOutlined />} />
      </Space>
    </Header>
  );
}
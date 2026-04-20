import React from "react";
import { Layout, Menu, Typography } from "antd";
import {
  DashboardOutlined,
  UserOutlined,
  TeamOutlined,
  FileTextOutlined,
  ApartmentOutlined,
  ClockCircleOutlined,
  IdcardOutlined,
  LaptopOutlined,
  HomeOutlined,
  TagOutlined,
  CalendarOutlined,
  UserAddOutlined,
  BarChartOutlined,
  ScheduleOutlined,
  ScanOutlined,
} from "@ant-design/icons";
import { router, usePage } from "@inertiajs/react";

const { Sider } = Layout;
const { Text } = Typography;

export default function SidebarLayout({ collapsed }) {
  const { url } = usePage();
  const openKeys = [
    url.startsWith("/biometric") ? "biometric" : null,
    url.startsWith("/master") ? "master" : null,
    url.startsWith("/shift") ? "manage_shift" : null,
    url.startsWith("/report") ? "reports" : null,
  ].filter(Boolean);

  const menuItems = [
    {
      key: "/",
      icon: <DashboardOutlined />,
      label: "Dashboard",
    },
    {
      key: "/attendance",
      icon: <ScheduleOutlined />, 
      label: "Attendance",
    },
    {
      key: "/employee",
      icon: <IdcardOutlined />,
      label: "Employee",
    },
    {
      key: "biometric",
      icon: <ScanOutlined />,
      label: "Biometric",
      children: [
        {
          key: "/biometric/users",
          icon: <UserOutlined />,
          label: "Users",
        },
        {
          key: "/biometric/devices",
          icon: <LaptopOutlined />,
          label: "Devices",
        },
      ],
    },
    {
      key: "master",
      icon: <ApartmentOutlined />,
      label: "Master",
      children: [
        {
          key: "/master/branch",
          icon: <HomeOutlined />, 
          label: "Branch",
        },
        {
          key: "/master/category",
          icon: <TagOutlined />,
          label: "Status",
        },
      ],
    },
    {
      key: "manage_shift",
      icon: <CalendarOutlined />,
      label: "Manage Shift",
      children: [
        {
          key: "/manage/shift/master",
          icon: <ClockCircleOutlined />,
          label: "Master",
        },
        {
          key: "/manage/shift/assignment",
          icon: <UserAddOutlined />, 
          label: "Assign",
        },
      ],
    },
    {
      key: "reports",
      icon: <BarChartOutlined />,
      label: "Report",
      children: [
        {
          key: "/report/attendance",
          icon: <TeamOutlined />,
          label: "Attendance",
        },
        {
          key: "/report/shift",
          icon: <FileTextOutlined />,
          label: "Shift",
        },
      ],
    },
  ];

  return (
    <Sider
      trigger={null}
      collapsible
      collapsed={collapsed}
      style={{
        overflow: "auto",
        height: "100vh",
        position: "sticky",
        insetInlineStart: 0,
        top: 0,
        bottom: 0,
      }}
    >
      <div
        style={{
          width: "100%",
          height: "70px",
          display: "flex",
          alignItems: "center",
          color: "#fff",
          fontWeight: "bold",
          fontSize: 18,
          justifyContent: "center",
        }}
      >
        {collapsed ? (
          <Text style={{ color: "#fff" }}>PKC</Text>
        ) : (
          "PKC Kebon Jeruk"
        )}
      </div>
      <Menu
        theme="dark"
        mode="inline"
        selectedKeys={[url]}
        defaultOpenKeys={openKeys}
        items={menuItems}
        onClick={({ key }) => {
          if (key.startsWith("/")) {
            router.visit(key);
          }
        }}
        style={{ borderRight: 0 }}
      />
    </Sider>
  );
}
import React from "react";
import { Layout, Menu, Typography } from "antd";
import { router, usePage } from "@inertiajs/react";
import {
  DashboardOutlined,
  ScheduleOutlined,
  IdcardOutlined,
  ScanOutlined,
  LaptopOutlined,
  UserOutlined,
  ApartmentOutlined,
  HomeOutlined,
  CalendarOutlined,
  ClockCircleOutlined,
  UserAddOutlined,
  BarChartOutlined,
  SettingFilled,
  TeamOutlined
} from "@ant-design/icons";

const { Sider } = Layout;
const { Text } = Typography;

export default function SidebarLayout({ collapsed, menus = [] }) {
  const { url } = usePage();

  const iconMap = {
    dashboard: <DashboardOutlined />,
    attendance: <ScheduleOutlined />,
    employee: <IdcardOutlined />,
    biometric: <ScanOutlined />,
    users: <UserOutlined />,
    devices: <LaptopOutlined />,
    master: <ApartmentOutlined />,
    branch: <HomeOutlined />,
    shift: <CalendarOutlined />,
    shift_master: <ClockCircleOutlined />,
    shift_assign: <UserAddOutlined />,
    report: <BarChartOutlined />,
    report_attendance: <TeamOutlined />,
    setting: <SettingFilled />,
    setting_users: <UserOutlined />,
  };

  const renderIcon = (icon) => {
    return iconMap[icon] || <DashboardOutlined />;
  };

  const mapMenu = (menus) => {
    return menus.map((m) => ({
      key: `menu-${m.id}`,
      label: m.label,
      icon: renderIcon(m.icon),
      url: m.url,
      children:
        m.childs && m.childs.length > 0
          ? m.childs.map((c) => ({
              key: `menu-${c.id}`,
              label: c.label,
              icon: renderIcon(c.icon),
              url: c.url,
            }))
          : undefined,
    }));
  };

  const mappedMenus = mapMenu(menus);

  const findKeyByUrl = (menus, url) => {
    for (let m of menus) {
      if (m.url === url) return m.key;
      if (m.children) {
        for (let c of m.children) {
          if (c.url === url) return c.key;
        }
      }
    }
    return null;
  };

  const selectedKey = findKeyByUrl(mappedMenus, url);

  // ================= FIND MENU BY KEY =================
  const findMenuByKey = (menus, key) => {
    for (let m of menus) {
      if (m.key === key) return m;
      if (m.children) {
        const found = findMenuByKey(m.children, key);
        if (found) return found;
      }
    }
    return null;
  };

  return (
    <Sider
      trigger={null}
      collapsible
      collapsed={collapsed}
      style={{
        overflow: "auto",
        height: "100vh",
        position: "sticky",
        top: 0,
      }}
    >
      {/* HEADER */}
      <div
        style={{
          height: "70px",
          display: "flex",
          alignItems: "center",
          justifyContent: "center",
          color: "#fff",
          fontWeight: "bold",
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
        selectedKeys={selectedKey ? [selectedKey] : []}
        items={mappedMenus}
        onClick={({ key }) => {
          const menu = findMenuByKey(mappedMenus, key);

          if (menu?.url) {
            router.visit(menu.url);
          }
        }}
      />
    </Sider>
  );
}
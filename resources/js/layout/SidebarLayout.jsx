import React, { useState, useEffect } from "react";
import { Layout, Menu } from "antd";
const { Sider } = Layout;

import { Link, usePage } from "@inertiajs/react";

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
  TeamOutlined,
  DeploymentUnitOutlined,
  SolutionOutlined,
} from "@ant-design/icons";

import { useResponsive } from "@/Helpers/ResponsiveHelpers";

function SidebarLayout(props) {
  const { isMobile } = useResponsive();

  const [menus, setMenus] = useState([]);
  const [selectedKey, setSelectedKey] = useState([]);
  const [openKey, setOpenKey] = useState([]);

  const pages = usePage().props;

  const getIcon = (icon) => {
    switch (icon) {
      case "dashboard":
        return <DashboardOutlined />;

      case "attendance":
        return <ScheduleOutlined />;

      case "employee":
        return <IdcardOutlined />;

      case "biometric":
        return <ScanOutlined />;

      case "users":
        return <UserOutlined />;

      case "devices":
        return <LaptopOutlined />;

      case "master":
        return <ApartmentOutlined />;

      case "branch":
        return <HomeOutlined />;

      case "employee_services":
        return <DeploymentUnitOutlined />;

      case "services":
        return <SolutionOutlined />;

      case "shift":
        return <CalendarOutlined />;

      case "shift_master":
        return <ClockCircleOutlined />;

      case "shift_assign":
        return <UserAddOutlined />;

      case "report":
        return <BarChartOutlined />;

      case "report_attendance":
        return <TeamOutlined />;

      case "setting":
        return <SettingFilled />;

      case "setting_users":
        return <UserOutlined />;

      default:
        return <DashboardOutlined />;
    }
  };

  useEffect(() => {
    if (props.menus.length > 0) {
      let newMenus = props.menus.map((menu) => ({
        icon: getIcon(menu.icon),

        label:
          menu.url == null ? (
            <span>{menu.label}</span>
          ) : (
            <Link href={menu.url}>{menu.label}</Link>
          ),

        key: menu.key,

        children:
          menu.childs.length == 0
            ? null
            : menu.childs.map((item) => ({
              icon: getIcon(item.icon),

              label: <Link href={item.url}>{item.label}</Link>,

              key: item.key,
            })),
      }));

      setMenus(newMenus);
    }
  }, [props.menus]);

  useEffect(() => {
    if (pages.open_key) {
      setOpenKey([pages.open_key]);
    }

    if (pages.selected_key) {
      setSelectedKey([pages.selected_key]);
    }
  }, [pages.open_key, pages.selected_key]);

  const handleOpenChange = (keys) => {
    setOpenKey(keys);
  };

  const siderStyle = {
    overflow: "auto",
    height: "100vh",
    position: "sticky",
    insetInlineStart: 0,
    top: 0,
    bottom: 0,
    backgroundColor: "#fff",
  };

  return (
    <>
      <Sider
        width={250}
        collapsedWidth={0}
        trigger={null}
        collapsible
        collapsed={props.collapsed}
        style={{
          ...siderStyle,
          background: "linear-gradient(180deg, #0f172a 0%, #1e293b 100%)",
          boxShadow: "2px 0 12px rgba(0,0,0,0.15)",
        }}
      >
        {/* LOGO */}
        <div
          style={{
            width: "100%",
            height: "80px",
            display: "flex",
            alignItems: "center",
            justifyContent: "center",
            borderBottom: "1px solid rgba(255,255,255,0.08)",
            background: "rgba(255,255,255,0.03)",
            backdropFilter: "blur(8px)",
          }}
        >
          <img
            src={window.origin + "/images/logo.png"}
            style={{
              width: "auto",
              height: "80px",
              objectFit: "contain",
            }}
          />
        </div>

        <Menu
          theme="dark"
          mode="inline"
          selectedKeys={selectedKey}
          openKeys={openKey}
          onOpenChange={handleOpenChange}
          items={menus}
          style={{
            background: "transparent",
            borderRight: 0,
            marginTop: "8px",
          }}
        />
      </Sider>
    </>
  );
}

export default SidebarLayout;
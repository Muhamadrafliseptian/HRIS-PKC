import React, { useState, useEffect } from "react";
import { Layout, Grid } from "antd";

import SidebarLayout from "./SidebarLayout";
import HeaderLayout from "./HeaderLayout";
import FooterLayout from "./FooterLayout";
import { getMenu } from "../services/api/menu";
import Swal from "sweetalert2";

const { Content } = Layout;
const { useBreakpoint } = Grid;

export default function Main({ children }) {
  const screens = useBreakpoint();
  const isMobile = !screens.md;
  const [collapsed, setCollapsed] = useState(isMobile);
  const [menus, setMenus] = useState([]);

  useEffect(() => {
    setCollapsed(isMobile);
  }, [isMobile]);

  const readMenu = async () => {
    try {
      let response = await getMenu();
      if (response.data.status) {
        setMenus(response.data.params);
      } else {
        Swal.fire({
          icon: "error",
          title: "Fetch menu gagal",
          text: response.data.message,
        });
      }
    } catch (e) {
      Swal.fire({
        icon: "error",
        title: "Fetch menu gagal",
        text: e.response.data.message,
      });
    }
  };

  useEffect(() => {
    readMenu();
  }, []);

  return (
    <Layout style={{ minHeight: "100vh" }}>
      <SidebarLayout
        collapsed={collapsed}
        isMobile={isMobile}
        menus={menus}
      />

      <Layout
      >
        <HeaderLayout
          collapsed={collapsed}
          setCollapsed={setCollapsed}
          isMobile={isMobile}
        />

        <Content style={{ margin: "16px" }}>
          <div
            style={{
              padding: "16px",
            }}
          >
            {children}
          </div>
        </Content>

        <FooterLayout />
      </Layout>
    </Layout>
  );
}
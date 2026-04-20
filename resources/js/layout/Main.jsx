import React, { useState, useEffect } from "react";
import { Layout, Grid } from "antd";

import SidebarLayout from "./SidebarLayout";
import HeaderLayout from "./HeaderLayout";
import FooterLayout from "./FooterLayout";

const { Content } = Layout;
const { useBreakpoint } = Grid;

export default function Main({ children }) {
  const screens = useBreakpoint();
  const isMobile = !screens.md;
  const [collapsed, setCollapsed] = useState(isMobile);

  useEffect(() => {
    setCollapsed(isMobile);
  }, [isMobile]);

  return (
    <Layout style={{ minHeight: "100vh" }}>
      <SidebarLayout
        collapsed={collapsed}
        isMobile={isMobile}
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
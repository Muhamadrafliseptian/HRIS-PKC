import React from "react";
import { Layout, Typography } from "antd";

const { Footer } = Layout;
const { Text } = Typography;

export default function FooterLayout() {
  return (
    <Footer
      style={{
        textAlign: "center",
        background: "#f0f2f5",
        padding: "10px 20px",
      }}
    >
      <Text type="secondary">
        © {new Date().getFullYear()} Puskesmas Kebon Jeruk. All rights reserved.
      </Text>
    </Footer>
  );
}
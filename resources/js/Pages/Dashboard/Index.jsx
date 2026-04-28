import React from "react";
import Main from "../../layout/Main";
import { Row, Col, Card, Statistic, Typography } from "antd";
import {
  UserOutlined,
  HddOutlined,
  ApartmentOutlined,
  ClockCircleOutlined,
} from "@ant-design/icons";
import { usePage } from "@inertiajs/react";

const { Title } = Typography;

const cardStyles = {
  borderRadius: 16,
  color: "#fff",
};

function Index() {
  const { props } = usePage();
  const stats = props.stats ?? {};

  return (
    <div style={{ padding: 20 }}>
      <Title level={3} style={{ marginBottom: 20 }}>
        Dashboard
      </Title>

      <Row gutter={[16, 16]}>
        {/* Employees */}
        <Col xs={24} sm={12} md={6}>
          <Card
            style={{
              ...cardStyles,
              background: "linear-gradient(135deg, #1890ff, #36cfc9)",
            }}
          >
            <Statistic
              title={<span style={{ color: "#fff" }}>Employees</span>}
              value={stats.employees}
              prefix={<UserOutlined />}
              valueStyle={{ color: "#fff" }}
            />
          </Card>
        </Col>

        {/* Branches */}
        <Col xs={24} sm={12} md={6}>
          <Card
            style={{
              ...cardStyles,
              background: "linear-gradient(135deg, #722ed1, #b37feb)",
            }}
          >
            <Statistic
              title={<span style={{ color: "#fff" }}>Branches</span>}
              value={stats.branches}
              prefix={<ApartmentOutlined />}
              valueStyle={{ color: "#fff" }}
            />
          </Card>
        </Col>

        {/* Shifts */}
        <Col xs={24} sm={12} md={6}>
          <Card
            style={{
              ...cardStyles,
              background: "linear-gradient(135deg, #fa8c16, #ffc069)",
            }}
          >
            <Statistic
              title={<span style={{ color: "#fff" }}>Shifts</span>}
              value={stats.shifts}
              prefix={<ClockCircleOutlined />}
              valueStyle={{ color: "#fff" }}
            />
          </Card>
        </Col>

        {/* Devices */}
        <Col xs={24} sm={12} md={6}>
          <Card
            style={{
              ...cardStyles,
              background: "linear-gradient(135deg, #13c2c2, #87e8de)",
            }}
          >
            <Statistic
              title={<span style={{ color: "#fff" }}>Devices Online</span>}
              value={`${stats.devices}/${stats.total_devices}`}
              prefix={<HddOutlined />}
              valueStyle={{ color: "#fff" }}
            />
          </Card>
        </Col>
      </Row>
    </div>
  );
}

Index.layout = (page) => <Main children={page} />;

export default Index;
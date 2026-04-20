import React, { useEffect, useState } from "react";
import Main from "../../layout/Main";
import { Row, Col, Card, Statistic, Table, Tag } from "antd";
import { UserOutlined, FileTextOutlined, HddOutlined } from "@ant-design/icons";

function Index() {
  const [stats, setStats] = useState({
    employees: 45,
    attendances: 320,
    devices: 3,
  });

  const [recentAttendance, setRecentAttendance] = useState([
    { id: 1, employee: "John Doe", attendance_in: "2026-04-07 08:00", status: "online" },
    { id: 2, employee: "Jane Smith", attendance_in: "2026-04-07 08:05", status: "online" },
    { id: 3, employee: "Bob Johnson", attendance_in: "2026-04-07 08:10", status: "offline" },
  ]);

  const columns = [
    { title: "User ID", dataIndex: "employee", key: "employee" },
    { title: "Scan Time", dataIndex: "attendance_in", key: "attendance_in" },
    { 
      title: "Status", 
      dataIndex: "status", 
      key: "status",
      render: (status) => {
        if(status === "online") return <Tag color="green">ONLINE</Tag>;
        if(status === "offline") return <Tag color="red">OFFLINE</Tag>;
        return <Tag color="orange">UNKNOWN</Tag>;
      }
    },
  ];

  return (
    <div>
      <h1 style={{ marginBottom: 20 }}>Dashboard</h1>

      <Row gutter={[16, 16]}>
        <Col xs={24} sm={12} md={8}>
          <Card>
            <Statistic
              title="Employees"
              value={stats.employees}
              prefix={<UserOutlined />}
            />
          </Card>
        </Col>
        <Col xs={24} sm={12} md={8}>
          <Card>
            <Statistic
              title="Attendance Today"
              value={stats.attendances}
              prefix={<FileTextOutlined />}
            />
          </Card>
        </Col>
        <Col xs={24} sm={12} md={8}>
          <Card>
            <Statistic
              title="Devices Online"
              value={stats.devices}
              prefix={<HddOutlined />}
            />
          </Card>
        </Col>
      </Row>

      <Card title="Recent Attendance" style={{ marginTop: 24 }}>
        <Table
          columns={columns}
          dataSource={recentAttendance}
          rowKey={(record) => record.id}
          pagination={{ pageSize: 5 }}
          bordered
        />
      </Card>
    </div>
  );
}

Index.layout = (page) => <Main children={page} />;

export default Index;
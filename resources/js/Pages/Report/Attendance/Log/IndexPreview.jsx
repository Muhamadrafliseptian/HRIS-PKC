import React, { useEffect, useState } from "react";
import { Head, usePage } from "@inertiajs/react";
import { Card, Table, Breadcrumb } from "antd";
import Main from "../../../../layout/Main";
import { router } from "@inertiajs/react";

function IndexPreview() {
  const { logs, start_date, end_date } = usePage().props;

  const [data, setData] = useState([]);

  useEffect(() => {
    if (logs) {
      const flat = Object.entries(logs).flatMap(([userId, items]) =>
        items.map((item) => ({
          ...item,
          user_id: userId,
        }))
      );

      setData(flat);
    }
  }, [logs]);

  const columns = [
    {
      title: "User ID",
      dataIndex: "user_id",
    },
    {
      title: "Scan Time",
      render: (row) => row.scan_time,
    },
    {
      title: "Device",
      render: (row) => row.dtbiouser?.biometricUser?.device?.name || "-",
    },
    {
      title: "Branch",
      render: (row) => row.dtbiouser?.dtbranch?.name || "-",
    },
  ];

  const breadcrumb = [
    { title: "Report" },
    { title: "Attendance" },
    { title: "Log Preview" },
  ];

  return (
    <div>
      <Head title="Preview Attendance Log" />

      <Breadcrumb items={breadcrumb} />

      <Card
        title={`Preview Log (${start_date} - ${end_date})`}
        style={{ marginTop: 12 }}
      >
        <Table
          dataSource={data}
          columns={columns}
          rowKey={(r, i) => i}
          pagination={{ pageSize: 20 }}
        />
      </Card>
    </div>
  );
}

IndexPreview.layout = (page) => <Main>{page}</Main>;

export default IndexPreview;
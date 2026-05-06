import React, { useEffect, useState } from "react";
import { Head, usePage } from "@inertiajs/react";
import { Card, Table, Breadcrumb } from "antd";
import Main from "../../../../layout/Main";
import '../../../../../css/main.css'

function IndexPreview() {
  const { data: logs, start_date, end_date } = usePage().props;

  const [data, setData] = useState([]);

  useEffect(() => {
    if (!logs) return;

    const flat = Object.entries(logs || {}).flatMap(([userId, items]) =>
      (items || []).map((item) => ({
        ...item,
        user_id: userId,
      }))
    );

    setData(flat);
  }, [logs]);

  const columns = [
    {
      title: "User ID",
      dataIndex: "user_id",
    },
    {
      title: "Name",
      render: (row) =>
        row.dtbiouser?.name || "-",
    },
    {
      title: "Scan Time",
      dataIndex: "scan_time",
      render: (val) => val || "-",
    },
    {
      title: "Devices",
      render: (data) => {
        const devices =
          data.dtbiouser?.biometric_user
            ?.map((u) => u.device?.name)
            ?.filter(Boolean) || [];
    
        return (
          <p className="tableSetUp">
            {devices.length ? devices.join(", ") : "-"}
          </p>
        );
      },
    },
    {
      title: "Branch",
      render: (row) =>
        row.dtbiouser?.dtbranch?.name || "-",
    },
  ];

  return (
    <div>
      <Head title="Preview Attendance Log" />

      <Breadcrumb
        items={[
          { title: "Report" },
          { title: "Attendance" },
          { title: "Log Preview" },
        ]}
      />

      <Card
        title={`Preview Log (${start_date} - ${end_date})`}
        style={{ marginTop: 12 }}
      >
        <Table
          dataSource={data}
          columns={columns}
          rowKey="id"
          pagination={{ pageSize: 20 }}
          className="custom-table"
        />
      </Card>
    </div>
  );
}

IndexPreview.layout = (page) => <Main>{page}</Main>;

export default IndexPreview;
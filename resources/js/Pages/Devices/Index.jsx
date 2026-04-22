import React, { useEffect, useState } from "react";
import { Table, Tag, Button, Card, Breadcrumb } from "antd";
import Main from "../../layout/Main";
import Create from "./Modals/Create";
import { PrimaryButton } from "../../components/Button";
import { readDevices, checkDevices } from "../../services/api/devices/devices";
import "../../../css/main.css";
import { Head } from "@inertiajs/react";
import { showError } from "../../components/Alert";

function Index() {
  const [devices, setDevices] = useState([]);
  const [loading, setLoading] = useState(false);
  const [modal, setModal] = useState({
    detail: { open: false, data: null },
    create: { open: false, data: null },
    update: { open: false, data: null },
  });

  const fetchDevices = async () => {
    try {
      setLoading(true);
      const res = await readDevices();
      setLoading(false);

      if (res.data.status) {
        const devicesWithChecking = res.data.params.data.map((d) => ({
          ...d,
          status: d.status,
          time: null,
        }));

        setDevices(devicesWithChecking);

        devicesWithChecking.forEach((device) => {
          checkDeviceStatus(device.id);
        });
      }
    } catch (err) {
      setLoading(false);
      showError(err.response.data.message)
    }
  };

  const checkDeviceStatus = async (id) => {
    try {
      const res = await checkDevices(null, id);

      if (res.data.status) {
        setDevices((prev) =>
          prev.map((d) =>
            d.id === id
              ? {
                ...d,
                status: res.data.params.status,
                synced: res.data.params.synced,
                time: res.data.params.device_time_after,
              }
              : d
          )
        );
      }
    } catch (err) {
      showError(err.response.data.message)
      setDevices((prev) =>
        prev.map((d) =>
          d.id === id ? { ...d, status: "error" } : d
        )
      );
    }
  };

  useEffect(() => {
    fetchDevices();
  }, []);

  const columns = [
    { title: "Branch", dataIndex: "branch", key: "branch" },
    { title: "Device Name", dataIndex: "name", key: "name" },
    { title: "Category", dataIndex: "category", key: "category" },
    { title: "IP Address", dataIndex: "ip", key: "ip" },
    { title: "Port", dataIndex: "port", key: "port" },
    {
      title: "Status",
      dataIndex: "status",
      key: "status",
      render: (status) => {
        if (status === "checking") return <Tag color="blue">CHECKING...</Tag>;
        if (status === "online") return <Tag color="green">ONLINE</Tag>;
        if (status === "offline") return <Tag color="red">OFFLINE</Tag>;
        if (status === "error") return <Tag color="orange">ERROR</Tag>;
        if (status === "no_config") return <Tag color="default">NO CONFIG</Tag>;
        return <Tag>UNKNOWN</Tag>;
      },
    },
    {
      title: "Action",
      key: "action",
      render: (_, record) => (
        <Button
          size="small"
          loading={record.status === "checking"}
          onClick={() => {
            setDevices((prev) =>
              prev.map((d) =>
                d.id === record.id ? { ...d, status: "checking" } : d
              )
            );
            checkDeviceStatus(record.id);
          }}
        >
          Check
        </Button>
      ),
    },
  ];

  const toggleModal = (what, data = null) => {
    setModal((prev) => ({
      ...prev,
      [what]: { open: !prev[what].open, data: data ?? prev[what].data },
    }));
  };

  const breadCrumbsItems = [
    { title: <h5 style={{ fontWeight: "600" }}>Biometric</h5> },
    { title: <h5 style={{ fontWeight: "600" }}>Devices</h5> },
  ];

  return (
    <div>
      <Head title="Devices" />
      <Breadcrumb items={breadCrumbsItems} />
      <Create
        open={modal.create.open}
        handleClose={toggleModal}
        handleUpdate={fetchDevices}
      />
      <Card
        title="Devices"
        style={{ marginTop: "12px" }}
        extra={
          <PrimaryButton
            label={"Tambah"}
            icon={"ti ti-plus"}
            onClick={() => toggleModal("create")}
          />
        }
      >
        <Table
          columns={columns}
          dataSource={devices}
          rowKey="id"
          loading={loading}
          bordered
          style={{ width: "100%" }}
          className="custom-table"
          scroll={{ x: "max-content" }}
        />
      </Card>
    </div>
  );
}

Index.layout = (page) => <Main children={page} />;
export default Index;
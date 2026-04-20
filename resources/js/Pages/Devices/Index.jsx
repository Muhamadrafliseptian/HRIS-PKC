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

  // Fetch semua device tanpa status dulu
  const fetchDevices = async () => {
    try {
      setLoading(true);
      const res = await readDevices();
      setLoading(false);

      if (res.data.status) {
        // Tampilkan device dulu dengan status "checking"
        const devicesWithChecking = res.data.params.map((d) => ({
          ...d,
          status: "checking",
          time: null,
        }));
        setDevices(devicesWithChecking);

        // Check status tiap device paralel
        devicesWithChecking.forEach((device) => {
          checkDeviceStatus(device.id);
        });
      }
    } catch (err) {
      setLoading(false);
      showError(err?.response?.data?.message);
    }
  };

  const checkDeviceStatus = async (id) => {
    try {
      const res = await checkDevices(null, id); // endpoint baru per device
      if (res.data.status) {
        setDevices((prev) =>
          prev.map((d) => (d.id === id ? { ...d, ...res.data.params } : d))
        );
      }
    } catch (err) {
      setDevices((prev) =>
        prev.map((d) => (d.id === id ? { ...d, status: "error" } : d))
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
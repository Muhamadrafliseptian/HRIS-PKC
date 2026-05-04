import {
  Modal,
  Descriptions,
  Tag,
  Spin,
  Divider,
  Button,
  message,
  Form,
} from "antd";
import React, { useEffect, useState } from "react";
import axios from "axios";
import { FormSelect } from "../../../components/Form";
import { destroyUsers, transferUsers } from "../../../services/api/biometric/biometric";

export default function Detail(props) {
  const [open, setOpen] = useState(false);
  const [loadingAction, setLoadingAction] = useState(null);

  const [openTransfer, setOpenTransfer] = useState(false);
  const [selectedDevice, setSelectedDevice] = useState(null);
  const [selectedItem, setSelectedItem] = useState(null);

  const devices = props.devices;
  const data = props.data;

  useEffect(() => {
    if (props.open) {
      setOpen(true);
    }
  }, [props.open]);

  const handleClose = () => {
    props.handleClose("detail");
    setOpen(false);
  };

  const handleDeleteUser = async (item) => {
    Modal.confirm({
      title: "Yakin hapus user?",
      content:
        "Fingerprint akan hilang dan tidak bisa dikembalikan!",
      okText: "Ya, Hapus",
      cancelText: "Batal",
      onOk: async () => {
        try {
          setLoadingAction(item.id);

          let formData = new FormData();
          formData.append("device", item.device_id);
          formData.append("user_id", data.user_id);

          let response = await destroyUsers(formData);

          if (response.data.status) {
            message.success("User berhasil dihapus permanen");
            props.handleUpdate("detail");
            handleClose()
          }
        } catch (err) {
          message.error(
            err?.response?.data?.message || "Gagal hapus user"
          );
        } finally {
          setLoadingAction(null);
        }
      },
    });
  };

  const handleTransfer = (item) => {
    setSelectedItem(item);
    setSelectedDevice(null);
    setOpenTransfer(true);
  };

  const handleSubmitTransfer = async () => {
    try {
      if (!selectedDevice) {
        return message.warning("Pilih device tujuan dulu");
      }

      setLoadingAction(selectedItem.id);

      let formData = new FormData();
      formData.append("from_device", selectedItem.device_id);
      formData.append("to_device", selectedDevice);
      formData.append("user_id", data.user_id);

      let response = await transferUsers(formData);

      if (response.data.status) {
        message.success("User berhasil dipindahkan");

        setOpenTransfer(false);
        setSelectedDevice(null);
        setSelectedItem(null);

        props.handleUpdate("detail");
        handleClose()
      }
    } catch (err) {
      message.error(
        err?.response?.data?.message
      );
    } finally {
      setLoadingAction(null);
    }
  };

  const filteredDevices = devices?.filter(
    (d) => d.value !== selectedItem?.device_id
  );

  return (
    <>
      <Modal
        title="Detail Employee"
        open={open}
        onCancel={handleClose}
        footer={null}
        width={700}
      >
        {!data ? (
          <Spin />
        ) : (
          <>
            <Descriptions bordered column={1} size="small">
              <Descriptions.Item label="NRK">
                {data.user_id}
              </Descriptions.Item>

              <Descriptions.Item label="Nama">
                {data.name}
              </Descriptions.Item>

              <Descriptions.Item label="Status">
                {data.dtstatus?.name}
              </Descriptions.Item>

              <Descriptions.Item label="Service">
                {data.dtservice?.name}
              </Descriptions.Item>

              <Descriptions.Item label="Unit Kerja">
                {data.dtbranch?.name}
              </Descriptions.Item>
            </Descriptions>

            <Divider />

            <h4>Registered Devices</h4>

            <div style={{ marginTop: 10 }}>
              {data.biometric_user?.length ? (
                data.biometric_user.map((item, index) => {
                  const device = item.device;

                  return (
                    <div
                      key={index}
                      style={{
                        border: "1px solid #eee",
                        borderRadius: 8,
                        padding: 12,
                        marginBottom: 10,
                        display: "flex",
                        justifyContent: "space-between",
                        alignItems: "center",
                      }}
                    >
                      <div>
                        <Tag color="blue">
                          {device?.name || "Unknown Device"}
                        </Tag>

                        <Tag
                          color={
                            item.is_disabled ? "red" : "green"
                          }
                          style={{ marginLeft: 6 }}
                        >
                          {item.is_disabled
                            ? "Disabled"
                            : "Active"}
                        </Tag>
                      </div>

                      <div>
                        <Button
                          type="primary"
                          size="small"
                          style={{ marginLeft: 6 }}
                          loading={loadingAction === item.id}
                          onClick={() => handleTransfer(item)}
                        >
                          Mutasi
                        </Button>

                        <Button
                          danger
                          size="small"
                          style={{ marginLeft: 6 }}
                          loading={loadingAction === item.id}
                          onClick={() => handleDeleteUser(item)}
                        >
                          Delete
                        </Button>
                      </div>
                    </div>
                  );
                })
              ) : (
                <Tag color="red">No Device Registered</Tag>
              )}
            </div>
          </>
        )}
      </Modal>

      <Modal
        title="Pindah Employee"
        open={openTransfer}
        onCancel={() => {
          setOpenTransfer(false);
          setSelectedDevice(null);
          setSelectedItem(null);
        }}
        footer={[
          <Button
            key="cancel"
            onClick={() => {
              setOpenTransfer(false);
              setSelectedDevice(null);
              setSelectedItem(null);
            }}
          >
            Batal
          </Button>,
          <Button
            key="submit"
            type="primary"
            loading={loadingAction !== null}
            onClick={handleSubmitTransfer}
          >
            Pindahkan
          </Button>,
        ]}
      >
        <Form layout="vertical">
          <FormSelect
            options={filteredDevices}
            label="Pilih Device Tujuan"
            value={selectedDevice}
            onChange={(val) => setSelectedDevice(val)}
            placeholder="Pilih device tujuan"
          />
        </Form>
      </Modal>
    </>
  );
}
import React, { useEffect, useState } from "react";
import { Head, router, usePage } from "@inertiajs/react";
import { Table, Button, Card, Form, Row, Col, Breadcrumb, Dropdown } from "antd";
import Main from "../../layout/Main";
import "../../../css/main.css";
import { PrimaryButton } from "../../components/Button";
import { FormSearch, FormSelect } from "../../components/Form";
import { destroyUsers, readUsers, syncUsers } from "../../services/api/biometric/biometric";
import { showError, showSuccess } from "../../components/Alert";
import { LoadingComponent } from "../../components/Loading";
import Create from "./Modals/Create";
import Swal from "sweetalert2";
import Update from "./Modals/Update";

function Index() {
  const pages = usePage().props
  const [loading, setLoading] = useState(false)
  const [utils, setUtils] = useState({
    branchs: [],
    devices: [],
    categories: [],
    status: []
  })
  const [modal, setModal] = useState({
    detail: { open: false, data: null },
    create: { open: false, data: null },
    update: { open: false, data: null },
  });

  const toggleModal = (what, data = null) => {
    setModal((prevModals) => {
      let updatedModals = { ...prevModals };
      updatedModals[what].open = !prevModals[what].open;
      if (data !== null) {
        updatedModals[what].data = data;
      }
      return updatedModals;
    });
  };

  const [filters, setFilters] = useState({
    branch: '',
    category: '',
    device: '',
  })
  const [users, setUsers] = useState([])

  useEffect(() => {
    setUtils({
      branchs: pages.branchs,
      categories: pages.categories,
      devices: pages.devices,
      status: pages.status
    })
  }, [])

  const handleChangeFilter = (field, value) => {
    try {
      setFilters((prev) => ({
        ...prev,
        [field]: value == undefined ? "" : value,
      }));
    } catch (e) {
      setLoading(false);
    }
  };

  useEffect(() => {
    readUser()
  }, [])

  const readUser = async () => {
    try {
      let response = await readUsers()
      if (response.data.status) {
        setUsers(response.data.params)
      }
    } catch (err) {
      showError(err.response.data.message)
    }
  }

  const deleteData = async (data) => {
    let message = "Yakin ingin hapus data ?";
    Swal.fire({
      title: "Perhatian !",
      html: message,
      icon: "question",
      showCancelButton: true,
      confirmButtonColor: "#FF9800",
      confirmButtonText: "Ya",
      cancelButtonText: "Batalkan",
      cancelButtonColor: "#ddd",
      closeOnConfirm: false,
      showLoaderOnConfirm: true,
      allowOutsideClick: () => !Swal.isLoading(),
      allowEscapeKey: () => !Swal.isLoading(),
      preConfirm: async (e) => {
        try {
          let formData = new FormData();
          formData.append("device", data.device_id);
          formData.append("user_id", data.user_id);
          let response = await destroyUsers(formData);
          if (response.data.status == false) {
            Swal.showValidationMessage(response.message);
            Swal.hideLoading();
          } else {
            Swal.close();
            readUser();
            showSuccess(response.data.message);
          }
        } catch (e) {
          showError(e.response.data.message);
        }
      },
    });
  };

  const getMenuItems = (data) => [
    {
      key: "edit",
      label: "Edit",
      icon: <i className="ti ti-edit"></i>,
      onClick: () => toggleModal("update", data),
    },

    {
      key: "delete",
      label: "Delete",
      icon: <i className="ti ti-trash"></i>,
      onClick: () => deleteData(data),
    },
  ];

  const columns = [
    {
      title: "UID",
      dataIndex: "uid",
      key: "uid",
    },
    {
      title: "Branch",
      key: "branch",
      render: (_, record) => {
        const branchId = record.device?.branch;

        const found = utils.branchs.find(
          (x) => x.value == branchId
        );

        return found ? found.label : branchId ?? "-";
      }
    },
    {
      title: "User ID",
      dataIndex: "user_id",
      key: "user_id",
    },
    {
      title: "Name",
      dataIndex: "name",
      key: "name",
    },
    {
      title: "Role",
      dataIndex: "role",
      key: "role",
      render: (role) => (role === 0 ? "User" : "Admin"),
    },
    {
      width: "150px",
      title: <i className="ti ti-settings"></i>,
      align: "center",
      render: (data) => (
        <>
          <Dropdown
            menu={{ items: getMenuItems(data) }}
            trigger={["click"]}
          >
            <i
              className="ti ti-dots-vertical"
              style={{ cursor: "pointer", padding: "12px" }}
            ></i>
          </Dropdown>
        </>
      ),
    },
  ];

  const breadCrumbsItems = [
    { title: <h5 style={{ fontWeight: "600" }}>Biometric</h5> },
    { title: <h5 style={{ fontWeight: "600" }}>Users</h5> },
  ];

  const synchronUsers = async () => {
    try {
      let formData = new FormData()
      formData.append("device", filters.device)
      formData.append("category", filters.category)
      setLoading(true)
      let response = await syncUsers(formData)
      setLoading(false)
      if (response.data.status) {
        window.location.reload()
      }
    } catch (err) {
      showError(err.response.data.message)
      setLoading(false)
    }
  }

  return (
    <div>
      <Head title="Biometric Users" />
      <Breadcrumb items={breadCrumbsItems} />
      <Create open={modal.create.open}
        handleClose={toggleModal}
        handleUpdate={readUser}
      />
      <Update open={modal.update.open}
        data={modal.update.data}
        handleUpdate={readUser}
        hancleClose={toggleModal}
      />
      <Card title={"Biometric Users"}
        style={{ marginTop: "12px" }}
        extra={
          <Row gutter={12}>
            <Col>
              <PrimaryButton
                label={"Sync"} icon={"ti ti-plus"}
                onClick={() => synchronUsers()}
              /></Col>
            <Col>
              <PrimaryButton
                label={"Tambah"} icon={"ti ti-plus"}
                onClick={() => toggleModal("create")}
              /></Col>
          </Row>
        }
      >
        <Form layout="vertical">
          <Row gutter={12}>
            <Col xs={24} sm={24} md={12} lg={4} xl={5}>
              <FormSelect
                label={"Devices"}
                options={utils.devices}
                value={filters.device}
                onChange={(e) =>
                  handleChangeFilter("device", e)
                }
              />
            </Col>
            <Col xs={24} sm={24} md={12} lg={4} xl={4}>
              <FormSelect
                options={utils.categories}
                label={"Category"}
                value={filters.category}
                onChange={(e) =>
                  handleChangeFilter("category", e)
                }
              />
            </Col>
            <Col xs={24} sm={24} md={12} lg={4} xl={4}>
              <FormSelect
                label={"Status"}
                options={utils.status}
              />
            </Col>
            <Col
              xs={24} sm={24} md={12} lg={4} xl={4}
              style={{ display: "flex", justifyContent: "end" }}
            >
              <FormSearch
                label={"Search"}
              />
            </Col>
          </Row>
        </Form>
        <Table
          columns={columns}
          dataSource={users}
          rowKey="uid"
          bordered
          style={{ width: "100%" }}
          className="custom-table"
          showExpandColumn={false}
          scroll={{ x: "max-content" }}
        />
      </Card>
      {loading ? <LoadingComponent /> : null}
    </div>
  );
}

Index.layout = (page) => <Main children={page} />;

export default Index;
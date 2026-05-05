import React, { useState, useEffect } from "react";
import Main from "../../layout/Main";
import { Breadcrumb, Card, Table, Dropdown, Tag, Row, Form, Col } from "antd";
import { Head } from "@inertiajs/react";
import { usePage } from "@inertiajs/react";
import "../../../css/main.css";
import { showError} from "../../Components/Alert";
import { readUsers } from "../../Services/api/users/users";
import Permission from "./Modals/Permission";
import { PrimaryButton } from "../../Components/Button";
import { FormSearch, FormSelect } from "../../Components/Form";

function Index() {
    const pages = usePage().props;
    const [loading, setLoading] = useState(false);
    const [users, setUsers] = useState([]);
    const [utils, setUtils] = useState({
        branch: [],
        divisions: [],
    });
    const [pagination, setPagination] = useState({
        current: 1,
        pageSize: 10,
        total: 0,
    });
    const handleTableChange = (pagination) => {
        try {
            setPagination(pagination);
        } catch (e) {
            showError("Error Change Page");
        }
    };
    const [modals, setModals] = useState({
        permission: { open: false, data: null },
    });

    const [filter, setFilter] = useState({
        search: "",
    });

    useEffect(() => {
        getUsers();
    }, [pagination.current, pagination.pageSize]);

    const toggleModal = (what, data = null) => {
        setModals((prevModals) => {
            let updatedModals = { ...prevModals };
            updatedModals[what].open = !prevModals[what].open;
            if (data !== null) {
                updatedModals[what].data = data;
            }
            return updatedModals;
        });
    };

    const handleChangeFilter = (field, value) => {
        setFilter((prev) => ({
            ...prev,
            [field]: value == undefined ? "" : value,
        }));
    };

    const getUsers = async () => {
        try {
            let formData = new FormData();
            formData.append("page", pagination.current);
            formData.append("per_page", pagination.pageSize);
            if (filter.search !== "") {
                formData.append("search", filter.search);
            }
            setLoading(true);
            let response = await readUsers(formData);
            setLoading(false);

            if (response.data.status) {
                setUsers(response.data.params.users.data);
                setPagination((prev) => ({
                    ...prev,
                    total: response.data.params.meta.total,
                }));
            } else {
                showError(response.data.message);
            }
        } catch (e) {
            setLoading(false);
            showError(e.response.data.message);
        }
    };

    const getMenuItems = (data) => [
        {
            key: "permission",
            label: "Permission",
            icon: <i className="ti ti-key"></i>,
            onClick: () => toggleModal("permission", data),
        },
    ];

    const breadCrumbsItems = [
        { title: <h5 style={{ fontWeight: "600" }}>Setting</h5> },
        { title: <h5 style={{ fontWeight: "600" }}>Users</h5> },
    ];

    const columns = [
        {
            title: "Name",
            dataIndex: "name",
            sorter: (a, b) => a.name.localeCompare(b.name),
            render: (text) => (
                <p style={{ marginTop: "0px", marginBottom: "0px" }}>{text}</p>
            ),
        },
        {
            width: "250px",
            title: "Email",
            dataIndex: "email",
            render: (text) => <p className="tableSetUp">{text}</p>,
        },
        {
            width: "200px",
            title: "Role",
            render: (data) => <p className="tableSetUp">{data.dtrole?.name}</p>,
        },
        {
            width: "120px",
            title: "Status",
            render: (data) => (
                <Tag
                    bordered={false}
                    color={data.is_active == 1 ? "green" : "error"}
                >
                    {data.is_active == 1 ? "Aktif" : "Non Aktif"}
                </Tag>
            ),
        },
        {
            width: "70px",
            title: <i className="ti ti-settings"></i>,
            align: "center",
            render: (data) => (
                <Dropdown
                    menu={{ items: getMenuItems(data) }}
                    trigger={["click"]}
                >
                    <i
                        className="ti ti-dots-vertical"
                        style={{ cursor: "pointer", padding: "12px" }}
                    ></i>
                </Dropdown>
            ),
        },
    ];

    return (
        <>
            <Head title="Users" />
            <Breadcrumb items={breadCrumbsItems}></Breadcrumb>
            <Permission
                open={modals.permission.open}
                data={modals.permission.data}
                handleClose={toggleModal}
                handleUpdate={getUsers}
            />
            <Card
                title="Users"
                style={{ marginTop: "24px" }}
                extra={
                    <PrimaryButton
                        label={"Create"}
                        icon={"ti ti-plus"}
                        onClick={() => toggleModal("create")}
                        disabled={loading}
                    />
                }
            >
                <Form layout="vertical">
                    <Row gutter={12}>
                        <Col
                            xs={24}
                            sm={24}
                            md={12}
                            lg={12}
                            xl={12}
                            style={{ margin: 0 }}
                        >
                            <Col xs={24} sm={24} md={12} lg={8} xl={12}>
                                <FormSearch
                                    label={"Search"}
                                    value={filter.search}
                                    onChange={(e) =>
                                        handleChangeFilter(
                                            "search",
                                            e.target.value
                                        )
                                    }
                                    disabled={loading}
                                    onSearch={getUsers}
                                />
                            </Col>
                        </Col>
                    </Row>
                </Form>
                <Table
                    loading={loading}
                    columns={columns}
                    dataSource={users}
                    className="custom-table"
                    rowKey={"id"}
                    pagination={pagination}
                    onChange={handleTableChange}
                    scroll={{ x: "max-content" }}
                    bordered={false}
                />
            </Card>
        </>
    );
}

Index.layout = (page) => <Main children={page} />;

export default Index;

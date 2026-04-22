import React, { useEffect, useState } from "react";
import { Table, Card, Form, Row, Col, Tag, Tooltip, Button } from "antd";
import Main from "../../../layout/Main";
import "../../../../css/main.css";
import { LoadingComponent } from "../../../components/Loading";
import { Head, usePage } from "@inertiajs/react";
import { FormSelect } from "../../../components/Form";
import { createAssignShift, readAssignShift } from "../../../services/api/shift/assign";
import dayjs from "dayjs";
import { SnippetsOutlined } from "@ant-design/icons";
import { showSuccess } from "../../../components/Alert"

function IndexAssignmentShift() {
  const pages = usePage().props;

  const [loading, setLoading] = useState(false);
  const [employees, setEmployees] = useState([]);
  const [shifts, setShifts] = useState([]);
  const [schedule, setSchedule] = useState({});
  const [pagination, setPagination] = useState({});
  const [selectedShift, setSelectedShift] = useState(null);
  const [copiedSchedule, setCopiedSchedule] = useState(null);
  const [copiedFrom, setCopiedFrom] = useState(null);

  const [selectedRowKeys, setSelectedRowKeys] = useState([]);

  const [contextMenu, setContextMenu] = useState({
    visible: false,
    x: 0,
    y: 0,
    employee: null,
  });

  const [filters, setFilters] = useState({
    branch: "",
    periode: dayjs().format("YYYY-MM"),
    service: ''
  });

  const [utils, setUtils] = useState({
    branchs: [],
    periods: [],
    services: []
  });

  useEffect(() => {
    setUtils({
      branchs: pages?.branchs,
      periods: pages?.periods,
      services: pages?.services,
    });
  }, []);

  useEffect(() => {
    if (filters.branch, filters.service) readShift();
  }, [filters]);

  const readShift = async () => {
    try {
      setLoading(true);

      let formData = new FormData();
      formData.append("branch", filters.branch);
      formData.append("service", filters.service);
      formData.append("month", filters.periode);
      formData.append("page", filters.page || 1);
      let response = await readAssignShift(formData);

      if (response.data.status) {
        const res = response.data.params;

        setEmployees(res.employees || []);
        setShifts(res.shifts || []);
        formatSchedule(res.employee_shifts || []);

        setPagination(res.pagination);
      }

      setLoading(false);
    } catch (e) {
      setLoading(false);
    }
  };

  const formatSchedule = (raw) => {
    let map = {};

    raw.forEach((item) => {
      const date = dayjs(item.date).format("YYYY-MM-DD");

      if (!map[item.employee_id]) {
        map[item.employee_id] = {};
      }

      map[item.employee_id][date] = item.shift_id;
    });

    setSchedule(map);
  };

  const getShift = (shiftId) => {
    return shifts.find((s) => s.id === shiftId);
  };

  const getShiftCode = (shiftId) => {
    const found = getShift(shiftId);
    return found ? found.code : "-";
  };

  const getShiftColor = (shift) => {
    if (!shift) return "#fafafa";
    if (shift.is_off) return "#f5f5f5";
    if (shift.type === "double") return "#fff7e6";
    if (shift.type === "split") return "#f0f5ff";
    return "#f6ffed";
  };

  const handleCellClick = (employeeId, date) => {
    if (!selectedShift) return;

    setSchedule((prev) => {
      let updated = { ...prev };

      if (!updated[employeeId]) {
        updated[employeeId] = {};
      }

      updated[employeeId][date] = selectedShift.id;

      return { ...updated };
    });
  };

  const handleCopy = (employee) => {
    const empSchedule = schedule[employee.id];
    if (!empSchedule) return;

    setCopiedSchedule({ ...empSchedule });
    setCopiedFrom(employee.name);
  };

  const handlePaste = (employee) => {
    if (!copiedSchedule) return;

    setSchedule((prev) => ({
      ...prev,
      [employee.id]: { ...copiedSchedule },
    }));
  };

  const handlePasteBulk = () => {
    if (!copiedSchedule) {
      alert("Copy dulu dari employee");
      return;
    }

    if (selectedRowKeys.length === 0) {
      alert("Pilih employee dulu");
      return;
    }

    let updated = { ...schedule };

    selectedRowKeys.forEach((empId) => {
      updated[empId] = { ...copiedSchedule };
    });

    setSchedule(updated);
  };

  const rowSelection = {
    selectedRowKeys,
    onChange: setSelectedRowKeys,
  };

  const daysInMonth = dayjs(filters.periode).daysInMonth();

  const handleSubmit = async () => {
    try {
      setLoading(true);

      let payload = [];

      Object.keys(schedule).forEach((employeeId) => {
        const dates = schedule[employeeId];

        Object.keys(dates).forEach((date) => {
          payload.push({
            employee_id: employeeId,
            date: date,
            shift_id: dates[date],
          });
        });
      });

      let formData = new FormData();
      formData.append("branch", filters.branch);
      formData.append("month", filters.periode);
      formData.append("data", JSON.stringify(payload));

      let response = await createAssignShift(formData);

      setLoading(false);

      if (response.data.status) {
        showSuccess(response.data.message);
        readShift();
      }
    } catch (err) {
      setLoading(false);
    }
  };

  const columns = [
    {
      title: "Nama",
      dataIndex: "name",
      fixed: "left",
      width: 220,
      render: (text, row) => (
        <div
          onContextMenu={(e) => {
            e.preventDefault();

            setContextMenu({
              visible: true,
              x: e.clientX,
              y: e.clientY,
              employee: row,
            });
          }}
          style={{ cursor: "context-menu" }}
        >
          <b>{text}</b>
        </div>
      )
    },

    ...Array.from({ length: daysInMonth }, (_, i) => {
      const day = i + 1;
      const date = `${filters.periode}-${String(day).padStart(2, "0")}`;
      const dayName = dayjs(date).format("dd");

      return {
        title: (
          <div>
            <div>{day}</div>
            <small>{dayName}</small>
          </div>
        ),
        width: 70,
        align: "center",
        render: (row) => {
          const shiftId = schedule[row.id]?.[date];
          const shift = getShift(shiftId);

          return (
            <Tooltip
              title={
                shift
                  ? `${shift.code} | ${shift.name}`
                  : "Belum ada shift"
              }
            >
              <div
                onClick={() => handleCellClick(row.id, date)}
                style={{
                  cursor: selectedShift ? "pointer" : "not-allowed",
                  padding: 6,
                  borderRadius: 6,
                  background: getShiftColor(shift),
                  border:
                    shiftId === selectedShift?.id
                      ? "2px solid #1890ff"
                      : "1px solid #eee",
                }}
              >
                <b>{getShiftCode(shiftId)}</b>
              </div>
            </Tooltip>
          );
        },
      };
    }),
  ];

  useEffect(() => {
    const handleClick = () => {
      setContextMenu((prev) => ({ ...prev, visible: false }));
    };

    window.addEventListener("click", handleClick);
    return () => window.removeEventListener("click", handleClick);
  }, []);

  return (
    <Card style={{ marginTop: 12 }}>
      <Head title="Assignment Shift" />

      <Card size="small" title="Pilih Shift" style={{ marginBottom: 12 }}>
        <div style={{ display: "flex", gap: 8, flexWrap: "wrap" }}>
          {shifts.map((shift) => (
            <Tag
              key={shift.id}
              color={selectedShift?.id === shift.id ? "blue" : "default"}
              onClick={() => setSelectedShift(shift)}
              style={{
                cursor: "pointer",
                padding: "6px 10px",
                lineHeight: "14px",
              }}
            >
              <div><b>{shift.code}</b></div>
              <div style={{ fontSize: 11, opacity: 0.7 }}>
                {shift.name}
              </div>
            </Tag>
          ))}
        </div>
      </Card>

      <Card size="small" style={{ marginBottom: 12 }} extra={(
        <div style={{ margin: 12 }}>
          <div style={{ display: "flex", gap: 8 }}>
            <Tooltip title="Paste ke employee yang dipilih">
              <Button
                type="primary"
                icon={<SnippetsOutlined />}
                onClick={handlePasteBulk}
                disabled={!copiedSchedule}
              />
            </Tooltip>

            <Button type="primary" onClick={handleSubmit}>
              Simpan
            </Button>
          </div>
        </div>
      )}>
        <Form layout="vertical">
          <Row gutter={12}>
            <Col xs={24} sm={12} md={8} lg={4}>
              <FormSelect
                label="Periode"
                options={utils.periods}
                value={filters.periode}
                onChange={(e) =>
                  setFilters({ ...filters, periode: e })
                }
                style={{ width: 160 }}
              />
            </Col>
            <Col xs={24} sm={12} md={8} lg={4}>
              <FormSelect
                label="Service"
                options={utils.services}
                value={filters.service}
                onChange={(e) =>
                  setFilters({ ...filters, service: e })
                }
                style={{ width: 180 }}
              />
            </Col>
            <Col xs={24} sm={12} md={8} lg={4}>
              <FormSelect
                label="Branch"
                options={utils.branchs}
                value={filters.branch}
                onChange={(e) =>
                  setFilters({ ...filters, branch: e })
                }
                style={{ width: 180 }}
              />
            </Col>
          </Row>
        </Form>
      </Card>

      <Table
        rowSelection={rowSelection}
        columns={columns}
        dataSource={employees}
        rowKey="id"
        bordered
        loading={loading}
        className="custom-table"
        pagination={{
          current: pagination.current_page,
          total: pagination.total,
          pageSize: pagination.per_page,
          onChange: (page) => {
            setFilters((prev) => ({ ...prev, page }));
          },
        }}
        scroll={{ x: "max-content", y: 500 }}
        size="small"
      />

      {contextMenu.visible && (
        <div
          style={{
            position: "fixed",
            top: contextMenu.y,
            left: contextMenu.x,
            background: "#fff",
            border: "1px solid #ddd",
            borderRadius: 6,
            boxShadow: "0 4px 12px rgba(0,0,0,0.15)",
            zIndex: 9999,
            width: 160,
            overflow: "hidden",
          }}
        >
          <div
            style={{
              padding: 10,
              cursor: "pointer",
              borderBottom: "1px solid #f0f0f0",
            }}
            onClick={() => {
              handleCopy(contextMenu.employee);
              setContextMenu({ ...contextMenu, visible: false });
            }}
          >
            Copy Shift
          </div>

          <div
            style={{ padding: 10, cursor: "pointer" }}
            onClick={() => {
              handlePaste(contextMenu.employee);
              setContextMenu({ ...contextMenu, visible: false });
            }}
          >
            Paste Shift
          </div>
        </div>
      )}

      {loading && <LoadingComponent />}
    </Card>
  );
}

IndexAssignmentShift.layout = (page) => <Main children={page} />;
export default IndexAssignmentShift;
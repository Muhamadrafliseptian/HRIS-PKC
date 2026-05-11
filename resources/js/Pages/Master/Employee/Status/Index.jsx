import React, { useEffect, useState } from 'react';
import Main from '../../../../layout/Main';
import "../../../../../css/main.css"
import { Table, Tag } from 'antd';
import { Head } from '@inertiajs/react';
import { readEmployeeStatus } from '../../../../services/api/employee/employee';

function Index() {
  const [data, setData] = useState([]);

  useEffect(() => {
    readEmployee();
  }, []);

  const readEmployee = async () => {
    try {
      let response = await readEmployeeStatus();
      if (response.status && response.data.params.status) {
        setData(response.data.params.status);
      }
    } catch (err) {
    }
  };

  const columns = [
    {
      title: 'Status Karyawan',
      dataIndex: 'name',
      key: 'name',
    },
  ];

  return (
    <div>
      <Head title='Status Karyawan' />
      <Table
        columns={columns}
        dataSource={data}
        rowKey="id"
        pagination={{ pageSize: 10 }}
        className='custom-table'
      />
    </div>
  );
}

Index.layout = (page) => <Main>{page}</Main>;

export default Index;
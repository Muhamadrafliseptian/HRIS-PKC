import React, { useEffect, useState } from 'react';
import Main from '../../../../layout/Main';
import "../../../../../css/main.css"
import { Table, Tag } from 'antd';
import { Head } from '@inertiajs/react';
import { readEmployeeService } from '../../../../services/api/employee/employee';

function Index() {
  const [data, setData] = useState([]);

  useEffect(() => {
    readEmployee();
  }, []);

  const readEmployee = async () => {
    try {
      let response = await readEmployeeService();
      if (response.status && response.data.params.services) {
        setData(response.data.params.services);
      }
    } catch (err) {
    }
  };

  const columns = [
    {
      title: 'Unit Pelayanan',
      dataIndex: 'name',
      key: 'name',
    },
  ];

  return (
    <div>
      <Head title='Unit Pelayanan' />
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
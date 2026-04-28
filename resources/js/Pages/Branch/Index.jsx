import React, { useEffect, useState } from 'react';
import Main from '../../layout/Main';
import { readBranch } from '../../services/api/branch/branch';
import "../../../css/main.css"
import { Table, Tag } from 'antd';

function Index() {
  const [data, setData] = useState([]);

  useEffect(() => {
    readAllBranch();
  }, []);

  const readAllBranch = async () => {
    try {
      let response = await readBranch();
      if (response.status && response.data.params.branchs) {
        setData(response.data.params.branchs);
      }
    } catch (err) {
    }
  };

  const columns = [
    {
      title: 'Nama Branch',
      dataIndex: 'name',
      key: 'name',
    },
    {
      title: 'Status',
      dataIndex: 'status',
      key: 'status',
      render: (status) => (status ? <Tag color="green">Aktif</Tag> : <Tag color="red">Nonaktif</Tag>),
    },
  ];

  return (
    <div>
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
import React, { useEffect, useState } from 'react';
import Main from '../../layout/Main';
import { readBranch } from '../../services/api/branch/branch';
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
      title: 'Provinsi',
      dataIndex: ['dtprovince', 'name'],
      key: 'province',
    },
    {
      title: 'Kota',
      dataIndex: ['dtcity', 'name'], 
      key: 'city',
    },
    {
      title: 'Kecamatan',
      dataIndex: ['dtdistrict', 'name'],
      key: 'district',
      render: (text) => text || '-',
    },
    {
      title: 'Kelurahan / Desa',
      dataIndex: ['dtvillage', 'name'],
      key: 'village',
      render: (text) => text || '-',
    },
    {
      title: 'Status',
      dataIndex: 'status',
      key: 'status',
      render: (status) => (status ? <Tag color="green">Aktif</Tag> : <Tag color="red">Nonaktif</Tag>),
    },
    {
      title: 'Latitude',
      dataIndex: ['dtconfig', 'lat'],
      key: 'lat',
    },
    {
      title: 'Longitude',
      dataIndex: ['dtconfig', 'lng'],
      key: 'lng',
    },
    {
      title: 'Zona Waktu',
      dataIndex: ['dtconfig', 'time_zone_label'],
      key: 'time_zone',
    },
  ];

  return (
    <div>
      <h1>Daftar Branch</h1>
      <Table
        columns={columns}
        dataSource={data}
        rowKey="id"
        pagination={{ pageSize: 10 }}
      />
    </div>
  );
}

Index.layout = (page) => <Main>{page}</Main>;

export default Index;
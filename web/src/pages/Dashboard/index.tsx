import React, { useState } from 'react';
import {
  createColumnHelper,
  flexRender,
  getCoreRowModel,
  useReactTable,
} from '@tanstack/react-table';
import { Table, Typography } from 'antd';

const { Title } = Typography;

type Person = {
  id: number;
  firstName: string;
  lastName: string;
  age: number;
  status: string;
};

const defaultData: Person[] = [
  { id: 1, firstName: 'John', lastName: 'Doe', age: 24, status: 'Active' },
  { id: 2, firstName: 'Jane', lastName: 'Smith', age: 30, status: 'Inactive' },
  { id: 3, firstName: 'Bob', lastName: 'Johnson', age: 45, status: 'Active' },
];

const columnHelper = createColumnHelper<Person>();

// Define TanStack Table generic columns
const columns = [
  columnHelper.accessor('id', {
    header: 'ID',
    cell: info => info.getValue(),
  }),
  columnHelper.accessor('firstName', {
    header: 'First Name',
    cell: info => info.getValue(),
  }),
  columnHelper.accessor('lastName', {
    header: 'Last Name',
    cell: info => info.getValue(),
  }),
  columnHelper.accessor('age', {
    header: 'Age',
    cell: info => info.renderValue(),
  }),
  columnHelper.accessor('status', {
    header: 'Status',
    cell: info => info.renderValue(),
  }),
];

const Dashboard: React.FC = () => {
  const [data] = useState(() => [...defaultData]);

  // Connect TanStack table logic
  const table = useReactTable({
    data,
    columns,
    getCoreRowModel: getCoreRowModel(),
  });

  // Adapt TanStack column headers properties to Ant Design Table columns format
  const antTableColumns = table.getHeaderGroups()[0].headers.map((header) => ({
    key: header.id,
    title: flexRender(header.column.columnDef.header, header.getContext()),
    dataIndex: header.column.id,
    render: (_: any, record: Person) => {
      // Find row
      const row = table.getRowModel().rows.find((r) => r.original.id === record.id);
      if (!row) return null;
      // Get cell
      const cell = row.getVisibleCells().find((c) => c.column.id === header.column.id);
      return cell ? flexRender(cell.column.columnDef.cell, cell.getContext()) : null;
    }
  }));

  return (
    <div>
      <Title level={2}>Dashboard Analytics</Title>
      <div style={{ marginTop: 20 }}>
        <Table 
          columns={antTableColumns} 
          dataSource={data} 
          rowKey="id" 
          pagination={false} 
          bordered
        />
      </div>
    </div>
  );
};

export default Dashboard;

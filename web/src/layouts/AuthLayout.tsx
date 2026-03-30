import React from 'react';
import { Outlet } from 'react-router-dom';
import { Layout } from 'antd';

const { Content } = Layout;

const AuthLayout: React.FC = () => {
  return (
    <Layout style={{ minHeight: '100vh', background: '#f0f2f5' }}>
      <Content
        style={{
          display: 'flex',
          justifyContent: 'center',
          alignItems: 'center',
          padding: '2rem',
        }}
      >
        <div
          style={{
            width: '100%',
            maxWidth: '450px',
            background: '#fff',
            padding: '2.5rem',
            borderRadius: '12px',
            boxShadow: '0 4px 12px rgba(0,0,0,0.05)',
          }}
        >
          <div style={{ textAlign: 'center', marginBottom: '2rem' }}>
            <h1 style={{ fontSize: '24px', fontWeight: 600, color: '#1890ff', margin: 0 }}>
              Admin Dashboard
            </h1>
            <p style={{ color: '#8c8c8c', marginTop: '8px' }}>
              Please sign in to access your account
            </p>
          </div>
          <Outlet />
        </div>
      </Content>
    </Layout>
  );
};

export default AuthLayout;

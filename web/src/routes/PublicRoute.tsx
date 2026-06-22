import React from 'react';
import { Navigate, Outlet } from 'react-router-dom';
import { useAppStore } from '../store/useAppStore';

const PublicRoute: React.FC = () => {
  const token = useAppStore((state) => state.token);

  if (token) {
    // Chuyển hướng về dashboard nếu đã đăng nhập
    return <Navigate to="/" replace />;
  }

  return <Outlet />;
};

export default PublicRoute;

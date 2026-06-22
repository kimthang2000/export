import React from 'react';
import { Navigate, Outlet } from 'react-router-dom';
import { useAppStore } from '../store/useAppStore';

const ProtectedRoute: React.FC = () => {
  const token = useAppStore((state) => state.token);

  if (!token) {
    // Chuyển hướng về login nếu chưa có token
    return <Navigate to="/login" replace />;
  }

  return <Outlet />;
};

export default ProtectedRoute;

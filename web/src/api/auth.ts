import api from '../config/axios';
import { LoginPayload, RegisterPayload, AuthResponse } from '../types/auth';

export const login = async (data: LoginPayload): Promise<AuthResponse> => {
  const response: any = await api.post('/login', data);
  const token = response.access_token;
  
  // Save token natively prior to /me call for axios interceptor
  localStorage.setItem('token', token);
  
  const user: any = await api.get('/me');
  
  return {
    token,
    user: {
      id: user.id,
      name: user.name,
      email: user.email,
    },
  };
};

export const register = async (data: RegisterPayload): Promise<AuthResponse> => {
  const response: any = await api.post('/register', data);
  const token = response.access_token;
  
  // Save token natively prior to /me call for axios interceptor
  localStorage.setItem('token', token);
  
  const user: any = await api.get('/me');
  
  return {
    token,
    user: {
      id: user.id,
      name: user.name,
      email: user.email,
    },
  };
};

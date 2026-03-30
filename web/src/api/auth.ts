import { LoginPayload, RegisterPayload, AuthResponse } from '../types/auth';

// Since the API validation is requested to be temporarily mocked for now to redirect to dashboard,
// we'll simulate the API call with a 1-second timeout and fake success response.

export const login = async (data: LoginPayload): Promise<AuthResponse> => {
  // Uncomment the below line when the real endpoint is available:
  // return await api.post<null, AuthResponse>('/auth/login', data);

  return new Promise((resolve) => {
    setTimeout(() => {
      resolve({
        token: 'fake-jwt-token-12345',
        user: {
          id: 1,
          name: 'Admin User',
          email: data.email,
        },
      });
    }, 1000);
  });
};

export const register = async (data: RegisterPayload): Promise<AuthResponse> => {
  // Uncomment the below line when the real endpoint is available:
  // return await api.post<null, AuthResponse>('/auth/register', data);

  return new Promise((resolve) => {
    setTimeout(() => {
      resolve({
        token: 'fake-jwt-token-67890',
        user: {
          id: Math.floor(Math.random() * 100),
          name: data.name,
          email: data.email,
        },
      });
    }, 1000);
  });
};

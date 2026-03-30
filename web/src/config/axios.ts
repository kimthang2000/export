import axios from 'axios';

// Create an Axios instance
const api = axios.create({
  baseURL: import.meta.env.VITE_API_BASE_URL || '/api',
  timeout: 10000,
  headers: {
    'Content-Type': 'application/json',
  },
});

// Request Interceptor
api.interceptors.request.use(
  (config) => {
    // You can retrieve the token from local storage or Zustand store here
    const token = localStorage.getItem('token');
    if (token && config.headers) {
      config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
  },
  (error) => {
    return Promise.reject(error);
  }
);

// Response Interceptor
api.interceptors.response.use(
  (response) => {
    return response.data; // Return data directly for convenience
  },
  (error) => {
    // Handle global errors here
    if (error.response?.status === 401) {
      // e.g., redirect to login or clear token
      console.error('Unauthorized! Please log in again.');
    }
    return Promise.reject(error);
  }
);

export default api;

export interface LoginPayload {
  email: string;
  password?: string;
}

export interface RegisterPayload {
  name: string;
  email: string;
  password?: string;
  password_confirmation?: string;
}

export interface AuthResponse {
  token: string;
  user: {
    id?: number | string;
    name: string;
    email: string;
  };
}

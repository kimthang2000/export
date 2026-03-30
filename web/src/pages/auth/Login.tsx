import React, { useState } from 'react';
import { Form, Input, Button, message } from 'antd';
import { UserOutlined, LockOutlined } from '@ant-design/icons';
import { useNavigate, Link } from 'react-router-dom';
import { login } from '../../api/auth';
import { useAppStore } from '../../store/useAppStore';
import { LoginPayload } from '../../types/auth';

const Login: React.FC = () => {
  const [loading, setLoading] = useState(false);
  const navigate = useNavigate();
  const { setToken, setUser } = useAppStore();

  const onFinish = async (values: LoginPayload) => {
    setLoading(true);
    try {
      const response = await login(values);
      setToken(response.token);
      setUser({ name: response.user.name, email: response.user.email });
      message.success('Successfully logged in!');
      navigate('/');
    } catch (error: any) {
      if (error?.response?.data?.message) {
        message.error(error.response.data.message);
      } else {
        message.error('An error occurred during login. Please try again.');
      }
    } finally {
      setLoading(false);
    }
  };

  return (
    <Form
      name="login_form"
      onFinish={onFinish}
      layout="vertical"
      requiredMark={false}
      size="large"
    >
      <Form.Item
        name="email"
        rules={[
          { required: true, message: 'Please input your Email!' },
          { type: 'email', message: 'Please enter a valid email address!' }
        ]}
      >
        <Input prefix={<UserOutlined />} placeholder="Email address" />
      </Form.Item>
      <Form.Item
        name="password"
        rules={[{ required: true, message: 'Please input your Password!' }]}
      >
        <Input.Password prefix={<LockOutlined />} placeholder="Password" />
      </Form.Item>
      
      <Form.Item>
        <Button 
          type="primary" 
          htmlType="submit" 
          style={{ width: '100%', height: '40px' }}
          loading={loading}
        >
          Sign In
        </Button>
      </Form.Item>
      
      <div style={{ textAlign: 'center' }}>
        Don't have an account? <Link to="/register">Sign up now!</Link>
      </div>
    </Form>
  );
};

export default Login;

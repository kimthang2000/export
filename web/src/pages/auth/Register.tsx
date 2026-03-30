import React, { useState } from 'react';
import { Form, Input, Button, message } from 'antd';
import { UserOutlined, LockOutlined, MailOutlined } from '@ant-design/icons';
import { useNavigate, Link } from 'react-router-dom';
import { register } from '../../api/auth';
import { RegisterPayload } from '../../types/auth';

const Register: React.FC = () => {
  const [loading, setLoading] = useState(false);
  const navigate = useNavigate();

  const onFinish = async (values: RegisterPayload) => {
    setLoading(true);
    try {
      await register(values);
      message.success('Registration successful! Please log in.');
      navigate('/login');
    } catch (error: any) {
      if (error?.response?.data?.message) {
        message.error(error.response.data.message);
      } else {
        message.error('An error occurred during registration. Please try again.');
      }
    } finally {
      setLoading(false);
    }
  };

  return (
    <Form
      name="register_form"
      onFinish={onFinish}
      layout="vertical"
      requiredMark={false}
      size="large"
    >
      <Form.Item
        name="name"
        rules={[
          { required: true, message: 'Please input your Full Name!' }
        ]}
      >
        <Input prefix={<UserOutlined />} placeholder="Full Name" />
      </Form.Item>
      
      <Form.Item
        name="email"
        rules={[
          { required: true, message: 'Please input your Email!' },
          { type: 'email', message: 'Please enter a valid email address!' }
        ]}
      >
        <Input prefix={<MailOutlined />} placeholder="Email address" />
      </Form.Item>
      
      <Form.Item
        name="password"
        rules={[
          { required: true, message: 'Please input your Password!' },
          { min: 6, message: 'Password must be at least 6 characters!' }
        ]}
        hasFeedback
      >
        <Input.Password prefix={<LockOutlined />} placeholder="Password" />
      </Form.Item>

      <Form.Item
        name="password_confirmation"
        dependencies={['password']}
        hasFeedback
        rules={[
          { required: true, message: 'Please confirm your password!' },
          ({ getFieldValue }) => ({
            validator(_, value) {
              if (!value || getFieldValue('password') === value) {
                return Promise.resolve();
              }
              return Promise.reject(new Error('The two passwords do not match!'));
            },
          }),
        ]}
      >
        <Input.Password prefix={<LockOutlined />} placeholder="Confirm password" />
      </Form.Item>

      <Form.Item>
        <Button 
          type="primary" 
          htmlType="submit" 
          style={{ width: '100%', height: '40px' }}
          loading={loading}
        >
          Sign Up
        </Button>
      </Form.Item>
      
      <div style={{ textAlign: 'center' }}>
        Already have an account? <Link to="/login">Sign in here!</Link>
      </div>
    </Form>
  );
};

export default Register;

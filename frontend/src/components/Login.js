import React, { useState } from 'react';
import api from '../services/api';

function Login({ onLogin }) {
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [error, setError] = useState('');
  const [loading, setLoading] = useState(false);

  const handleSubmit = async (e) => {
    e.preventDefault();
    setError('');
    setLoading(true);

    try {
      const response = await api.post('/login', { email, password });
      onLogin(response.data.token, response.data.user);
    } catch (err) {
      setError(err.response?.data?.message || 'Login failed');
    } finally {
      setLoading(false);
    }
  };

  return (
    <div style={{ maxWidth: '400px', margin: '100px auto' }}>
      <div className="card">
        <h2 style={{ textAlign: 'center', marginBottom: '20px' }}>Login</h2>
        {error && (
          <div style={{ padding: '10px', background: '#f8d7da', color: '#721c24', borderRadius: '4px', marginBottom: '15px' }}>
            {error}
          </div>
        )}
        <form onSubmit={handleSubmit}>
          <div>
            <label>Email</label>
            <input
              type="email"
              value={email}
              onChange={(e) => setEmail(e.target.value)}
              required
            />
          </div>
          <div>
            <label>Password</label>
            <input
              type="password"
              value={password}
              onChange={(e) => setPassword(e.target.value)}
              required
            />
          </div>
          <button 
            type="submit" 
            className="btn btn-primary" 
            style={{ width: '100%', marginTop: '10px' }}
            disabled={loading}
          >
            {loading ? 'Logging in...' : 'Login'}
          </button>
        </form>
        <p style={{ textAlign: 'center', marginTop: '20px', color: '#666' }}>
          Don't have an account? <a href="/register" style={{ color: '#007bff' }}>Sign up here</a>
        </p>
        <p style={{ textAlign: 'center', marginTop: '10px', color: '#999', fontSize: '14px' }}>
          Demo: user@example.com / password
        </p>
      </div>
    </div>
  );
}

export default Login;

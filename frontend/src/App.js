import React, { useState, useEffect } from 'react';
import { BrowserRouter as Router, Routes, Route, Navigate } from 'react-router-dom';
import Login from './components/Login';
import Register from './components/Register';
import Dashboard from './components/Dashboard';
import Plans from './components/Plans';
import Subscriptions from './components/Subscriptions';
import Invoices from './components/Invoices';
import AdminDashboard from './components/AdminDashboard';
import Navbar from './components/Navbar';
import api from './services/api';

function App() {
  const [user, setUser] = useState(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    const token = localStorage.getItem('token');
    if (token) {
      loadUser();
    } else {
      setLoading(false);
    }
  }, []);

  const loadUser = async () => {
    try {
      const response = await api.get('/me');
      setUser(response.data);
    } catch (error) {
      localStorage.removeItem('token');
    } finally {
      setLoading(false);
    }
  };

  const handleLogin = (token, userData) => {
    localStorage.setItem('token', token);
    setUser(userData);
  };

  const handleLogout = () => {
    localStorage.removeItem('token');
    setUser(null);
  };

  if (loading) {
    return <div style={{ textAlign: 'center', padding: '50px' }}>Loading...</div>;
  }

  return (
    <Router>
      {user && <Navbar user={user} onLogout={handleLogout} />}
      <Routes>
        <Route 
          path="/login" 
          element={user ? <Navigate to="/" /> : <Login onLogin={handleLogin} />} 
        />
        <Route 
          path="/register" 
          element={user ? <Navigate to="/" /> : <Register />} 
        />
        <Route 
          path="/" 
          element={user ? <Dashboard user={user} /> : <Navigate to="/login" />} 
        />
        <Route 
          path="/plans" 
          element={user ? <Plans user={user} /> : <Navigate to="/login" />} 
        />
        <Route 
          path="/subscriptions" 
          element={user ? <Subscriptions user={user} /> : <Navigate to="/login" />} 
        />
        <Route 
          path="/invoices" 
          element={user ? <Invoices user={user} /> : <Navigate to="/login" />} 
        />
        <Route 
          path="/admin" 
          element={user?.role === 'admin' ? <AdminDashboard /> : <Navigate to="/" />} 
        />
      </Routes>
    </Router>
  );
}

export default App;

import React, { useState, useEffect } from 'react';
import api from '../services/api';

function AdminDashboard() {
  const [stats, setStats] = useState({
    totalRevenue: 0,
    activeSubscriptions: 0,
    totalUsers: 0,
    pendingInvoices: 0
  });
  const [recentSubscriptions, setRecentSubscriptions] = useState([]);
  const [recentInvoices, setRecentInvoices] = useState([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    fetchAdminData();
  }, []);

  const fetchAdminData = async () => {
    try {
      const [subsResponse, invoicesResponse] = await Promise.all([
        api.get('/admin/subscriptions'),
        api.get('/admin/invoices')
      ]);

      const subscriptions = subsResponse.data;
      const invoices = invoicesResponse.data;

      // Calculate stats
      const activeCount = subscriptions.filter(s => s.status === 'active').length;
      const revenue = invoices
        .filter(i => i.status === 'paid')
        .reduce((sum, i) => sum + parseFloat(i.total_amount), 0);
      const pending = invoices.filter(i => i.status === 'pending').length;

      setStats({
        totalRevenue: revenue.toFixed(2),
        activeSubscriptions: activeCount,
        totalUsers: subscriptions.length,
        pendingInvoices: pending
      });

      setRecentSubscriptions(subscriptions.slice(0, 5));
      setRecentInvoices(invoices.slice(0, 5));
    } catch (err) {
      console.error('Failed to load admin data', err);
    } finally {
      setLoading(false);
    }
  };

  if (loading) return <div className="container"><p>Loading dashboard...</p></div>;

  return (
    <div className="container">
      <h1>Admin Dashboard</h1>

      {/* Stats Grid */}
      <div style={{ 
        display: 'grid', 
        gridTemplateColumns: 'repeat(auto-fit, minmax(200px, 1fr))',
        gap: '20px',
        marginTop: '30px'
      }}>
        <div className="card" style={{ textAlign: 'center', background: '#e3f2fd' }}>
          <h3 style={{ margin: '10px 0', fontSize: '36px', color: '#1976d2' }}>
            ${stats.totalRevenue}
          </h3>
          <p style={{ color: '#666' }}>Total Revenue</p>
        </div>

        <div className="card" style={{ textAlign: 'center', background: '#e8f5e9' }}>
          <h3 style={{ margin: '10px 0', fontSize: '36px', color: '#388e3c' }}>
            {stats.activeSubscriptions}
          </h3>
          <p style={{ color: '#666' }}>Active Subscriptions</p>
        </div>

        <div className="card" style={{ textAlign: 'center', background: '#fff3e0' }}>
          <h3 style={{ margin: '10px 0', fontSize: '36px', color: '#f57c00' }}>
            {stats.totalUsers}
          </h3>
          <p style={{ color: '#666' }}>Total Users</p>
        </div>

        <div className="card" style={{ textAlign: 'center', background: '#fce4ec' }}>
          <h3 style={{ margin: '10px 0', fontSize: '36px', color: '#c2185b' }}>
            {stats.pendingInvoices}
          </h3>
          <p style={{ color: '#666' }}>Pending Invoices</p>
        </div>
      </div>

      {/* Recent Activity */}
      <div style={{ 
        display: 'grid', 
        gridTemplateColumns: '1fr 1fr',
        gap: '20px',
        marginTop: '30px'
      }}>
        {/* Recent Subscriptions */}
        <div className="card">
          <h3>Recent Subscriptions</h3>
          {recentSubscriptions.length === 0 ? (
            <p>No subscriptions yet</p>
          ) : (
            <table style={{ width: '100%', marginTop: '15px' }}>
              <thead>
                <tr style={{ borderBottom: '1px solid #ddd', textAlign: 'left' }}>
                  <th style={{ padding: '8px' }}>User</th>
                  <th style={{ padding: '8px' }}>Status</th>
                  <th style={{ padding: '8px' }}>Date</th>
                </tr>
              </thead>
              <tbody>
                {recentSubscriptions.map(sub => (
                  <tr key={sub.id} style={{ borderBottom: '1px solid #eee' }}>
                    <td style={{ padding: '8px' }}>User #{sub.user_id}</td>
                    <td style={{ padding: '8px', textTransform: 'capitalize' }}>{sub.status}</td>
                    <td style={{ padding: '8px' }}>
                      {new Date(sub.start_date).toLocaleDateString()}
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          )}
        </div>

        {/* Recent Invoices */}
        <div className="card">
          <h3>Recent Invoices</h3>
          {recentInvoices.length === 0 ? (
            <p>No invoices yet</p>
          ) : (
            <table style={{ width: '100%', marginTop: '15px' }}>
              <thead>
                <tr style={{ borderBottom: '1px solid #ddd', textAlign: 'left' }}>
                  <th style={{ padding: '8px' }}>ID</th>
                  <th style={{ padding: '8px' }}>Amount</th>
                  <th style={{ padding: '8px' }}>Status</th>
                </tr>
              </thead>
              <tbody>
                {recentInvoices.map(invoice => (
                  <tr key={invoice.id} style={{ borderBottom: '1px solid #eee' }}>
                    <td style={{ padding: '8px' }}>#{invoice.id}</td>
                    <td style={{ padding: '8px' }}>${invoice.total_amount}</td>
                    <td style={{ padding: '8px', textTransform: 'capitalize' }}>
                      {invoice.status}
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          )}
        </div>
      </div>
    </div>
  );
}

export default AdminDashboard;

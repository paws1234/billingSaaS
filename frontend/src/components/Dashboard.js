import React from 'react';

function Dashboard({ user }) {
  return (
    <div className="container">
      <h1>Welcome, {user.name}!</h1>
      
      <div className="grid" style={{ marginTop: '30px' }}>
        <div className="card">
          <h3>Account Information</h3>
          <p><strong>Email:</strong> {user.email}</p>
          <p><strong>Role:</strong> {user.role}</p>
          {user.billing_name && (
            <>
              <p><strong>Billing Name:</strong> {user.billing_name}</p>
              <p><strong>Address:</strong> {user.billing_address}, {user.billing_city}</p>
              <p><strong>Country:</strong> {user.billing_country}</p>
            </>
          )}
        </div>

        <div className="card">
          <h3>Quick Actions</h3>
          <div style={{ display: 'flex', flexDirection: 'column', gap: '10px' }}>
            <a href="/plans" className="btn btn-primary" style={{ textDecoration: 'none', textAlign: 'center' }}>
              View Plans
            </a>
            <a href="/subscriptions" className="btn btn-secondary" style={{ textDecoration: 'none', textAlign: 'center' }}>
              My Subscriptions
            </a>
            <a href="/invoices" className="btn btn-secondary" style={{ textDecoration: 'none', textAlign: 'center' }}>
              View Invoices
            </a>
          </div>
        </div>
      </div>
    </div>
  );
}

export default Dashboard;
